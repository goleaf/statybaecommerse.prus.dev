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
        if (! Schema::hasTable('order_shippings')
            || ! Schema::hasTable('orders')
            || ! Schema::hasColumn('order_shippings', 'order_id')) {
            return;
        }

        if (! $this->foreignKeyExists('order_shippings', 'order_shippings_order_id_foreign')) {
            Schema::table('order_shippings', function (Blueprint $table): void {
                $table->foreign('order_id')
                    ->references('id')
                    ->on('orders')
                    ->cascadeOnDelete();
            });
        }

        if (! $this->indexExists('order_shippings', 'order_shippings_order_id_index')) {
            Schema::table('order_shippings', function (Blueprint $table): void {
                $table->index('order_id', 'order_shippings_order_id_index');
            });
        }
    }

    public function down(): void
    {
        throw new \RuntimeException('This migration is forward-only and cannot be rolled back.');
    }

    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $foreignKeys = DB::select("PRAGMA foreign_key_list('{$table}')");

            foreach ($foreignKeys as $foreignKey) {
                if (($foreignKey->table ?? null) === 'orders' && ($foreignKey->from ?? null) === 'order_id') {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'mysql') {
            $databaseName = $connection->getDatabaseName();

            $existing = DB::selectOne(
                'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? LIMIT 1',
                [$databaseName, $table, $constraintName],
            );

            if ($existing !== null) {
                return true;
            }

            $fallback = DB::selectOne(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = 'order_id' AND REFERENCED_TABLE_NAME = 'orders' LIMIT 1",
                [$databaseName, $table],
            );

            return $fallback !== null;
        }

        return false;
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");

            foreach ($indexes as $index) {
                if (($index->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'mysql') {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

            return $indexes !== [];
        }

        return false;
    }
};
