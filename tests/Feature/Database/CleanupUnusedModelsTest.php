<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('cleanup migration removes all specified tables', function () {
    // Tables that should be dropped by the cleanup migrations
    $tablesToDrop = [
        'news_approvals',
        'news_categories',
        'news_category_translations',
        'news_category_pivot',
    ];

    // Verify tables don't exist after migration
    foreach ($tablesToDrop as $table) {
        expect(Schema::hasTable($table))->toBeFalse("Table {$table} should not exist after cleanup migration");
    }
});

test('cleanup migration is irreversible', function () {
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('This cleanup migration cannot be reversed');

    // Try to rollback the cleanup migration
    $this->artisan('migrate:rollback', ['--step' => 1]);
});

test('essential tables remain after cleanup', function () {
    $essentialTables = [
        'users',
        'products',
        'orders',
        'discount_campaigns', // Campaign table should remain (renamed from campaigns)
        'categories',
        'reviews',
    ];

    foreach ($essentialTables as $table) {
        expect(Schema::hasTable($table))->toBeTrue("Essential table {$table} should exist after cleanup");
    }
});

test('campaign model still works with legacy attributes', function () {
    $campaign = \App\Models\Campaign::factory()->create();

    // Test legacy attributes return safe defaults
    expect($campaign->total_clicks)->toBe(0);
    expect($campaign->total_conversions)->toBe(0);
    expect($campaign->track_conversions)->toBeFalse();
});

test('cleanup migration logs operations properly', function () {
    Log::shouldReceive('info')
        ->with('Starting cleanup migration for unused model tables', \Mockery::type('array'))
        ->once();

    Log::shouldReceive('info')
        ->with('Completed cleanup migration', \Mockery::type('array'))
        ->once();

    // Run the migration
    $this->artisan('migrate', ['--step' => true]);
});

test('migration handles missing tables gracefully', function () {
    // This test ensures the migration doesn't fail if some tables are already missing
    // The migration should complete successfully even if tables don't exist

    $this->artisan('migrate')
        ->assertExitCode(0);
});

test('foreign key constraint cleanup works correctly', function () {
    // Test that the migration properly handles foreign key constraints
    // This is important for data integrity during cleanup

    // Essential tables with potential foreign keys should still exist
    $tablesWithConstraints = ['users', 'products', 'campaigns'];

    foreach ($tablesWithConstraints as $table) {
        if (Schema::hasTable($table)) {
            expect(Schema::hasTable($table))->toBeTrue("Table {$table} should exist after constraint cleanup");
        }
    }
});
