<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_reservations', function (Blueprint $table): void {
            $table->id();

            // Manually declare the foreign key columns so the table can be created
            // even if the referenced tables are introduced by later migrations.
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variant_inventory_id')->nullable();

            // Capture the reserved stock quantities and lifecycle timestamps.
            $table->unsignedInteger('quantity');
            $table->string('status')->default('reserved');
            $table->timestamp('reserved_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('consumed_at')->nullable();
            $table->json('meta')->nullable();
            $table->string('reference_type')->nullable();
            $table->string('reference_id')->nullable();
            $table->timestamps();

            // Index the frequently queried relationships and lifecycle state.
            $table->index(['product_id', 'status']);
            $table->index(['variant_inventory_id', 'status']);
            $table->index(['status', 'expires_at']);
        });

        // Add the product foreign key when the products table is already present.
        if (Schema::hasTable('products')) {
            Schema::table('stock_reservations', function (Blueprint $table): void {
                $table->foreign('product_id', 'stock_reservations_product_id_foreign')
                    ->references('id')
                    ->on('products')
                    ->cascadeOnDelete();
            });
        }

        // Attach the variant inventory constraint if the supporting table exists.
        if (Schema::hasTable('variant_inventories')) {
            Schema::table('stock_reservations', function (Blueprint $table): void {
                $table->foreign('variant_inventory_id', 'stock_reservations_variant_inventory_id_foreign')
                    ->references('id')
                    ->on('variant_inventories')
                    ->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};
