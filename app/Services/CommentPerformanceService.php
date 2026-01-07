<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Comment;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for monitoring and optimizing comment query performance.
 */
final class CommentPerformanceService
{
    /**
     * Analyze query performance for a given entity's comments.
     */
    public function analyzeEntityCommentPerformance(Model $entity): array
    {
        $startTime = microtime(true);

        // Test basic entity query
        $basicQuery = Comment::forEntity($entity);
        $basicCount = $basicQuery->count();
        $basicTime = microtime(true) - $startTime;

        // Test approved entity query
        $startTime = microtime(true);
        $approvedQuery = Comment::approvedForEntity($entity);
        $approvedCount = $approvedQuery->count();
        $approvedTime = microtime(true) - $startTime;

        // Test paginated query
        $startTime = microtime(true);
        $paginatedQuery = Comment::paginatedForEntity($entity, 20);
        $paginatedResults = $paginatedQuery->get();
        $paginatedTime = microtime(true) - $startTime;

        // Analyze query plans (SQLite specific)
        $explainResults = $this->analyzeQueryPlans($entity);

        return [
            'entity_type' => $entity->getMorphClass(),
            'entity_id'   => $entity->getKey(),
            'metrics'     => [
                'basic_query' => [
                    'count'   => $basicCount,
                    'time_ms' => round($basicTime * 1000, 2),
                ],
                'approved_query' => [
                    'count'   => $approvedCount,
                    'time_ms' => round($approvedTime * 1000, 2),
                ],
                'paginated_query' => [
                    'count'   => $paginatedResults->count(),
                    'time_ms' => round($paginatedTime * 1000, 2),
                ],
            ],
            'query_plans'     => $explainResults,
            'recommendations' => $this->generateRecommendations($basicTime, $approvedTime, $paginatedTime, $basicCount),
        ];
    }

    /**
     * Analyze query execution plans for comment queries.
     */
    private function analyzeQueryPlans(Model $entity): array
    {
        $plans = [];

        try {
            // Analyze basic entity query plan
            $basicPlan = DB::select('EXPLAIN QUERY PLAN ' . Comment::forEntity($entity)->toSql(), [
                $entity->getMorphClass(),
                $entity->getKey(),
            ]);
            $plans['basic_entity'] = $basicPlan;

            // Analyze approved entity query plan
            $approvedPlan = DB::select('EXPLAIN QUERY PLAN ' . Comment::approvedForEntity($entity)->toSql(), [
                $entity->getMorphClass(),
                $entity->getKey(),
                true,
            ]);
            $plans['approved_entity'] = $approvedPlan;

        } catch (Exception $e) {
            Log::warning('Failed to analyze comment query plans', [
                'entity_type' => $entity->getMorphClass(),
                'entity_id'   => $entity->getKey(),
                'error'       => $e->getMessage(),
            ]);
        }

        return $plans;
    }

    /**
     * Generate performance recommendations based on metrics.
     */
    private function generateRecommendations(float $basicTime, float $approvedTime, float $paginatedTime, int $commentCount): array
    {
        $recommendations = [];

        // Check for slow queries
        if ($basicTime > 0.1) {
            $recommendations[] = [
                'type'     => 'performance',
                'severity' => 'high',
                'message'  => 'Basic entity query is slow (' . round($basicTime * 1000, 2) . 'ms). Consider adding composite indexes.',
            ];
        }

        if ($approvedTime > 0.15) {
            $recommendations[] = [
                'type'     => 'performance',
                'severity' => 'high',
                'message'  => 'Approved entity query is slow (' . round($approvedTime * 1000, 2) . 'ms). Ensure composite index includes is_approved column.',
            ];
        }

        if ($paginatedTime > 0.2) {
            $recommendations[] = [
                'type'     => 'performance',
                'severity' => 'medium',
                'message'  => 'Paginated query is slow (' . round($paginatedTime * 1000, 2) . 'ms). Consider optimizing eager loading.',
            ];
        }

        // Check for large datasets without pagination
        if ($commentCount > 1000) {
            $recommendations[] = [
                'type'     => 'optimization',
                'severity' => 'medium',
                'message'  => 'Large comment dataset (' . $commentCount . ' comments). Consider implementing pagination or limiting results.',
            ];
        }

        // Performance ratio checks
        if ($approvedTime > $basicTime * 2) {
            $recommendations[] = [
                'type'     => 'index',
                'severity' => 'medium',
                'message'  => 'Approved query is significantly slower than basic query. Check composite index coverage.',
            ];
        }

        if (empty($recommendations)) {
            $recommendations[] = [
                'type'     => 'success',
                'severity' => 'info',
                'message'  => 'Comment queries are performing well within acceptable thresholds.',
            ];
        }

        return $recommendations;
    }

    /**
     * Validate that required indexes exist for optimal performance.
     */
    public function validateIndexes(): array
    {
        $requiredIndexes = [
            'comments_commentable_index'          => ['commentable_type', 'commentable_id'],
            'comments_commentable_approved_index' => ['commentable_type', 'commentable_id', 'is_approved'],
            'comments_commentable_created_index'  => ['commentable_type', 'commentable_id', 'created_at'],
            'comments_commentable_parent_index'   => ['commentable_type', 'commentable_id', 'parent_id'],
        ];

        $existingIndexes = [];
        $missingIndexes = [];

        try {
            $indexes = DB::select("PRAGMA index_list('comments')");
            $indexNames = collect($indexes)->pluck('name')->toArray();

            foreach ($requiredIndexes as $indexName => $columns) {
                if (in_array($indexName, $indexNames, true)) {
                    // Verify index column composition
                    $indexInfo = DB::select("PRAGMA index_info('{$indexName}')");
                    $indexColumns = collect($indexInfo)->pluck('name')->toArray();

                    $existingIndexes[$indexName] = [
                        'expected_columns' => $columns,
                        'actual_columns'   => $indexColumns,
                        'matches'          => $indexColumns === $columns,
                    ];
                } else {
                    $missingIndexes[] = $indexName;
                }
            }
        } catch (Exception $e) {
            Log::error('Failed to validate comment indexes', [
                'error' => $e->getMessage(),
            ]);
        }

        return [
            'existing_indexes'  => $existingIndexes,
            'missing_indexes'   => $missingIndexes,
            'validation_passed' => empty($missingIndexes),
        ];
    }

    /**
     * Monitor comment query performance in real-time.
     */
    public function monitorQueryPerformance(callable $queryCallback, string $queryType = 'unknown'): array
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        try {
            $result = $queryCallback();
            $success = true;
            $error = null;
        } catch (Exception $e) {
            $result = null;
            $success = false;
            $error = $e->getMessage();
        }

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);

        $metrics = [
            'query_type'        => $queryType,
            'execution_time_ms' => round(($endTime - $startTime) * 1000, 2),
            'memory_usage_mb'   => round(($endMemory - $startMemory) / 1024 / 1024, 2),
            'success'           => $success,
            'error'             => $error,
            'timestamp'         => now()->toISOString(),
        ];

        // Log slow queries
        if ($metrics['execution_time_ms'] > 100) {
            Log::warning('Slow comment query detected', $metrics);
        }

        return [
            'result'  => $result,
            'metrics' => $metrics,
        ];
    }
}
