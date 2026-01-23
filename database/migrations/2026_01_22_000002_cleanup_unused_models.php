<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables to be dropped by this cleanup migration.
     *
     * These tables correspond to removed models and functionality:
     * - news_approvals, news_categories, news_category_translations, news_category_pivot: News system components (deprecated)
     */
    private const TABLES_TO_DROP = [
        'news_approvals',
        'news_categories',
        'news_category_translations',
        'news_category_pivot',
    ];

    /**
     * Foreign key constraints that may reference dropped tables.
     */
    private const CONSTRAINT_CLEANUP_MAP = [
        'news' => [
            'news_approvals_news_id_foreign',
            'news_category_pivot_news_id_foreign',
        ],
        // no user-related audit constraints remain
    ];

    public function up(): void
    {
        Log::info('Starting cleanup migration for unused model tables', [
            'tables_to_drop' => self::TABLES_TO_DROP,
            'migration'      => '2026_01_22_000002_cleanup_unused_models',
        ]);

        $droppedTables = [];
        $skippedTables = [];

        // Drop tables in dependency order to avoid foreign key constraint issues
        foreach (self::TABLES_TO_DROP as $table) {
            if (Schema::hasTable($table)) {
                try {
                    // Check if table has data (for logging purposes)
                    $recordCount = DB::table($table)->count();

                    Schema::dropIfExists($table);
                    $droppedTables[] = $table;

                    Log::info("Dropped table: {$table}", [
                        'record_count' => $recordCount,
                        'migration'    => '2026_01_22_000002_cleanup_unused_models',
                    ]);
                } catch (\Exception $e) {
                    Log::warning("Failed to drop table: {$table}", [
                        'error'     => $e->getMessage(),
                        'migration' => '2026_01_22_000002_cleanup_unused_models',
                    ]);
                    $skippedTables[] = $table;
                }
            } else {
                $skippedTables[] = $table;
            }
        }

        // Clean up any remaining foreign key constraints
        $this->cleanupOrphanedConstraints();

        Log::info('Completed cleanup migration', [
            'dropped_tables' => $droppedTables,
            'skipped_tables' => $skippedTables,
            'migration'      => '2026_01_22_000002_cleanup_unused_models',
        ]);
    }

    public function down(): void
    {
        throw new \RuntimeException(
            'This cleanup migration cannot be reversed. ' .
            'The removed tables contained deprecated functionality that has been permanently removed. ' .
            'If you need to restore data, use a database backup from before this migration was run.'
        );
    }

    /**
     * Clean up orphaned foreign key constraints that might reference dropped tables.
     */
    private function cleanupOrphanedConstraints(): void
    {
        foreach (self::CONSTRAINT_CLEANUP_MAP as $table => $constraints) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $tableBlueprint) use ($constraints, $table) {
                foreach ($constraints as $constraint) {
                    try {
                        // Check if constraint exists before trying to drop it
                        $constraintExists = $this->constraintExists($table, $constraint);

                        if ($constraintExists) {
                            $tableBlueprint->dropForeign($constraint);
                            Log::info("Dropped orphaned constraint: {$constraint} from table: {$table}");
                        }
                    } catch (\Exception $e) {
                        // Log but don't fail - constraint might not exist or already be dropped
                        Log::debug("Could not drop constraint {$constraint} from {$table}: {$e->getMessage()}");
                    }
                }
            });
        }
    }

    /**
     * Check if a foreign key constraint exists on a table.
     */
    private function constraintExists(string $table, string $constraint): bool
    {
        try {
            // For SQLite, we need to check the schema differently
            if (DB::getDriverName() === 'sqlite') {
                $sql = "SELECT sql FROM sqlite_master WHERE type='table' AND name=?";
                $result = DB::selectOne($sql, [$table]);

                return $result && str_contains($result->sql, $constraint);
            }

            // For MySQL/PostgreSQL
            $constraints = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
                WHERE TABLE_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ", [$table]);

            return collect($constraints)->pluck('CONSTRAINT_NAME')->contains($constraint);
        } catch (\Exception) {
            return false;
        }
    }
};
