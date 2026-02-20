<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Filament\FilamentResourceFixer;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

final class FixCriticalFilamentErrorsCommand extends Command
{
    protected $signature = 'filament:fix-critical-errors 
                            {--dry-run : Show what would be changed without making changes}
                            {--resource=* : Specific resources to fix (optional)}';

    protected $description = '';

    public function __construct(
        private readonly FilamentResourceFixer $fixer
    ) {
        parent::__construct();
        $this->setDescription(__('messages.fix_critical_filament_errors_command_description'));
    }

    public function handle(): int
    {
        $this->components->info('Fixing critical syntax errors...');

        $resources = $this->getResourcesToProcess();
        $isDryRun = $this->option('dry-run');

        if ($resources->isEmpty()) {
            $this->components->warn('No resources found to process.');

            return self::SUCCESS;
        }

        $results = $this->fixer->fixResources($resources, $isDryRun);

        $this->displayResults($results, $isDryRun);

        return $results->hasErrors() ? self::FAILURE : self::SUCCESS;
    }

    private function getResourcesToProcess(): Collection
    {
        $specificResources = $this->option('resource');

        if (! empty($specificResources)) {
            return collect($specificResources);
        }

        return $this->fixer->getCriticalResources();
    }

    private function displayResults(object $results, bool $isDryRun): void
    {
        $this->newLine();

        if ($isDryRun) {
            $this->components->info('=== DRY RUN RESULTS ===');
        } else {
            $this->components->info('=== RESULTS ===');
        }

        $this->table(
            ['Resource', 'Status', 'Issues Fixed'],
            $results->toTableRows()
        );

        if ($results->hasErrors()) {
            $this->newLine();
            $this->components->error('Errors encountered:');
            foreach ($results->getErrors() as $error) {
                $this->line("  • {$error}");
            }
        }

        $this->newLine();
        $this->components->info("Processed: {$results->getProcessedCount()} resources");
        $this->components->info("Fixed: {$results->getFixedCount()} resources");

        if ($results->hasErrors()) {
            $this->components->error("Errors: {$results->getErrorCount()}");
        }
    }
}
