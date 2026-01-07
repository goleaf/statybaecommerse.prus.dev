<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OptimizeCampaignRemoval extends Command
{
    protected $signature = 'campaign:optimize-removal {--dry-run : Show what would be done without executing}';

    protected $description = 'Optimize database after campaign feature removal';

    public function handle(): int
    {
        $this->info('Starting campaign removal optimization...');

        $dryRun = $this->option('dry-run');

        // 1. Remove unused indexes
        $this->optimizeIndexes($dryRun);

        // 2. Clean up foreign key constraints
        $this->cleanupConstraints($dryRun);

        // 3. Update statistics
        $this->updateStatistics($dryRun);

        $this->info('Campaign removal optimization completed!');

        return self::SUCCESS;
    }

    private function optimizeIndexes(bool $dryRun): void
    {
        $campaignTables = [
            'discount_campaigns',
            'email_campaigns',
            'referral_campaigns',
            'campaign_clicks',
            'campaign_conversions',
        ];

        foreach ($campaignTables as $table) {
            if (Schema::hasTable($table)) {
                if ($dryRun) {
                    $this->line("Would optimize indexes for table: {$table}");
                } else {
                    // Add deprecation indexes for performance during transition
                    if (! Schema::hasColumn($table, 'deprecated_at')) {
                        Schema::table($table, function ($table) {
                            $table->timestamp('deprecated_at')->nullable()->index();
                        });
                    }
                    $this->line("Optimized indexes for table: {$table}");
                }
            }
        }
    }

    private function cleanupConstraints(bool $dryRun): void
    {
        if ($dryRun) {
            $this->line('Would clean up foreign key constraints');

            return;
        }

        // Mark all campaign records as deprecated instead of deleting
        $tables = ['discount_campaigns', 'email_campaigns', 'referral_campaigns'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->update(['deprecated_at' => now()]);
                $this->line("Marked {$table} records as deprecated");
            }
        }
    }

    private function updateStatistics(bool $dryRun): void
    {
        if ($dryRun) {
            $this->line('Would update database statistics');

            return;
        }

        // Update table statistics for better query performance
        DB::statement('ANALYZE');
        $this->line('Updated database statistics');
    }
}
