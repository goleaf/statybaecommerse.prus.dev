<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Optimizes database queries for better performance.
 */
final class QueryOptimizationService
{
    private const BATCH_SIZE = 1000;

    /**
     * Optimize query with selective field loading.
     */
    public function selectOptimized(Builder $query, array $fields): Builder
    {
        // Always include primary key and timestamps for Eloquent compatibility
        $model = $query->getModel();
        $keyName = $model->getKeyName();
        $table = $model->getTable();

        $optimizedFields = array_unique(array_merge(
            [$table . '.' . $keyName],
            $this->qualifyFields($fields, $table),
            $model->usesTimestamps() ? [
                $table . '.' . $model->getCreatedAtColumn(),
                $table . '.' . $model->getUpdatedAtColumn(),
            ] : []
        ));

        return $query->select($optimizedFields);
    }

    /**
     * Batch process large datasets to prevent memory exhaustion.
     */
    public function batchProcess(Builder $query, callable $callback, ?int $batchSize = null): int
    {
        $batchSize ??= self::BATCH_SIZE;
        $processed = 0;

        try {
            $query->chunk($batchSize, function (Collection $items) use ($callback, &$processed) {
                $callback($items);
                $processed += $items->count();
            });
        } catch (Throwable $e) {
            Log::error('Batch processing failed', [
                'processed' => $processed,
                'error'     => $e->getMessage(),
            ]);

            throw $e;
        }

        return $processed;
    }

    /**
     * Optimize eager loading to prevent N+1 queries.
     */
    public function eagerLoadOptimized(Builder $query, array $relations): Builder
    {
        $optimizedRelations = [];

        foreach ($relations as $relation => $callback) {
            if (is_numeric($relation)) {
                // Simple relation name
                $optimizedRelations[] = $callback;
            } else {
                // Relation with constraints - optimize the callback
                $optimizedRelations[$relation] = function ($q) use ($callback) {
                    if (is_callable($callback)) {
                        $callback($q);
                    }

                    // Add select optimization if not already specified
                    if (! $this->hasSelectClause($q)) {
                        $this->optimizeRelationSelect($q);
                    }
                };
            }
        }

        return $query->with($optimizedRelations);
    }

    /**
     * Create optimized aggregation queries.
     */
    public function aggregateOptimized(string $table, array $groupBy, array $aggregates): array
    {
        try {
            $query = DB::table($table);

            // Add group by clauses
            if (! empty($groupBy)) {
                $query->groupBy($groupBy);
            }

            // Add aggregate functions
            $selectClauses = $groupBy;
            foreach ($aggregates as $alias => $aggregate) {
                if (is_array($aggregate)) {
                    $function = $aggregate['function'] ?? 'count';
                    $column = $aggregate['column'] ?? '*';
                    $selectClauses[] = DB::raw("{$function}({$column}) as {$alias}");
                } else {
                    $selectClauses[] = DB::raw($aggregate);
                }
            }

            $query->select($selectClauses);

            return $query->get()->toArray();
        } catch (Throwable $e) {
            Log::error('Aggregate optimization failed', [
                'table'      => $table,
                'groupBy'    => $groupBy,
                'aggregates' => $aggregates,
                'error'      => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Optimize index usage for queries.
     */
    public function optimizeIndexUsage(Builder $query, array $conditions): Builder
    {
        // Sort conditions by selectivity (most selective first)
        $sortedConditions = $this->sortConditionsBySelectivity($conditions);

        foreach ($sortedConditions as $condition) {
            $column = $condition['column'];
            $operator = $condition['operator'] ?? '=';
            $value = $condition['value'];

            // Use appropriate query method based on operator
            switch ($operator) {
                case '=':
                    $query->where($column, $value);
                    break;
                case 'in':
                    $query->whereIn($column, (array) $value);
                    break;
                case 'between':
                    $query->whereBetween($column, (array) $value);
                    break;
                case 'like':
                    // Optimize LIKE queries
                    if (str_starts_with($value, '%')) {
                        Log::info('Non-optimized LIKE query detected', ['column' => $column, 'value' => $value]);
                    }
                    $query->where($column, 'like', $value);
                    break;
                default:
                    $query->where($column, $operator, $value);
            }
        }

        return $query;
    }

    /**
     * Get query performance statistics.
     */
    public function getQueryStats(): array
    {
        try {
            // Get slow query log if available
            $slowQueries = DB::select("SHOW VARIABLES LIKE 'slow_query_log'");
            $longQueryTime = DB::select("SHOW VARIABLES LIKE 'long_query_time'");

            return [
                'slow_query_log_enabled' => $slowQueries[0]->Value ?? 'unknown',
                'long_query_time'        => $longQueryTime[0]->Value ?? 'unknown',
                'connection_count'       => DB::select("SHOW STATUS LIKE 'Threads_connected'")[0]->Value ?? 0,
                'query_cache_hits'       => DB::select("SHOW STATUS LIKE 'Qcache_hits'")[0]->Value ?? 0,
            ];
        } catch (Throwable $e) {
            Log::warning('Failed to get query statistics', [
                'error' => $e->getMessage(),
            ]);

            return [
                'slow_query_log_enabled' => 'unknown',
                'long_query_time'        => 'unknown',
                'connection_count'       => 0,
                'query_cache_hits'       => 0,
            ];
        }
    }

    /**
     * Qualify field names with table prefix.
     */
    private function qualifyFields(array $fields, string $table): array
    {
        return array_map(function ($field) use ($table) {
            if (str_contains($field, '.')) {
                return $field; // Already qualified
            }

            return $table . '.' . $field;
        }, $fields);
    }

    /**
     * Check if query has select clause.
     */
    private function hasSelectClause($query): bool
    {
        return ! empty($query->getQuery()->columns);
    }

    /**
     * Optimize relation select clause.
     */
    private function optimizeRelationSelect($query): void
    {
        $model = $query->getModel();

        if ($model instanceof Model) {
            $table = $model->getTable();
            $keyName = $model->getKeyName();

            // Select only essential fields for relations
            $essentialFields = [
                $table . '.' . $keyName,
                $table . '.name', // Common field
            ];

            // Add foreign key if this is a pivot relation
            if (method_exists($model, 'getForeignKey')) {
                $essentialFields[] = $table . '.' . $model->getForeignKey();
            }

            $query->select(array_filter($essentialFields));
        }
    }

    /**
     * Sort conditions by selectivity (most selective first).
     */
    private function sortConditionsBySelectivity(array $conditions): array
    {
        // Simple heuristic: exact matches first, then ranges, then LIKE
        usort($conditions, function ($a, $b) {
            $aOperator = $a['operator'] ?? '=';
            $bOperator = $b['operator'] ?? '=';

            $selectivityOrder = [
                '='       => 1,
                'in'      => 2,
                'between' => 3,
                'like'    => 4,
            ];

            $aScore = $selectivityOrder[$aOperator] ?? 5;
            $bScore = $selectivityOrder[$bOperator] ?? 5;

            return $aScore <=> $bScore;
        });

        return $conditions;
    }
}
