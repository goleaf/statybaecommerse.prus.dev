<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
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
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('location_id')->references('id')->on('locations')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
