<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stock_reservations')) {
            // Bail out early when the stock reservations table does not exist yet.
            return;
        }

        // Safely attach the missing foreign keys once the referenced tables are present.
        Schema::table('stock_reservations', function (Blueprint $table): void {
            if (Schema::hasTable('products')) {
                $table->foreign('product_id', 'stock_reservations_product_id_foreign')
                    ->references('id')
                    ->on('products')
                    ->cascadeOnDelete();
            }

            if (Schema::hasTable('variant_inventories')) {
                $table->foreign('variant_inventory_id', 'stock_reservations_variant_inventory_id_foreign')
                    ->references('id')
                    ->on('variant_inventories')
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('stock_reservations')) {
            // Nothing to roll back when the table itself is absent.
            return;
        }

        Schema::table('stock_reservations', function (Blueprint $table): void {
            // Drop the foreign keys defensively to keep rollbacks idempotent.
            if (Schema::hasTable('products')) {
                $table->dropForeign('stock_reservations_product_id_foreign');
            }

            if (Schema::hasTable('variant_inventories')) {
                $table->dropForeign('stock_reservations_variant_inventory_id_foreign');
            }
        });
    }
};
