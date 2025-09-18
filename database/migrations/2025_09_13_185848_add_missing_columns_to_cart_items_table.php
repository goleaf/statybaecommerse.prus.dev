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
            if (! Schema::hasColumn('cart_items', 'variant_id')) {
                $table->unsignedBigInteger('variant_id')->nullable()->after('product_id');
            }

            if (! Schema::hasColumn('cart_items', 'product_variant_id')) {
                $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete()->after('variant_id');
            }

            if (! Schema::hasColumn('cart_items', 'unit_price')) {
                $table->decimal('unit_price', 10, 2)->nullable()->after('quantity');
            }

            if (! Schema::hasColumn('cart_items', 'total_price')) {
                $table->decimal('total_price', 10, 2)->nullable()->after('unit_price');
            }

            if (! Schema::hasColumn('cart_items', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('total_price');
            }

            if (! Schema::hasColumn('cart_items', 'product_snapshot')) {
                $table->json('product_snapshot')->nullable()->after('price');
            }

            if (! Schema::hasColumn('cart_items', 'notes')) {
                $table->text('notes')->nullable()->after('product_snapshot');
            }

            if (! Schema::hasColumn('cart_items', 'attributes')) {
                $table->json('attributes')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('cart_items')) {
            return;
        }

        Schema::table('cart_items', function (Blueprint $table) {
            if (Schema::hasColumn('cart_items', 'attributes')) {
                $table->dropColumn('attributes');
            }

            if (Schema::hasColumn('cart_items', 'notes')) {
                $table->dropColumn('notes');
            }

            if (Schema::hasColumn('cart_items', 'product_snapshot')) {
                $table->dropColumn('product_snapshot');
            }

            if (Schema::hasColumn('cart_items', 'price')) {
                $table->dropColumn('price');
            }

            if (Schema::hasColumn('cart_items', 'total_price')) {
                $table->dropColumn('total_price');
            }

            if (Schema::hasColumn('cart_items', 'unit_price')) {
                $table->dropColumn('unit_price');
            }

            if (Schema::hasColumn('cart_items', 'product_variant_id')) {
                $table->dropForeign(['product_variant_id']);
                $table->dropColumn('product_variant_id');
            }

            if (Schema::hasColumn('cart_items', 'variant_id')) {
                try {
                    $table->dropForeign(['variant_id']);
                } catch (\Throwable $e) {
                    // Foreign key might not exist
                }

                $table->dropColumn('variant_id');
            }
        });
    }
};
