<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CampaignRollbackCommand extends Command
{
    protected $signature = 'campaign:rollback {--confirm : Confirm the rollback operation}';

    protected $description = 'Rollback campaign feature removal (emergency use only)';

    public function handle(): int
    {
        if (! $this->option('confirm')) {
            $this->error('This is a destructive operation. Use --confirm to proceed.');

            return self::FAILURE;
        }

        $this->warn('Rolling back campaign feature removal...');

        if (! $this->confirm('Are you sure you want to restore campaign functionality?')) {
            $this->info('Rollback cancelled.');

            return self::SUCCESS;
        }

        try {
            // 1. Restore feature flag
            $this->restoreFeatureFlag();

            // 2. Restore archived data
            $this->restoreArchivedData();

            // 3. Remove deprecation markers
            $this->removeDeprecationMarkers();

            $this->info('Campaign feature rollback completed successfully!');
            $this->warn('Remember to update your application code to re-enable campaign functionality.');

        } catch (Exception $e) {
            $this->error('Rollback failed: ' . $e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function restoreFeatureFlag(): void
    {
        $configPath = config_path('app-features.php');
        $content = file_get_contents($configPath);

        // Add campaigns back to features array
        $content = str_replace(
            "'recommendations'  => true,",
            "'recommendations'  => true,\n        'campaigns'        => true,",
            $content
        );

        file_put_contents($configPath, $content);
        $this->line('Restored campaigns feature flag');
    }

    private function restoreArchivedData(): void
    {
        if (! Schema::hasTable('campaign_data_archive')) {
            $this->warn('No archived campaign data found');

            return;
        }

        $archivedData = DB::table('campaign_data_archive')
            ->where('archive_reason', 'feature_removal')
            ->get()
            ->groupBy('table_name');

        foreach ($archivedData as $tableName => $records) {
            if (Schema::hasTable($tableName)) {
                foreach ($records as $record) {
                    $originalData = json_decode($record->original_data, true);
                    DB::table($tableName)->insert($originalData);
                }
                $this->line("Restored data for table: {$tableName}");
            }
        }
    }

    private function removeDeprecationMarkers(): void
    {
        $tables = ['discount_campaigns', 'email_campaigns', 'referral_campaigns'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deprecated_at')) {
                DB::table($table)->update(['deprecated_at' => null]);
                $this->line("Removed deprecation markers from: {$table}");
            }
        }
    }
}
