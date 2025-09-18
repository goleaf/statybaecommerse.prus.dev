<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cart_items')) {
            return;
        }

        Schema::table('cart_items', function (Blueprint $table) {
            if (!Schema::hasColumn('cart_items', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('total_price');
            }
            if (!Schema::hasColumn('cart_items', 'product_variant_id')) {
                $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete()->after('product_id');
            }
            if (!Schema::hasColumn('cart_items', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (!Schema::hasColumn('cart_items', 'attributes')) {
                $table->json('attributes')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('cart_items')) {
            return;
        }

        Schema::table('cart_items', function (Blueprint $table) {
            if (Schema::hasColumn('cart_items', 'price')) {
                $table->dropColumn('price');
            }
            if (Schema::hasColumn('cart_items', 'product_variant_id')) {
                $table->dropForeign(['product_variant_id']);
                $table->dropColumn('product_variant_id');
            }
            if (Schema::hasColumn('cart_items', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('cart_items', 'attributes')) {
                $table->dropColumn('attributes');
            }
        });
    }
};
