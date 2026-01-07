<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BackupAnalysisService;
use App\Services\RestorationPriorityQueue;
use Exception;
use Illuminate\Console\Command;

class AnalyzeBackupCommand extends Command
{
    protected $signature = 'backup:analyze {--backup-path= : Specific backup path to analyze}';

    protected $description = 'Analyze backup files and generate restoration priority queue';

    public function handle(BackupAnalysisService $backupAnalysisService, RestorationPriorityQueue $priorityQueue): int
    {
        $this->info('Starting backup analysis...');

        try {
            // Get backup path from option or use latest
            $backupPath = $this->option('backup-path');

            // Catalog resources
            $this->info('Cataloging resources...');
            $catalogedResources = $backupAnalysisService->catalogResources($backupPath);

            // Display catalog summary
            $this->displayCatalogSummary($catalogedResources);

            // Identify dependencies
            $this->info('Analyzing dependencies...');
            $dependencies = $backupAnalysisService->identifyDependencies($catalogedResources);

            // Build priority queue
            $this->info('Building restoration priority queue...');
            $queue = $priorityQueue->buildQueue($catalogedResources);

            // Display queue statistics
            $this->displayQueueStatistics($priorityQueue);

            // Display ready items
            $this->displayReadyItems($priorityQueue);

            $this->info('Backup analysis completed successfully!');

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('Error during backup analysis: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

    private function displayCatalogSummary(array $catalogedResources): void
    {
        $this->info('=== Catalog Summary ===');

        foreach ($catalogedResources as $type => $items) {
            $count = count($items);
            $this->line("  {$type}: {$count} items");
        }

        $this->newLine();
    }

    private function displayQueueStatistics(RestorationPriorityQueue $priorityQueue): void
    {
        $stats = $priorityQueue->getStatistics();

        $this->info('=== Priority Queue Statistics ===');
        $this->line("  Total items: {$stats['total_original']}");
        $this->line("  Completed: {$stats['completed']}");
        $this->line("  Failed: {$stats['failed']}");
        $this->line("  Remaining: {$stats['remaining']}");

        $this->info('By Priority:');
        foreach ($stats['by_priority'] as $priority => $count) {
            $this->line("  {$priority}: {$count} items");
        }

        $this->newLine();
    }

    private function displayReadyItems(RestorationPriorityQueue $priorityQueue): void
    {
        $readyItems = $priorityQueue->getReadyItems();

        $this->info('=== Ready to Restore (First 10) ===');

        $displayed = 0;
        foreach ($readyItems as $item) {
            if ($displayed >= 10) {
                break;
            }

            $this->line("  [{$item['priority']}] {$item['type']}: {$item['name']}");
            $displayed++;
        }

        if (count($readyItems) > 10) {
            $remaining = count($readyItems) - 10;
            $this->line("  ... and {$remaining} more items");
        }

        $this->newLine();
    }
}
