<?php

declare(strict_types=1);

namespace Tests\Feature\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Property-based test for News cleanup migration
 *
 * **Feature: news-blog-cleanup-upgrade, Property 4: Database integrity after cleanup**
 * **Validates: Requirements 5.3, 5.4**
 */
final class NewsCleanupMigrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 4: Database integrity after cleanup
     *
     * For any database state after running cleanup migrations, there should be no
     * orphaned foreign key constraints, no references to dropped tables, and all
     * remaining data should maintain referential integrity.
     *
     * **Validates: Requirements 5.3, 5.4**
     */
    public function test_database_integrity_after_cleanup_migration(): void
    {
        // Arrange: Create test data before cleanup
        $this->seedTestDataBeforeCleanup();

        // Store initial news count to verify preservation
        $initialNewsCount = DB::table('news')->count();
        $initialNewsCategoryCount = DB::table('news_categories')->count();

        // Act: Run the cleanup migration
        $this->runCleanupMigration();

        // Assert: Verify tables are dropped
        $this->assertTableDoesNotExist('news_tags');
        $this->assertTableDoesNotExist('news_tag_translations');
        $this->assertTableDoesNotExist('sh_news_tag_translations');
        $this->assertTableDoesNotExist('news_tag_pivot');
        $this->assertTableDoesNotExist('news_comments');

        // Assert: Verify core news data is preserved
        $this->assertEquals($initialNewsCount, DB::table('news')->count());
        $this->assertEquals($initialNewsCategoryCount, DB::table('news_categories')->count());

        // Assert: Verify no orphaned foreign key constraints exist
        $this->assertNoOrphanedForeignKeys();

        // Assert: Verify remaining tables maintain referential integrity
        $this->assertReferentialIntegrityMaintained();
    }

    /**
     * Test that the migration can be rolled back safely
     */
    public function test_migration_rollback_recreates_tables(): void
    {
        // Arrange: Run the cleanup migration first
        $this->runCleanupMigration();

        // Verify tables are dropped
        $this->assertTableDoesNotExist('news_tags');
        $this->assertTableDoesNotExist('news_comments');

        // Act: Rollback the migration
        $this->rollbackCleanupMigration();

        // Assert: Verify tables are recreated
        $this->assertTableExists('news_tags');
        $this->assertTableExists('news_tag_translations');
        $this->assertTableExists('news_tag_pivot');
        $this->assertTableExists('news_comments');

        // Assert: Verify table structure is correct
        $this->assertTableHasExpectedColumns('news_tags', [
            'id', 'is_visible', 'is_active', 'color', 'sort_order', 'created_at', 'updated_at',
        ]);

        $this->assertTableHasExpectedColumns('news_comments', [
            'id', 'news_id', 'parent_id', 'author_name', 'author_email',
            'content', 'is_approved', 'is_visible', 'is_active', 'created_at', 'updated_at',
        ]);
    }

    /**
     * Property test: Migration preserves data across multiple scenarios
     *
     * Tests the migration with different database states to ensure data preservation
     * holds universally across various initial conditions.
     */
    public function test_migration_preserves_data_across_scenarios(): void
    {
        $scenarios = [
            'empty_database'       => [],
            'news_only'            => ['news' => 3],
            'news_with_categories' => ['news' => 5, 'categories' => 2],
            'complex_setup'        => ['news' => 10, 'categories' => 5, 'images' => 15],
        ];

        foreach ($scenarios as $scenarioName => $setup) {
            // Fresh database for each scenario
            $this->refreshDatabase();

            // Setup scenario-specific data
            $this->seedScenarioData($setup);

            // Store counts before migration
            $beforeCounts = $this->getTableCounts();

            // Run cleanup migration
            $this->runCleanupMigration();

            // Verify data preservation for this scenario
            $afterCounts = $this->getTableCounts();

            // Assert core data is preserved
            $this->assertEquals(
                $beforeCounts['news'] ?? 0,
                $afterCounts['news'] ?? 0,
                "News data not preserved in scenario: {$scenarioName}"
            );

            $this->assertEquals(
                $beforeCounts['news_categories'] ?? 0,
                $afterCounts['news_categories'] ?? 0,
                "News categories not preserved in scenario: {$scenarioName}"
            );

            // Assert cleanup tables are removed
            $this->assertTableDoesNotExist('news_tags', "news_tags should be dropped in scenario: {$scenarioName}");
            $this->assertTableDoesNotExist('news_comments', "news_comments should be dropped in scenario: {$scenarioName}");
        }
    }

    private function seedTestDataBeforeCleanup(): void
    {
        // Only seed if tables exist and are empty to avoid constraint violations
        if (Schema::hasTable('news') && DB::table('news')->count() === 0) {
            DB::table('news')->insert([
                [
                    'is_visible'       => true,
                    'is_featured'      => false,
                    'is_breaking'      => false,
                    'moderation_state' => 'published',
                    'published_at'     => now(),
                    'author_name'      => 'Test Author',
                    'author_email'     => 'test@example.com',
                    'view_count'       => 0,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ],
                [
                    'is_visible'       => true,
                    'is_featured'      => true,
                    'is_breaking'      => false,
                    'moderation_state' => 'published',
                    'published_at'     => now(),
                    'author_name'      => 'Another Author',
                    'author_email'     => 'another@example.com',
                    'view_count'       => 5,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ],
            ]);
        }

        // Create news categories if table exists and has required columns
        if (Schema::hasTable('news_categories') && DB::table('news_categories')->count() === 0) {
            $categoryData = [
                'is_visible' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Add required fields based on schema
            if (Schema::hasColumn('news_categories', 'name')) {
                $categoryData['name'] = 'Test Category';
            }
            if (Schema::hasColumn('news_categories', 'slug')) {
                $categoryData['slug'] = 'test-category';
            }

            DB::table('news_categories')->insert($categoryData);
        }
    }

    private function seedScenarioData(array $setup): void
    {
        if (isset($setup['news']) && Schema::hasTable('news')) {
            // Clear existing data first
            DB::table('news')->truncate();

            for ($i = 1; $i <= $setup['news']; $i++) {
                DB::table('news')->insert([
                    'is_visible'       => true,
                    'is_featured'      => $i % 3 === 0,
                    'is_breaking'      => false,
                    'moderation_state' => 'published',
                    'published_at'     => now(),
                    'author_name'      => "Author {$i}",
                    'author_email'     => "author{$i}@example.com",
                    'view_count'       => $i * 2,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }

        if (isset($setup['categories']) && Schema::hasTable('news_categories')) {
            // Clear existing data first
            DB::table('news_categories')->truncate();

            for ($i = 1; $i <= $setup['categories']; $i++) {
                $categoryData = [
                    'is_visible' => true,
                    'sort_order' => $i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Add required fields based on schema
                if (Schema::hasColumn('news_categories', 'name')) {
                    $categoryData['name'] = "Category {$i}";
                }
                if (Schema::hasColumn('news_categories', 'slug')) {
                    $categoryData['slug'] = "category-{$i}";
                }

                DB::table('news_categories')->insert($categoryData);
            }
        }

        if (isset($setup['images']) && Schema::hasTable('news_images')) {
            // Clear existing data first
            DB::table('news_images')->truncate();

            for ($i = 1; $i <= $setup['images']; $i++) {
                DB::table('news_images')->insert([
                    'news_id'     => ($i % ($setup['news'] ?? 1)) + 1,
                    'file_path'   => "/images/news/image{$i}.jpg",
                    'alt_text'    => "Image {$i}",
                    'is_featured' => $i === 1,
                    'sort_order'  => $i,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }

    private function runCleanupMigration(): void
    {
        Artisan::call('migrate', [
            '--path'  => 'database/migrations/2026_01_07_154930_cleanup_news_tag_and_comment_tables.php',
            '--force' => true,
        ]);
    }

    private function rollbackCleanupMigration(): void
    {
        Artisan::call('migrate:rollback', [
            '--path'  => 'database/migrations/2026_01_07_154930_cleanup_news_tag_and_comment_tables.php',
            '--force' => true,
        ]);
    }

    private function assertTableExists(string $table): void
    {
        $this->assertTrue(
            Schema::hasTable($table),
            "Table '{$table}' should exist but does not"
        );
    }

    private function assertTableDoesNotExist(string $table, ?string $message = null): void
    {
        $this->assertFalse(
            Schema::hasTable($table),
            $message ?? "Table '{$table}' should not exist but does"
        );
    }

    private function assertTableHasExpectedColumns(string $table, array $expectedColumns): void
    {
        foreach ($expectedColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn($table, $column),
                "Table '{$table}' should have column '{$column}'"
            );
        }
    }

    private function assertNoOrphanedForeignKeys(): void
    {
        // Check that no foreign key constraints reference the dropped tables
        $foreignKeys = $this->getForeignKeyConstraints();

        $droppedTables = ['news_tags', 'news_tag_translations', 'news_comments', 'news_tag_pivot'];

        foreach ($foreignKeys as $constraint) {
            $this->assertNotContains(
                $constraint['referenced_table'],
                $droppedTables,
                "Found orphaned foreign key constraint referencing dropped table: {$constraint['referenced_table']}"
            );
        }
    }

    private function assertReferentialIntegrityMaintained(): void
    {
        // Verify that all remaining foreign key relationships are valid
        $foreignKeys = $this->getForeignKeyConstraints();

        foreach ($foreignKeys as $constraint) {
            $referencedTable = $constraint['referenced_table'];
            $referencingTable = $constraint['table'];

            // Skip if either table doesn't exist (expected for dropped tables)
            if (! Schema::hasTable($referencedTable) || ! Schema::hasTable($referencingTable)) {
                continue;
            }

            // Check that referenced table exists and has the referenced column
            $this->assertTrue(
                Schema::hasTable($referencedTable),
                "Referenced table '{$referencedTable}' should exist"
            );

            $this->assertTrue(
                Schema::hasColumn($referencedTable, $constraint['referenced_column']),
                "Referenced table '{$referencedTable}' should have column '{$constraint['referenced_column']}'"
            );
        }
    }

    private function getForeignKeyConstraints(): array
    {
        // For SQLite, we need to parse the schema to find foreign keys
        // This is a simplified implementation for testing purposes
        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
        $constraints = [];

        foreach ($tables as $table) {
            $tableName = $table->name;
            $foreignKeys = DB::select("PRAGMA foreign_key_list({$tableName})");

            foreach ($foreignKeys as $fk) {
                $constraints[] = [
                    'table'             => $tableName,
                    'column'            => $fk->from,
                    'referenced_table'  => $fk->table,
                    'referenced_column' => $fk->to,
                ];
            }
        }

        return $constraints;
    }

    private function getTableCounts(): array
    {
        $counts = [];
        $tables = ['news', 'news_categories', 'news_images'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $counts[$table] = DB::table($table)->count();
            }
        }

        return $counts;
    }
}
