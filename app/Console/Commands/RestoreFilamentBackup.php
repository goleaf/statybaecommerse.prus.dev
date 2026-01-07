<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BackupRestoreService;
use App\Services\VersionCompatibilityService;
use Illuminate\Console\Command;

class RestoreFilamentBackup extends Command
{
    protected $signature = 'filament:restore-backup';

    protected $description = 'Restore Filament files from backup with v3.3 compatibility fixes';

    public function handle(): int
    {
        $this->info('Starting Filament backup restoration...');

        // Create the services
        $compatibilityService = new VersionCompatibilityService;
        $restoreService = new BackupRestoreService($compatibilityService);

        // Restore from the latest backup
        $results = $restoreService->restoreFromLatestBackup();

        // Display results
        $this->newLine();
        $this->info('=== RESTORATION RESULTS ===');
        $this->info("Restored files: {$results['summary']['restored_count']}");
        $this->info("Skipped files: {$results['summary']['skipped_count']}");
        $this->info("Errors: {$results['summary']['error_count']}");

        if (! empty($results['restored'])) {
            $this->newLine();
            $this->info('=== RESTORED FILES ===');
            foreach ($results['restored'] as $file) {
                $this->line("✓ {$file}");
            }
        }

        if (! empty($results['skipped'])) {
            $this->newLine();
            $this->warn('=== SKIPPED FILES ===');
            foreach ($results['skipped'] as $file) {
                $this->line("⚠ {$file}");
            }
        }

        if (! empty($results['errors'])) {
            $this->newLine();
            $this->error('=== ERRORS ===');
            foreach ($results['errors'] as $error) {
                $this->line("✗ {$error}");
            }
        }

        $this->newLine();
        $this->info('Restoration complete!');

        return self::SUCCESS;
    }
}
