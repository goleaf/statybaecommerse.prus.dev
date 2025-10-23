<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addCreatedAtIndex('orders', 'orders_created_at_index');
        $this->addCreatedAtIndex('products', 'products_created_at_index');
        $this->addCreatedAtIndex('users', 'users_created_at_index');
    }

    public function down(): void
    {
        $this->dropCreatedAtIndex('orders', 'orders_created_at_index');
        $this->dropCreatedAtIndex('products', 'products_created_at_index');
        $this->dropCreatedAtIndex('users', 'users_created_at_index');
    }

    /**
     * Safely add an index on the created_at column when both the table and column exist.
     */
    private function addCreatedAtIndex(string $tableName, string $indexName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        if (! Schema::hasColumn($tableName, 'created_at')) {
            // Skip tables that opt out of timestamps to keep the migration idempotent.
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName, $indexName): void {
            if ($this->indexExists($tableName, $indexName)) {
                return;
            }

            // Ensure we only create the index once to avoid duplicate key errors in production deployments.
            $table->index('created_at', $indexName);
        });
    }

    /**
     * Drop the created_at index when present to keep rollbacks idempotent.
     */
    private function dropCreatedAtIndex(string $tableName, string $indexName): void
    {
        if (! Schema::hasTable($tableName) || ! $this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName): void {
            $table->dropIndex($indexName);
        });
    }

    /**
     * Determine whether the target index already exists without assuming Doctrine is installed.
     */
    private function indexExists(string $tableName, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $table = $connection->getTablePrefix() . $tableName;

        if ($connection->getDriverName() === 'sqlite') {
            // SQLite keeps schema state between Pest invocations, so we must query the pragma
            // metadata directly to detect an existing index when Doctrine DBAL is unavailable.
            $existing = $connection->select("PRAGMA index_list('{$table}')");

            foreach ($existing as $definition) {
                if (($definition->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        if (! method_exists($connection, 'getDoctrineSchemaManager')) {
            // Doctrine DBAL is optional, therefore we fall back to driver-specific introspection so
            // that repeated deployments remain idempotent even in minimal installations.
            return $this->indexExistsViaInformationSchema($connection, $tableName, $indexName);
        }

        $schemaManager = $connection->getDoctrineSchemaManager();
        $indexes = $schemaManager->listTableIndexes($table);

        foreach ($indexes as $name => $index) {
            // Doctrine may normalise index names to uppercase on some database drivers, therefore
            // we compare the names in a case-insensitive manner to avoid false negatives.
            if (strcasecmp($name, $indexName) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Use lightweight INFORMATION_SCHEMA queries when Doctrine DBAL support is unavailable.
     */
    private function indexExistsViaInformationSchema($connection, string $tableName, string $indexName): bool
    {
        $driver = $connection->getDriverName();
        $table = $connection->getTablePrefix() . $tableName;

        return match ($driver) {
            'mysql', 'mariadb' => (bool) $connection->selectOne(
                // MySQL exposes index metadata via information_schema.statistics.
                'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
                [$connection->getDatabaseName(), $table, $indexName],
            ),
            'pgsql' => (bool) $connection->selectOne(
                // PostgreSQL stores index metadata in pg_indexes; we scope to the current schema.
                'SELECT 1 FROM pg_indexes WHERE schemaname = current_schema() AND tablename = ? AND indexname = ? LIMIT 1',
                [$table, $indexName],
            ),
            'sqlite' => (bool) collect(
                // SQLite provides the pragma_index_list command for the same purpose; parameter binding
                // is not supported, so we safely interpolate the known table identifier.
                $connection->select(sprintf("PRAGMA index_list('%s')", str_replace("'", "''", $table))),
            )->firstWhere('name', $indexName),
            default => false,
        };
    }
};
