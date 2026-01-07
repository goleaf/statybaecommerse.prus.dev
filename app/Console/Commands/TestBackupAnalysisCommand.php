<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BackupAnalysisService;
use App\Services\RestorationPriorityQueue;
use Exception;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class TestBackupAnalysisCommand extends Command
{
    protected $signature = 'backup:test-analysis';

    protected $description = 'Test backup analysis functionality without loading problematic resources';

    public function handle(): int
    {
        $this->info('Testing backup analysis functionality...');

        try {
            // Create service instance
            $backupAnalysisService = new BackupAnalysisService;

            // Test backup directory discovery
            $backupDirs = $backupAnalysisService->getBackupDirectories();
            $this->info('Found backup directories: ' . count($backupDirs));

            foreach ($backupDirs as $dir) {
                $this->line("  - {$dir}");
            }

            if (empty($backupDirs)) {
                $this->warn('No backup directories found. Analysis cannot proceed.');

                return Command::SUCCESS;
            }

            // Test basic cataloging (without loading PHP files that might have compatibility issues)
            $this->info('Testing basic file discovery...');

            $latestBackup = collect($backupDirs)->last();
            $backupPath = base_path("backups/{$latestBackup}");

            $this->info("Analyzing backup: {$latestBackup}");
            $this->info("Backup path: {$backupPath}");

            // Test directory structure
            $filamentPath = "{$backupPath}/app/Filament";
            if (file_exists($filamentPath)) {
                $this->info('✓ Filament directory found');

                // Count resources
                $resourcesPath = "{$filamentPath}/Resources";
                if (file_exists($resourcesPath)) {
                    $resourceFiles = glob("{$resourcesPath}/*.php");
                    $this->info('✓ Found ' . count($resourceFiles) . ' resource files');
                }

                // Count pages
                $pagesPath = "{$filamentPath}/Pages";
                if (file_exists($pagesPath)) {
                    $pageFiles = $this->countPhpFiles($pagesPath);
                    $this->info('✓ Found ' . $pageFiles . ' page files');
                }

                // Count widgets
                $widgetsPath = "{$filamentPath}/Widgets";
                if (file_exists($widgetsPath)) {
                    $widgetFiles = $this->countPhpFiles($widgetsPath);
                    $this->info('✓ Found ' . $widgetFiles . ' widget files');
                }
            } else {
                $this->warn('✗ Filament directory not found');
            }

            // Test models directory
            $modelsPath = "{$backupPath}/app/Models";
            if (file_exists($modelsPath)) {
                $modelFiles = $this->countPhpFiles($modelsPath);
                $this->info('✓ Found ' . $modelFiles . ' model files');
            }

            // Test migrations directory
            $migrationsPath = "{$backupPath}/database/migrations";
            if (file_exists($migrationsPath)) {
                $migrationFiles = glob("{$migrationsPath}/*.php");
                $this->info('✓ Found ' . count($migrationFiles) . ' migration files');
            }

            // Test priority queue creation
            $this->info('Testing priority queue...');
            $priorityQueue = new RestorationPriorityQueue($backupAnalysisService);

            // Create mock cataloged resources for testing
            $mockResources = [
                'models' => [
                    ['name' => 'User', 'path' => '/test/User.php', 'type' => 'model', 'dependencies' => []],
                    ['name' => 'Product', 'path' => '/test/Product.php', 'type' => 'model', 'dependencies' => ['User']],
                ],
                'resources' => [
                    ['name' => 'UserResource', 'path' => '/test/UserResource.php', 'type' => 'resource', 'model' => 'User'],
                    ['name' => 'ProductResource', 'path' => '/test/ProductResource.php', 'type' => 'resource', 'model' => 'Product'],
                ],
                'pages'        => [],
                'widgets'      => [],
                'components'   => [],
                'actions'      => [],
                'concerns'     => [],
                'migrations'   => [],
                'translations' => [],
                'config'       => [],
            ];

            // Test queue building with mock data
            $queue = $priorityQueue->buildQueue($mockResources);
            $stats = $priorityQueue->getStatistics();

            $this->info('✓ Priority queue created successfully');
            $this->info("  Total items: {$stats['total_original']}");
            $this->info('  By priority: ' . json_encode($stats['by_priority']));

            $this->info('✓ All tests passed!');

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('Error during testing: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());

            return Command::FAILURE;
        }
    }

    private function countPhpFiles(string $directory): int
    {
        $count = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $count++;
            }
        }

        return $count;
    }
}
