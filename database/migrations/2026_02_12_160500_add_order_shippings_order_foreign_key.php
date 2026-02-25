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
        if (! Schema::hasTable('order_shippings') || ! Schema::hasTable('orders')) {
            return;
        }

        if (! Schema::hasColumn('order_shippings', 'order_id')) {
            return;
        }

        if ($this->hasOrderForeignKey()) {
            return;
        }

        if ($this->isSqlite()) {
            $this->rebuildSqliteOrderShippingsTable();

            return;
        }

        Schema::table('order_shippings', function (Blueprint $table): void {
            $table->foreign('order_id', 'order_shippings_order_id_foreign')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('order_shippings')) {
            return;
        }

        if ($this->isSqlite()) {
            return;
        }

        Schema::table('order_shippings', function (Blueprint $table): void {
            $table->dropForeign('order_shippings_order_id_foreign');
        });
    }

    private function rebuildSqliteOrderShippingsTable(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        try {
            if (Schema::hasTable('order_shippings_backup_without_fk')) {
                Schema::drop('order_shippings_backup_without_fk');
            }

            Schema::rename('order_shippings', 'order_shippings_backup_without_fk');

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
                $table->decimal('cost', 10, 2)->nullable();
                $table->decimal('base_cost', 10, 2)->nullable();
                $table->decimal('insurance_cost', 10, 2)->nullable();
                $table->decimal('total_cost', 10, 2)->nullable();
                $table->json('metadata')->nullable();
                $table->string('status')->default('pending');
                $table->boolean('is_delivered')->default(false);
                $table->string('delivery_notes', 500)->nullable();
                $table->text('notes')->nullable();
            });

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
                'cost',
                'base_cost',
                'insurance_cost',
                'total_cost',
                'metadata',
                'status',
                'is_delivered',
                'delivery_notes',
                'notes',
            ];

            $quotedColumns = implode(', ', array_map(static fn (string $column): string => "\"{$column}\"", $columns));
            DB::statement(
                "INSERT INTO \"order_shippings\" ({$quotedColumns}) " .
                "SELECT {$quotedColumns} FROM \"order_shippings_backup_without_fk\" " .
                'WHERE "order_id" IN (SELECT "id" FROM "orders")'
            );

            Schema::drop('order_shippings_backup_without_fk');

            Schema::table('order_shippings', function (Blueprint $table): void {
                $table->index('order_id', 'order_shippings_order_id_index');
                $table->index('status', 'order_shippings_status_idx');
                $table->index('created_at', 'order_shippings_created_at_idx');
                $table->index('tracking_number', 'order_shippings_tracking_number_idx');
            });
        } finally {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    private function hasOrderForeignKey(): bool
    {
        if ($this->isSqlite()) {
            $foreignKeys = DB::select("PRAGMA foreign_key_list('order_shippings')");

            foreach ($foreignKeys as $foreignKey) {
                $table = $foreignKey->table ?? null;
                $from = $foreignKey->from ?? null;
                $to = $foreignKey->to ?? null;

                if ($table === 'orders' && $from === 'order_id' && $to === 'id') {
                    return true;
                }
            }

            return false;
        }

        $connection = Schema::getConnection();

        if ($connection->getDriverName() === 'mysql') {
            $result = $connection->selectOne(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order_shippings' AND COLUMN_NAME = 'order_id' AND REFERENCED_TABLE_NAME = 'orders' AND REFERENCED_COLUMN_NAME = 'id' LIMIT 1"
            );

            return $result !== null;
        }

        return false;
    }

    private function isSqlite(): bool
    {
        return Schema::getConnection()->getDriverName() === 'sqlite';
    }
};
