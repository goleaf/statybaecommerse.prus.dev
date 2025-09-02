<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('sh_variant_inventories')) {
            return;
        }

        Schema::create('sh_variant_inventories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('variant_id');
            $table->unsignedBigInteger('inventory_id');
            $table->integer('stock');
            $table->integer('reserved')->default(0);
            $table->timestamps();

            $table->unique(['variant_id', 'inventory_id'], 'variant_inventory_unique');
            $table->index('inventory_id');

            $table
                ->foreign('variant_id')
                ->references('id')
                ->on('sh_product_variants')
                ->cascadeOnDelete();

            $table
                ->foreign('inventory_id')
                ->references('id')
                ->on('sh_inventories')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sh_variant_inventories');
    }
};
