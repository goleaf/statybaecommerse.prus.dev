<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventories')) {
            Schema::create('inventories', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('location_id');
                $table->integer('quantity')->default(0);
                $table->integer('reserved')->default(0);
                $table->integer('incoming')->default(0);
                $table->integer('threshold')->default(0);
                $table->boolean('is_tracked')->default(true);
                $table->timestamps();

                $table->unique(['product_id', 'location_id'], 'inventory_unique_per_location');
                $table->index(['product_id']);
                $table->index(['location_id']);
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('variant_inventories')) {
            Schema::create('variant_inventories', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('variant_id');
                $table->unsignedBigInteger('location_id');
                $table->integer('stock')->default(0);
                $table->integer('reserved')->default(0);
                $table->integer('incoming')->default(0);
                $table->integer('threshold')->default(0);
                $table->boolean('is_tracked')->default(true);
                $table->timestamps();

                $table->unique(['variant_id', 'location_id'], 'variant_inventory_unique_per_location');
                $table->index(['location_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('variant_inventories');
        Schema::dropIfExists('inventories');
    }
};
