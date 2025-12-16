<?php

declare(strict_types=1);

namespace App\Services\Search;

use Illuminate\Database\ConnectionInterface;
use Throwable;

final class DatabaseSearchOptimizer
{
    public function __construct(
        private readonly ConnectionInterface $connection
    ) {}

    /**
     * Apply database engine-specific optimizations for search queries.
     */
    public function optimizeForProduction(): void
    {
        $driver = $this->connection->getDriverName();

        match ($driver) {
            'mysql'  => $this->optimizeForMySQL(),
            'pgsql'  => $this->optimizeForPostgreSQL(),
            'sqlite' => $this->optimizeForSQLite(),
            default  => null, // No specific optimizations for other drivers
        };
    }

    /**
     * Get database engine-specific search hints for query optimization.
     */
    public function getSearchHints(): array
    {
        $driver = $this->connection->getDriverName();

        return match ($driver) {
            'mysql'  => $this->getMySQLSearchHints(),
            'pgsql'  => $this->getPostgreSQLSearchHints(),
            'sqlite' => $this->getSQLiteSearchHints(),
            default  => [],
        };
    }

    /**
     * Check if full-text search is available and configured.
     */
    public function isFullTextSearchAvailable(): bool
    {
        $driver = $this->connection->getDriverName();

        return match ($driver) {
            'mysql'  => $this->isMySQLFullTextAvailable(),
            'pgsql'  => $this->isPostgreSQLFullTextAvailable(),
            'sqlite' => $this->isSQLiteFullTextAvailable(),
            default  => false,
        };
    }

    private function optimizeForMySQL(): void
    {
        // Enable query cache if available (MySQL 5.7 and earlier)
        try {
            $this->connection->statement('SET SESSION query_cache_type = ON');
        } catch (Throwable) {
            // Query cache not available in MySQL 8.0+, ignore
        }

        // Optimize for InnoDB
        $this->connection->statement('SET SESSION innodb_optimize_fulltext_only = ON');

        // Use appropriate isolation level for read-heavy workloads
        $this->connection->statement('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
    }

    private function optimizeForPostgreSQL(): void
    {
        // Enable parallel query execution for search operations
        $this->connection->statement('SET max_parallel_workers_per_gather = 2');

        // Optimize for read workloads
        $this->connection->statement('SET random_page_cost = 1.1');

        // Enable JIT compilation for complex queries if available
        try {
            $this->connection->statement('SET jit = on');
        } catch (Throwable) {
            // JIT not available, ignore
        }
    }

    private function optimizeForSQLite(): void
    {
        // Enable query planner optimizations
        $this->connection->statement('PRAGMA optimize');

        // Use WAL mode for better concurrent read performance (skip in transactions)
        try {
            $this->connection->statement('PRAGMA journal_mode = WAL');
        } catch (Throwable) {
            // WAL mode cannot be changed within transactions, skip in tests
        }

        // Increase cache size for better performance
        $this->connection->statement('PRAGMA cache_size = -64000'); // 64MB cache
    }

    private function getMySQLSearchHints(): array
    {
        return [
            'use_index'     => 'USE INDEX FOR ORDER BY (created_at, updated_at)',
            'force_index'   => 'FORCE INDEX (idx_products_search)',
            'straight_join' => 'STRAIGHT_JOIN',
        ];
    }

    private function getPostgreSQLSearchHints(): array
    {
        return [
            'enable_seqscan'    => 'SET enable_seqscan = off',
            'enable_indexscan'  => 'SET enable_indexscan = on',
            'enable_bitmapscan' => 'SET enable_bitmapscan = on',
        ];
    }

    private function getSQLiteSearchHints(): array
    {
        return [
            'analyze'    => 'ANALYZE',
            'indexed_by' => 'INDEXED BY idx_products_search',
        ];
    }

    private function isMySQLFullTextAvailable(): bool
    {
        try {
            $result = $this->connection->select("SHOW VARIABLES LIKE 'ft_min_word_len'");

            return ! empty($result);
        } catch (Throwable) {
            return false;
        }
    }

    private function isPostgreSQLFullTextAvailable(): bool
    {
        try {
            $result = $this->connection->select("SELECT to_tsvector('english', 'test')");

            return ! empty($result);
        } catch (Throwable) {
            return false;
        }
    }

    private function isSQLiteFullTextAvailable(): bool
    {
        try {
            $result = $this->connection->select("SELECT fts5('test')");

            return ! empty($result);
        } catch (Throwable) {
            return false;
        }
    }
}
