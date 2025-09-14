<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SQLite Optimization Service Provider
 * 
 * Applies production-ready SQLite optimizations based on Laravel News recommendations.
 * This provider configures SQLite for optimal performance in production environments.
 */
final class SqliteOptimizationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->optimizeSqlite();
        }
    }

    /**
     * Apply SQLite optimizations for production use.
     */
    private function optimizeSqlite(): void
    {
        try {
            DB::connection('sqlite')->getPdo()->exec('
                -- Enable Write-Ahead Logging (WAL) mode for better concurrency
                PRAGMA journal_mode = WAL;
                
                -- Set busy timeout to 10 seconds (10,000 milliseconds)
                PRAGMA busy_timeout = 10000;
                
                -- Set cache size to 64MB (negative value means KB)
                PRAGMA cache_size = -64000;
                
                -- Store temporary tables in memory for better performance
                PRAGMA temp_store = memory;
                
                -- Enable memory mapping for better I/O performance
                PRAGMA mmap_size = 268435456;
                
                -- Set page size to 4KB for optimal performance
                PRAGMA page_size = 4096;
                
                -- Enable incremental auto-vacuum for better space management
                PRAGMA auto_vacuum = incremental;
                
                -- Set synchronous mode to normal (balance between safety and performance)
                PRAGMA synchronous = normal;
                
                -- Enable foreign key constraints
                PRAGMA foreign_keys = on;
                
                -- Optimize for performance
                PRAGMA optimize;
            ');

            Log::info('SQLite optimizations applied successfully');
        } catch (\Exception $e) {
            Log::error('Failed to apply SQLite optimizations: ' . $e->getMessage());
        }
    }
}
