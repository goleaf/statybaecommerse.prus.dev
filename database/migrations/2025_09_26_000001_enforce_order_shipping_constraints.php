<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_shippings')) {
            return;
        }

        Schema::table('order_shippings', function (Blueprint $table): void {
            if (! Schema::hasColumn('order_shippings', 'order_id')) {
                $table->foreignId('order_id')
                    ->constrained()
                    ->cascadeOnDelete()
                    ->after('id');
            }
        });

        $this->ensureForeignKey(
            table: 'order_shippings',
            column: 'order_id',
            referencedTable: 'orders',
            referencedColumn: 'id',
            onDelete: 'cascade',
        );

        $this->ensureIndex('order_shippings', ['status'], 'order_shippings_status_idx');
        $this->ensureIndex('order_shippings', ['created_at'], 'order_shippings_created_at_idx');
        $this->ensureIndex('order_shippings', ['tracking_number'], 'order_shippings_tracking_number_idx');
    }

    public function down(): void
    {
        if (! Schema::hasTable('order_shippings')) {
            return;
        }

        $this->dropIndexIfExists('order_shippings', 'order_shippings_tracking_number_idx');
        $this->dropIndexIfExists('order_shippings', 'order_shippings_created_at_idx');
        $this->dropIndexIfExists('order_shippings', 'order_shippings_status_idx');

        if ($this->isSqliteConnection()) {
            return;
        }

        Schema::table('order_shippings', function (Blueprint $table): void {
            if ($this->foreignKeyExists('order_shippings', 'order_id', 'orders', 'id')) {
                $table->dropForeign('order_shippings_order_id_foreign');
            }
        });
    }

    private function ensureForeignKey(
        string $table,
        string $column,
        string $referencedTable,
        string $referencedColumn,
        string $onDelete,
    ): void {
        if ($this->foreignKeyExists($table, $column, $referencedTable, $referencedColumn)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($column, $referencedTable, $referencedColumn, $onDelete): void {
            $foreign = $table->foreign($column)->references($referencedColumn)->on($referencedTable);

            match (strtolower($onDelete)) {
                'cascade' => $foreign->cascadeOnDelete(),
                'restrict' => $foreign->restrictOnDelete(),
                'set null' => $foreign->nullOnDelete(),
                default => null,
            };
        });
    }

    private function ensureIndex(string $table, array $columns, string $indexName): void
    {
        if ($this->indexExists($table, $indexName, $columns)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns, $indexName): void {
            $table->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($indexName): void {
            $table->dropIndex($indexName);
        });
    }

    private function foreignKeyExists(
        string $table,
        string $column,
        string $referencedTable,
        string $referencedColumn,
    ): bool {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $foreignKeys = DB::select("PRAGMA foreign_key_list('{$table}')");

            foreach ($foreignKeys as $foreignKey) {
                $from = $foreignKey->from ?? $foreignKey['from'] ?? null;
                $toTable = $foreignKey->table ?? $foreignKey['table'] ?? null;
                $toColumn = $foreignKey->to ?? $foreignKey['to'] ?? null;

                if ($from === $column && $toTable === $referencedTable && $toColumn === $referencedColumn) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'mysql') {
            $result = $connection->select(
                'SELECT COUNT(*) AS aggregate FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME = ? AND REFERENCED_COLUMN_NAME = ?',
                [$table, $column, $referencedTable, $referencedColumn],
            );

            return (int) ($result[0]->aggregate ?? 0) > 0;
        }

        return false;
    }

    private function indexExists(string $table, string $indexName, array $columns = []): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");

            foreach ($indexes as $index) {
                $name = $index->name ?? $index['name'] ?? null;

                if ($name !== $indexName) {
                    continue;
                }

                if ($columns === []) {
                    return true;
                }

                $details = DB::select("PRAGMA index_info('{$name}')");
                $indexedColumns = array_map(
                    static fn ($detail) => $detail->name ?? $detail['name'] ?? null,
                    $details,
                );

                return $indexedColumns === $columns;
            }

            return false;
        }

        if ($driver === 'mysql') {
            $result = $connection->select(
                'SELECT COUNT(*) AS aggregate FROM information_schema.statistics WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
                [$table, $indexName],
            );

            if ((int) ($result[0]->aggregate ?? 0) === 0) {
                return false;
            }

            if ($columns === []) {
                return true;
            }

            $columnResult = $connection->select(
                'SELECT COLUMN_NAME FROM information_schema.statistics WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? ORDER BY SEQ_IN_INDEX',
                [$table, $indexName],
            );

            $indexedColumns = array_map(
                static fn ($column) => $column->COLUMN_NAME ?? $column['COLUMN_NAME'] ?? null,
                $columnResult,
            );

            return $indexedColumns === $columns;
        }

        return false;
    }

    private function isSqliteConnection(): bool
    {
        return Schema::getConnection()->getDriverName() === 'sqlite';
    }
};
