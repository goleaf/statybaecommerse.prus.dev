<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Optimize SQLite Database Command
 *
 * Applies production-ready SQLite optimizations and displays current settings.
 */
final class OptimizeSqliteCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sqlite:optimize 
                            {--check : Only check current SQLite settings without applying optimizations}
                            {--force : Force apply optimizations even if already optimized}
                            {--vacuum : Run incremental vacuum to reclaim space}';

    /**
     * The console command description.
     */
    protected $description = 'Apply SQLite optimizations for production use or check current settings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (config('database.default') !== 'sqlite') {
            $this->error('Current database connection is not SQLite. This command only works with SQLite.');

            return 1;
        }

        if ($this->option('check')) {
            return $this->checkCurrentSettings();
        }

        if ($this->option('vacuum')) {
            return $this->runVacuum();
        }

        return $this->applyOptimizations();
    }

    /**
     * Check current SQLite settings.
     */
    private function checkCurrentSettings(): int
    {
        $this->info('Checking current SQLite settings...');
        $this->newLine();

        try {
            $settings = [
                'journal_mode' => DB::connection('sqlite')->getPdo()->query('PRAGMA journal_mode')->fetchColumn(),
                'busy_timeout' => DB::connection('sqlite')->getPdo()->query('PRAGMA busy_timeout')->fetchColumn(),
                'cache_size' => DB::connection('sqlite')->getPdo()->query('PRAGMA cache_size')->fetchColumn(),
                'temp_store' => DB::connection('sqlite')->getPdo()->query('PRAGMA temp_store')->fetchColumn(),
                'mmap_size' => DB::connection('sqlite')->getPdo()->query('PRAGMA mmap_size')->fetchColumn(),
                'page_size' => DB::connection('sqlite')->getPdo()->query('PRAGMA page_size')->fetchColumn(),
                'auto_vacuum' => DB::connection('sqlite')->getPdo()->query('PRAGMA auto_vacuum')->fetchColumn(),
                'synchronous' => DB::connection('sqlite')->getPdo()->query('PRAGMA synchronous')->fetchColumn(),
                'foreign_keys' => DB::connection('sqlite')->getPdo()->query('PRAGMA foreign_keys')->fetchColumn(),
            ];

            $this->table(
                ['Setting', 'Current Value', 'Recommended Value', 'Status'],
                [
                    ['journal_mode', $settings['journal_mode'], 'wal', $settings['journal_mode'] === 'wal' ? '✅' : '❌'],
                    ['busy_timeout', $settings['busy_timeout'], '10000', $settings['busy_timeout'] == 10000 ? '✅' : '❌'],
                    ['cache_size', $settings['cache_size'], '-64000', $settings['cache_size'] == -64000 ? '✅' : '❌'],
                    ['temp_store', $settings['temp_store'], '2 (memory)', $settings['temp_store'] == 2 ? '✅' : '❌'],
                    ['mmap_size', $settings['mmap_size'], '268435456', $settings['mmap_size'] == 268435456 ? '✅' : '❌'],
                    ['page_size', $settings['page_size'], '4096', $settings['page_size'] == 4096 ? '✅' : '❌'],
                    ['auto_vacuum', $settings['auto_vacuum'], '2 (incremental)', $settings['auto_vacuum'] == 2 ? '✅' : '❌'],
                    ['synchronous', $settings['synchronous'], '1 (normal)', $settings['synchronous'] == 1 ? '✅' : '❌'],
                    ['foreign_keys', $settings['foreign_keys'], '1 (on)', $settings['foreign_keys'] == 1 ? '✅' : '❌'],
                ]
            );

            $this->newLine();
            $this->info('Legend: ✅ = Optimized, ❌ = Needs optimization');

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to check SQLite settings: '.$e->getMessage());

            return 1;
        }
    }

    /**
     * Apply SQLite optimizations.
     */
    private function applyOptimizations(): int
    {
        $this->info('Applying SQLite optimizations...');
        $this->newLine();

        try {
            $optimizations = [
                'journal_mode' => 'WAL',
                'busy_timeout' => 10000,
                'cache_size' => -64000,
                'temp_store' => 2, // memory
                'mmap_size' => 268435456,
                'page_size' => 4096,
                'synchronous' => 1, // normal
                'foreign_keys' => 1, // on
            ];

            $pdo = DB::connection('sqlite')->getPdo();

            foreach ($optimizations as $setting => $value) {
                $pdo->exec("PRAGMA {$setting} = {$value}");
                $this->line("✅ Set {$setting} = {$value}");
            }

            // Check if auto_vacuum can be set (only works on empty databases)
            $autoVacuumResult = $pdo->query('PRAGMA auto_vacuum')->fetchColumn();
            if ($autoVacuumResult == 0) {
                $this->line("⚠️  auto_vacuum cannot be changed on existing database (current: {$autoVacuumResult})");
                $this->line('   To enable auto_vacuum, you would need to recreate the database.');
                $this->line("   For now, you can run 'PRAGMA incremental_vacuum' manually when needed.");
            } else {
                $this->line("✅ auto_vacuum is already set to: {$autoVacuumResult}");
            }

            // Run optimize command
            $pdo->exec('PRAGMA optimize');
            $this->line('✅ Ran PRAGMA optimize');

            $this->newLine();
            $this->info('SQLite optimizations applied successfully!');
            $this->info('These optimizations will improve performance for production use.');

            Log::info('SQLite optimizations applied via artisan command');

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to apply SQLite optimizations: '.$e->getMessage());
            Log::error('Failed to apply SQLite optimizations: '.$e->getMessage());

            return 1;
        }
    }

    /**
     * Run incremental vacuum to reclaim space.
     */
    private function runVacuum(): int
    {
        $this->info('Running incremental vacuum...');
        $this->newLine();

        try {
            $pdo = DB::connection('sqlite')->getPdo();

            // Get database size before vacuum
            $sizeBefore = $pdo->query('SELECT page_count * page_size as size FROM pragma_page_count(), pragma_page_size()')->fetchColumn();
            $this->line('Database size before vacuum: '.number_format($sizeBefore / 1024 / 1024, 2).' MB');

            // Run incremental vacuum
            $pdo->exec('PRAGMA incremental_vacuum');
            $this->line('✅ Incremental vacuum completed');

            // Get database size after vacuum
            $sizeAfter = $pdo->query('SELECT page_count * page_size as size FROM pragma_page_count(), pragma_page_size()')->fetchColumn();
            $this->line('Database size after vacuum: '.number_format($sizeAfter / 1024 / 1024, 2).' MB');

            $spaceReclaimed = $sizeBefore - $sizeAfter;
            if ($spaceReclaimed > 0) {
                $this->line('Space reclaimed: '.number_format($spaceReclaimed / 1024 / 1024, 2).' MB');
            } else {
                $this->line('No space was reclaimed (database was already optimized)');
            }

            $this->newLine();
            $this->info('Incremental vacuum completed successfully!');

            Log::info('SQLite incremental vacuum completed via artisan command');

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to run incremental vacuum: '.$e->getMessage());
            Log::error('Failed to run incremental vacuum: '.$e->getMessage());

            return 1;
        }
    }
}
