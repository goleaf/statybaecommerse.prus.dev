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
        // Guard each table in case schema dumps or partial installs omit earlier tables.
        // Optimize product queries for storefront filtering and sorting
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                // Composite index for category filtering with visibility
                $table->index(['is_enabled', 'status', 'published_at'], 'products_storefront_visibility_idx');

                // Index for price range filtering
                $table->index(['price', 'is_enabled'], 'products_price_visibility_idx');

                // Index for stock filtering
                $table->index(['stock_quantity', 'is_enabled'], 'products_stock_visibility_idx');
            });
        }

        // Optimize product translations for search
        if (Schema::hasTable('product_translations')) {
            Schema::table('product_translations', function (Blueprint $table) {
                // Composite index for locale-based searches
                $table->index(['locale', 'product_id'], 'product_translations_locale_product_idx');

                // Index for full-text search on name and description
                if (config('database.default') === 'mysql') {
                    $table->fullText(['name', 'description'], 'product_translations_search_idx');
                }
            });
        }

        // Optimize brand queries
        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table) {
                // Index for active brands with product counts
                $table->index(['is_active', 'sort_order'], 'brands_active_sort_idx');
            });
        }

        // Optimize collection queries
        if (Schema::hasTable('collections')) {
            Schema::table('collections', function (Blueprint $table) {
                // Index for visible collections with sorting
                $table->index(['is_visible', 'is_enabled', 'sort_order'], 'collections_visibility_sort_idx');
            });
        }

        // Optimize category queries
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                // Index for category hierarchy navigation
                $table->index(['parent_id', 'is_visible', 'sort_order'], 'categories_hierarchy_idx');
            });
        }

        // Optimize pivot table queries
        if (Schema::hasTable('product_categories')) {
            Schema::table('product_categories', function (Blueprint $table) {
                // Index for category-based product filtering
                $table->index(['category_id', 'product_id'], 'product_categories_category_product_idx');
            });
        }

        if (Schema::hasTable('product_collections')) {
            Schema::table('product_collections', function (Blueprint $table) {
                // Index for collection-based product filtering
                $table->index(['collection_id', 'product_id'], 'product_collections_collection_product_idx');
            });
        }

        // Optimize review queries
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                // Index for product reviews with approval status
                $table->index(['product_id', 'is_approved', 'created_at'], 'reviews_product_approved_idx');
            });
        }

        // Optimize order queries for reporting
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                // Index for order reporting and filtering
                $table->index(['created_at', 'status'], 'orders_analytics_idx');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Mirror the guards to avoid drop errors when tables were never created.
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('products_storefront_visibility_idx');
                $table->dropIndex('products_price_visibility_idx');
                $table->dropIndex('products_stock_visibility_idx');
            });
        }

        if (Schema::hasTable('product_translations')) {
            Schema::table('product_translations', function (Blueprint $table) {
                $table->dropIndex('product_translations_locale_product_idx');

                if (config('database.default') === 'mysql') {
                    $table->dropFullText('product_translations_search_idx');
                }
            });
        }

        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->dropIndex('brands_active_sort_idx');
            });
        }

        if (Schema::hasTable('collections')) {
            Schema::table('collections', function (Blueprint $table) {
                $table->dropIndex('collections_visibility_sort_idx');
            });
        }

        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex('categories_hierarchy_idx');
            });
        }

        if (Schema::hasTable('product_categories')) {
            Schema::table('product_categories', function (Blueprint $table) {
                $table->dropIndex('product_categories_category_product_idx');
            });
        }

        if (Schema::hasTable('product_collections')) {
            Schema::table('product_collections', function (Blueprint $table) {
                $table->dropIndex('product_collections_collection_product_idx');
            });
        }

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropIndex('reviews_product_approved_idx');
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('orders_analytics_idx');
            });
        }

    }
};
