<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class QueryOptimizationService
{
    /**
     * Analyze and optimize a query builder instance
     */
    public function optimizeQuery(Builder $query): array
    {
        $originalSql = $query->toSql();
        $bindings = $query->getBindings();

        return [
            'original' => [
                'sql'      => $originalSql,
                'bindings' => $bindings,
            ],
            'optimizations' => $this->generateOptimizations($query),
        ];
    }

    /**
     * Generate three optimization versions
     */
    private function generateOptimizations(Builder $query): array
    {
        return [
            'eloquent'      => $this->optimizeEloquent($query),
            'query_builder' => $this->optimizeQueryBuilder($query),
            'raw_sql'       => $this->optimizeRawSql($query),
        ];
    }

    /**
     * OPTIMIZATION VERSION 1: Better Eloquent
     */
    private function optimizeEloquent(Builder $query): array
    {
        $model = $query->getModel();
        $optimizations = [];

        // Eager loading optimization
        if ($this->hasRelationshipAccess($query)) {
            $optimizations[] = [
                'type'        => 'eager_loading',
                'description' => 'Use eager loading to prevent N+1 queries',
                'code'        => $this->generateEagerLoadingCode($model),
            ];
        }

        // Select optimization
        $optimizations[] = [
            'type'        => 'select_optimization',
            'description' => 'Only select needed columns',
            'code'        => $this->generateSelectOptimization($model),
        ];

        // Chunking for large datasets
        $optimizations[] = [
            'type'        => 'chunking',
            'description' => 'Use chunking for memory efficiency',
            'code'        => $this->generateChunkingCode($model),
        ];

        return $optimizations;
    }

    /**
     * OPTIMIZATION VERSION 2: Query Builder
     */
    private function optimizeQueryBuilder(Builder $query): array
    {
        $table = $query->getModel()->getTable();

        return [
            [
                'type'        => 'query_builder',
                'description' => 'Use DB facade for better performance',
                'code'        => "DB::table('{$table}')
    ->select(['id', 'name', 'created_at']) // Only needed columns
    ->where('status', 'active')
    ->whereDate('created_at', '>=', now()->subDays(30))
    ->orderBy('created_at', 'desc')
    ->limit(100)
    ->get();",
            ],
            [
                'type'        => 'join_optimization',
                'description' => 'Optimize joins',
                'code'        => "DB::table('{$table}')
    ->join('users', '{$table}.user_id', '=', 'users.id')
    ->select(['{$table}.id', '{$table}.name', 'users.email'])
    ->where('{$table}.status', 'active')
    ->get();",
            ],
        ];
    }

    /**
     * OPTIMIZATION VERSION 3: Raw SQL
     */
    private function optimizeRawSql(Builder $query): array
    {
        $table = $query->getModel()->getTable();

        return [
            [
                'type'        => 'raw_sql',
                'description' => 'Direct SQL for maximum performance',
                'code'        => "DB::select('
                    SELECT id, name, created_at 
                    FROM {$table} 
                    WHERE status = ? 
                        AND created_at >= ? 
                    ORDER BY created_at DESC 
                    LIMIT 100
                ', ['active', now()->subDays(30)]);",
            ],
            [
                'type'        => 'prepared_statement',
                'description' => 'Use prepared statements for repeated queries',
                'code'        => "\$stmt = DB::getPdo()->prepare('
                    SELECT * FROM {$table} 
                    WHERE status = ? AND user_id = ?
                ');
                \$stmt->execute(['active', \$userId]);
                \$results = \$stmt->fetchAll();",
            ],
        ];
    }

    /**
     * Benchmark query performance
     */
    public function benchmarkQuery(callable $queryCallback, int $iterations = 10): array
    {
        $times = [];
        $memoryUsages = [];
        $queryCounts = [];

        for ($i = 0; $i < $iterations; $i++) {
            DB::enableQueryLog();
            $startTime = microtime(true);
            $startMemory = memory_get_usage();
            $startQueries = count(DB::getQueryLog());

            $result = $queryCallback();

            $endTime = microtime(true);
            $endMemory = memory_get_usage();
            $endQueries = count(DB::getQueryLog());

            $times[] = ($endTime - $startTime) * 1000; // Convert to ms
            $memoryUsages[] = ($endMemory - $startMemory) / 1024 / 1024; // Convert to MB
            $queryCounts[] = $endQueries - $startQueries;

            DB::flushQueryLog();
        }

        return [
            'avg_time'    => round(array_sum($times) / count($times), 2),
            'min_time'    => round(min($times), 2),
            'max_time'    => round(max($times), 2),
            'avg_memory'  => round(array_sum($memoryUsages) / count($memoryUsages), 2),
            'avg_queries' => round(array_sum($queryCounts) / count($queryCounts), 1),
            'iterations'  => $iterations,
        ];
    }

    /**
     * Helper methods
     */
    private function hasRelationshipAccess(Builder $query): bool
    {
        // This is a simplified check - in practice, you'd analyze the actual query
        return true;
    }

    private function generateEagerLoadingCode(Model $model): string
    {
        $class = get_class($model);

        return "{$class}::with(['user', 'orders', 'comments'])
    ->where('status', 'active')
    ->get();";
    }

    private function generateSelectOptimization(Model $model): string
    {
        $class = get_class($model);

        return "{$class}::select(['id', 'name', 'status', 'created_at'])
    ->where('status', 'active')
    ->get();";
    }

    private function generateChunkingCode(Model $model): string
    {
        $class = get_class($model);

        return "{$class}::where('status', 'active')
    ->chunk(1000, function (\$records) {
        foreach (\$records as \$record) {
            // Process record
        }
    });";
    }
}
