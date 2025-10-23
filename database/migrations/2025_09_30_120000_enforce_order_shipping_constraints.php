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

        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            if (! $this->foreignKeyExists('order_shippings', 'order_shippings_order_id_foreign')) {
                $this->rebuildOrderShippingsTableForSqlite();
            }

            foreach ([
                'order_shippings_order_id_index' => 'order_id',
                'order_shippings_status_idx' => 'status',
                'order_shippings_created_at_idx' => 'created_at',
                'order_shippings_tracking_number_idx' => 'tracking_number',
            ] as $indexName => $column) {
                DB::statement(sprintf(
                    'CREATE INDEX IF NOT EXISTS "%s" ON "order_shippings" ("%s")',
                    $indexName,
                    $column,
                ));
            }

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

        foreach ([
            'order_shippings_order_id_index' => 'order_id',
            'order_shippings_status_idx' => 'status',
            'order_shippings_created_at_idx' => 'created_at',
            'order_shippings_tracking_number_idx' => 'tracking_number',
        ] as $indexName => $column) {
            if (! $this->indexExists('order_shippings', $indexName)) {
                Schema::table('order_shippings', function (Blueprint $table) use ($column, $indexName): void {
                    $table->index($column, $indexName);
                });
            }
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

    private function rebuildOrderShippingsTableForSqlite(): void
    {
        $columns = [
            'id',
            'order_id',
            'carrier_name',
            'tracking_number',
            'tracking_url',
            'created_at',
            'updated_at',
            'shipping_method',
            'carrier',
            'service',
            'service_type',
            'shipped_at',
            'estimated_delivery',
            'delivered_at',
            'weight',
            'dimensions',
            'base_cost',
            'insurance_cost',
            'total_cost',
            'metadata',
            'status',
            'is_delivered',
            'delivery_notes',
            'notes',
        ];

        DB::transaction(function () use ($columns): void {
            if (Schema::hasTable('order_shippings_temp')) {
                Schema::dropIfExists('order_shippings_temp');
            }

            foreach ([
                'order_shippings_order_id_index',
                'order_shippings_status_idx',
                'order_shippings_created_at_idx',
                'order_shippings_tracking_number_idx',
            ] as $indexName) {
                DB::statement(sprintf('DROP INDEX IF EXISTS "%s"', $indexName));
            }

            DB::statement('ALTER TABLE "order_shippings" RENAME TO "order_shippings_temp"');

            Schema::create('order_shippings', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')
                    ->constrained('orders')
                    ->cascadeOnDelete();
                $table->string('carrier_name')->nullable();
                $table->string('tracking_number')->nullable();
                $table->string('tracking_url')->nullable();
                $table->timestamps();

                $table->string('shipping_method')->nullable();
                $table->string('carrier')->nullable();
                $table->string('service')->nullable();
                $table->string('service_type')->nullable();
                $table->timestamp('shipped_at')->nullable();
                $table->timestamp('estimated_delivery')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->decimal('weight', 8, 3)->nullable();
                $table->json('dimensions')->nullable();
                $table->decimal('base_cost', 10, 2)->nullable();
                $table->decimal('insurance_cost', 10, 2)->nullable();
                $table->decimal('total_cost', 10, 2)->nullable();
                $table->json('metadata')->nullable();
                $table->string('status')->default('pending');
                $table->boolean('is_delivered')->default(false);
                $table->string('delivery_notes', 500)->nullable();
                $table->text('notes')->nullable();

                $table->index('order_id', 'order_shippings_order_id_index');
                $table->index('status', 'order_shippings_status_idx');
                $table->index('created_at', 'order_shippings_created_at_idx');
                $table->index('tracking_number', 'order_shippings_tracking_number_idx');
            });

            $quotedColumns = implode(', ', array_map(
                static fn (string $column): string => '"' . $column . '"',
                $columns
            ));

            DB::statement(sprintf(
                'INSERT INTO "order_shippings" (%1$s) SELECT %1$s FROM "order_shippings_temp"',
                $quotedColumns
            ));

            Schema::dropIfExists('order_shippings_temp');
        });
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
