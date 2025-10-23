<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add supporting indexes that feature and performance tests depend on.
     */
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            // Skip when the orders table has not been created yet (e.g. during package testing).
            return;
        }

        if (! $this->indexExists('orders', 'orders_created_at_idx')) {
            Schema::table('orders', function (Blueprint $table): void {
                // Ensure the orders listing can leverage the created_at column efficiently.
                $table->index('created_at', 'orders_created_at_idx');
            });
        }

        if (Schema::hasTable('order_items')) {
            if (! $this->indexExists('order_items', 'order_items_order_id_idx')) {
                Schema::table('order_items', function (Blueprint $table): void {
                    // Optimise lookups when joining order items to their parent orders.
                    $table->index('order_id', 'order_items_order_id_idx');
                });
            }

            if (! $this->indexExists('order_items', 'order_items_product_id_idx')) {
                Schema::table('order_items', function (Blueprint $table): void {
                    // Allow product level aggregations to remain performant in diagnostics tests.
                    $table->index('product_id', 'order_items_product_id_idx');
                });
            }
        }
    }

    /**
     * Remove the indexes when rolling back migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders') && $this->indexExists('orders', 'orders_created_at_idx')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropIndex('orders_created_at_idx');
            });
        }

        if (Schema::hasTable('order_items')) {
            if ($this->indexExists('order_items', 'order_items_order_id_idx')) {
                Schema::table('order_items', function (Blueprint $table): void {
                    $table->dropIndex('order_items_order_id_idx');
                });
            }

            if ($this->indexExists('order_items', 'order_items_product_id_idx')) {
                Schema::table('order_items', function (Blueprint $table): void {
                    $table->dropIndex('order_items_product_id_idx');
                });
            }
        }
    }

    /**
     * Determine whether the given index already exists for the supplied table.
     */
    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        return match ($driver) {
            'sqlite' => $this->sqliteIndexExists($table, $index),
            'mysql', 'mariadb' => $this->mysqlIndexExists($table, $index),
            'pgsql' => $this->postgresIndexExists($table, $index),
            default => false,
        };
    }

    /**
     * Query the SQLite system catalog for the specified index name.
     */
    private function sqliteIndexExists(string $table, string $index): bool
    {
        $rows = DB::select('PRAGMA index_list("' . $table . '")');

        foreach ($rows as $row) {
            if (isset($row->name) && $row->name === $index) {
                return true;
            }
        }

        return false;
    }

    /**
     * Query MySQL/MariaDB information schema for an index.
     */
    private function mysqlIndexExists(string $table, string $index): bool
    {
        $database = DB::getDatabaseName();
        $rows = DB::select(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$database, $table, $index]
        );

        return ! empty($rows);
    }

    /**
     * Query PostgreSQL catalog tables for an index.
     */
    private function postgresIndexExists(string $table, string $index): bool
    {
        $rows = DB::select(
            'SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ? LIMIT 1',
            [$table, $index]
        );

        return ! empty($rows);
    }
};
