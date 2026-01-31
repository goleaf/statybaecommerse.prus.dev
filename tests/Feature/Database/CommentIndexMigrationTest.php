<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('Comment Index Migration', function () {
    it('creates proper composite indexes for polymorphic relationships', function () {
        // Verify the composite indexes exist
        $indexes = DB::select("PRAGMA index_list('comments')");
        $indexNames = collect($indexes)->pluck('name')->toArray();

        expect($indexNames)->toContain('comments_commentable_index');
        expect($indexNames)->toContain('comments_commentable_approved_index');
        expect($indexNames)->toContain('comments_commentable_created_index');
        expect($indexNames)->toContain('comments_commentable_parent_index');
    });

    it('verifies index column composition for optimal query performance', function () {
        // Check the main composite index
        $indexInfo = DB::select("PRAGMA index_info('comments_commentable_index')");
        $columns = collect($indexInfo)->pluck('name')->toArray();

        expect($columns)->toContain('commentable_type');
        expect($columns)->toContain('commentable_id');

        // Verify column order (type first, then id for optimal selectivity)
        expect($indexInfo[0]->name)->toBe('commentable_type');
        expect($indexInfo[1]->name)->toBe('commentable_id');
    });

    it('ensures indexes support common query patterns', function () {
        // Test that EXPLAIN QUERY PLAN shows index usage
        $explainResult = DB::select('
            EXPLAIN QUERY PLAN 
            SELECT * FROM comments 
            WHERE commentable_type = ? AND commentable_id = ?
        ', ['App\\Models\\Project', 1]);

        $planText = collect($explainResult)->pluck('detail')->implode(' ');
        expect($planText)->toContain('comments_commentable_index');
    });

    it('validates index performance with large dataset', function () {
        // Insert test data
        $testData = [];
        for ($i = 1; $i <= 1000; $i++) {
            $testData[] = [
                'content'          => "Test comment {$i}",
                'user_id'          => 1,
                'commentable_type' => 'App\\Models\\Project',
                'commentable_id'   => ($i % 10) + 1, // Distribute across 10 tasks
                'is_approved'      => true,
                'is_pinned'        => false,
                'likes_count'      => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        DB::table('comments')->insert($testData);

        // Measure query performance
        $startTime = microtime(true);

        $result = DB::table('comments')
            ->where('commentable_type', 'App\\Models\\Project')
            ->where('commentable_id', 1)
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        expect($result)->toHaveCount(20);
        expect($executionTime)->toBeLessThan(0.1); // Should be very fast with proper indexing
    });

    it('ensures backward compatibility with existing queries', function () {
        // Test that existing query patterns still work
        $queries = [
            'SELECT * FROM comments WHERE parent_id IS NULL',
            'SELECT * FROM comments WHERE user_id = 1 ORDER BY created_at DESC',
            "SELECT * FROM comments WHERE commentable_type = 'App\\Models\\Project'",
        ];

        foreach ($queries as $query) {
            $result = DB::select($query);
            expect($result)->toBeArray(); // Should execute without error
        }
    });
});

describe('Index Performance Properties', function () {
    it('maintains consistent query performance regardless of data distribution', function () {
        // Property: Query time should remain consistent even with skewed data
        $times = [];

        for ($projectId = 1; $projectId <= 5; $projectId++) {
            // Create varying amounts of comments per task
            $commentCount = $projectId * 100; // 100, 200, 300, 400, 500 comments

            $testData = [];
            for ($i = 1; $i <= $commentCount; $i++) {
                $testData[] = [
                    'content'          => "Comment {$i} for project {$projectId}",
                    'user_id'          => 1,
                    'commentable_type' => 'App\\Models\\Project',
                    'commentable_id'   => $projectId,
                    'is_approved'      => true,
                    'is_pinned'        => false,
                    'likes_count'      => 0,
                    'created_at'       => now()->subMinutes($i),
                    'updated_at'       => now()->subMinutes($i),
                ];
            }

            DB::table('comments')->insert($testData);

            // Measure query time
            $startTime = microtime(true);
            DB::table('comments')
                ->where('commentable_type', 'App\\Models\\Project')
                ->where('commentable_id', $projectId)
                ->where('is_approved', true)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            $times[] = microtime(true) - $startTime;
        }

        // Verify performance consistency (no query should be more than 2x slower than the fastest)
        $minTime = min($times);
        $maxTime = max($times);

        expect($maxTime / $minTime)->toBeLessThan(2.0);
    });

    it('supports efficient pagination without performance degradation', function () {
        // Property: Pagination performance should remain stable across pages

        // Insert 1000 comments for a single task
        $testData = [];
        for ($i = 1; $i <= 1000; $i++) {
            $testData[] = [
                'content'          => "Paginated comment {$i}",
                'user_id'          => 1,
                'commentable_type' => 'App\\Models\\Project',
                'commentable_id'   => 1,
                'is_approved'      => true,
                'is_pinned'        => false,
                'likes_count'      => 0,
                'created_at'       => now()->subMinutes($i),
                'updated_at'       => now()->subMinutes($i),
            ];
        }

        DB::table('comments')->insert($testData);

        $pageTimes = [];
        $perPage = 20;

        // Test first, middle, and last pages
        $offsets = [0, 500, 980]; // Pages 1, 26, 50

        foreach ($offsets as $offset) {
            $startTime = microtime(true);

            DB::table('comments')
                ->where('commentable_type', 'App\\Models\\Project')
                ->where('commentable_id', 1)
                ->where('is_approved', true)
                ->orderBy('created_at', 'desc')
                ->offset($offset)
                ->limit($perPage)
                ->get();

            $pageTimes[] = microtime(true) - $startTime;
        }

        // All page queries should complete in reasonable time
        foreach ($pageTimes as $time) {
            expect($time)->toBeLessThan(0.1);
        }

        // Performance should not degrade significantly for later pages
        $firstPageTime = $pageTimes[0];
        $lastPageTime = $pageTimes[2];

        expect($lastPageTime / $firstPageTime)->toBeLessThan(3.0);
    });
});
