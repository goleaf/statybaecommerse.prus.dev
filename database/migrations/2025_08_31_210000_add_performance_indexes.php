<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('sh_products')) {
            Schema::table('sh_products', function (Blueprint $table) {
                try {
                    $table->index(['published_at'], 'sh_products_published_at_index');
                } catch (\Throwable $e) {
                }
                try {
                    $table->index(['is_visible', 'published_at'], 'sh_products_visible_published_index');
                } catch (\Throwable $e) {
                }
                try {
                    $table->index(['brand_id'], 'sh_products_brand_id_index');
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('sh_category_product')) {
            Schema::table('sh_category_product', function (Blueprint $table) {
                try {
                    $table->index(['category_id', 'product_id'], 'sh_cat_prod_cat_prod_index');
                } catch (\Throwable $e) {
                }
                try {
                    $table->index(['product_id'], 'sh_cat_prod_product_id_index');
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('sh_collection_product')) {
            Schema::table('sh_collection_product', function (Blueprint $table) {
                try {
                    $table->index(['collection_id', 'product_id'], 'sh_coll_prod_coll_prod_index');
                } catch (\Throwable $e) {
                }
                try {
                    $table->index(['product_id'], 'sh_coll_prod_product_id_index');
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('sh_product_translations')) {
            Schema::table('sh_product_translations', function (Blueprint $table) {
                try {
                    $table->index(['product_id'], 'sh_prod_trans_product_id_index');
                } catch (\Throwable $e) {
                }
                try {
                    $table->index(['locale'], 'sh_prod_trans_locale_index');
                } catch (\Throwable $e) {
                }
            });
        }
    }

    public function down(): void
    {
        // Non-destructive
    }
};
