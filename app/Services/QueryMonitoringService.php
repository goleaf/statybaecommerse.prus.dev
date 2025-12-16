<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

/**
 * Service for monitoring database queries to detect N+1 patterns and performance issues.
 */
final class QueryMonitoringService
{
    private array $queries = [];

    private bool $monitoring = false;

    private int $queryThreshold = 20; // Alert if more than 20 queries in a single request

    /**
     * Start monitoring database queries.
     */
    public function startMonitoring(int $threshold = 20): void
    {
        if ($this->monitoring) {
            return;
        }

        $this->monitoring = true;
        $this->queryThreshold = $threshold;
        $this->queries = [];

        Event::listen(QueryExecuted::class, function (QueryExecuted $query): void {
            if (! $this->monitoring) {
                return;
            }

            $this->queries[] = [
                'sql'        => $query->sql,
                'bindings'   => $query->bindings,
                'time'       => $query->time,
                'connection' => $query->connectionName,
                'backtrace'  => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10),
            ];
        });
    }

    /**
     * Stop monitoring and return collected query data.
     */
    public function stopMonitoring(): array
    {
        $this->monitoring = false;

        $queryData = [
            'total_queries'      => count($this->queries),
            'total_time'         => array_sum(array_column($this->queries, 'time')),
            'queries'            => $this->queries,
            'n1_patterns'        => $this->detectN1Patterns(),
            'threshold_exceeded' => count($this->queries) > $this->queryThreshold,
        ];

        // Log warning if threshold exceeded
        if ($queryData['threshold_exceeded']) {
            Log::warning('Query threshold exceeded', [
                'total_queries' => $queryData['total_queries'],
                'threshold'     => $this->queryThreshold,
                'n1_patterns'   => count($queryData['n1_patterns']),
            ]);
        }

        return $queryData;
    }

    /**
     * Detect potential N+1 query patterns.
     */
    private function detectN1Patterns(): array
    {
        $patterns = [];
        $queryGroups = [];

        // Group similar queries
        foreach ($this->queries as $query) {
            $normalizedSql = $this->normalizeSql($query['sql']);

            if (! isset($queryGroups[$normalizedSql])) {
                $queryGroups[$normalizedSql] = [];
            }

            $queryGroups[$normalizedSql][] = $query;
        }

        // Look for patterns that might indicate N+1
        foreach ($queryGroups as $sql => $queries) {
            if (count($queries) > 3) { // More than 3 similar queries might indicate N+1
                $isLikelyN1 = $this->isLikelyN1Pattern($sql, $queries);
                $patterns[] = [
                    'sql'        => $sql,
                    'count'      => count($queries),
                    'total_time' => array_sum(array_column($queries, 'time')),
                    'likely_n1'  => $isLikelyN1,
                ];
            }
        }

        return $patterns;
    }

    /**
     * Normalize SQL to group similar queries.
     */
    private function normalizeSql(string $sql): string
    {
        // Replace parameter placeholders and values with generic markers
        $normalized = preg_replace('/\?/', '?', $sql);
        $normalized = preg_replace('/\b\d+\b/', '?', $normalized);
        $normalized = preg_replace('/\'[^\']*\'/', '?', $normalized);
        $normalized = preg_replace('/`[^`]*`/', '?', $normalized);

        return trim($normalized);
    }

    /**
     * Determine if a query pattern is likely an N+1 issue.
     */
    private function isLikelyN1Pattern(string $sql, array $queries): bool
    {
        $sqlLower = strtolower($sql);

        // Exclude schema introspection queries (SQLite specific)
        $schemaQueries = [
            'pragma_table_xinfo',
            'sqlite_master',
            'pragma_table_info',
            'pragma_foreign_key_list',
            'information_schema',
        ];

        foreach ($schemaQueries as $schemaQuery) {
            if (str_contains($sqlLower, $schemaQuery)) {
                return false;
            }
        }

        // Look for common N+1 indicators
        $n1Indicators = [
            'select .* from .* where .*\.id = .*',
            'select .* from .* where .*_id = .*',
            'limit 1',
        ];

        foreach ($n1Indicators as $indicator) {
            if (preg_match('/' . $indicator . '/i', $sqlLower)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get current query count without stopping monitoring.
     */
    public function getCurrentQueryCount(): int
    {
        return count($this->queries);
    }

    /**
     * Check if monitoring is active.
     */
    public function isMonitoring(): bool
    {
        return $this->monitoring;
    }

    /**
     * Reset query collection without stopping monitoring.
     */
    public function resetQueries(): void
    {
        $this->queries = [];
    }
}
