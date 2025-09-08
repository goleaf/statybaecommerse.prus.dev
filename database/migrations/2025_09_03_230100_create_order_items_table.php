<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
                $table->string('name');
                $table->string('sku');
                $table->integer('quantity');
                $table->decimal('unit_price', 10, 2);
                $table->decimal('total', 10, 2);
                $table->timestamps();

                $table->index(['order_id']);
                $table->index(['product_id']);
                $table->index(['product_variant_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};


