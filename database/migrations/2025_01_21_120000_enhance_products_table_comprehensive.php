<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                // Pricing fields (EUR precision)
                $table->decimal('compare_price', 15, 2)->nullable()->after('price');
                $table->decimal('cost_price', 15, 2)->nullable()->after('compare_price');

                // Inventory fields
                $table->boolean('track_stock')->default(true)->after('manage_stock');
                $table->boolean('allow_backorder')->default(false)->after('track_stock');

                // Product fields
                $table->string('barcode')->nullable()->after('sku');
                $table->string('video_url')->nullable()->after('seo_description');
                $table->json('metadata')->nullable()->after('video_url');
                $table->integer('sort_order')->default(0)->after('metadata');

                // E-commerce specific fields
                $table->string('tax_class')->nullable()->after('sort_order');
                $table->string('shipping_class')->nullable()->after('tax_class');

                // Digital product fields
                $table->integer('download_limit')->nullable()->after('shipping_class');
                $table->integer('download_expiry')->nullable()->after('download_limit');
                $table->string('external_url')->nullable()->after('download_expiry');
                $table->string('button_text')->nullable()->after('external_url');
            });
        }

        if (Schema::hasTable('product_translations')) {
            // Update product_translations table to include all necessary fields
            Schema::table('product_translations', function (Blueprint $table) {
                $table->text('short_description')->nullable()->after('summary');
                $table->json('meta_keywords')->nullable()->after('seo_description');
                $table->string('alt_text')->nullable()->after('meta_keywords');
            });
        }

        if (Schema::hasTable('products')) {
            // Add indexes for better performance
            Schema::table('products', function (Blueprint $table) {
                $table->index(['barcode'], 'products_barcode_idx');
                $table->index(['compare_price'], 'products_compare_price_idx');
                $table->index(['cost_price'], 'products_cost_price_idx');
                $table->index(['sort_order'], 'products_sort_order_idx');
                $table->index(['tax_class'], 'products_tax_class_idx');
                $table->index(['shipping_class'], 'products_shipping_class_idx');
                $table->index(['status', 'type'], 'products_status_type_idx');
                $table->index(['is_visible', 'is_featured'], 'products_visible_featured_idx');
                if (Schema::hasColumn('products', 'manage_stock')) {
                    $table->index(['manage_stock', 'track_stock'], 'products_stock_tracking_idx');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products')) {
            // Drop indexes first
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'barcode')) {
                    $table->dropIndex('products_barcode_idx');
                }
                if (Schema::hasColumn('products', 'compare_price')) {
                    $table->dropIndex('products_compare_price_idx');
                }
                if (Schema::hasColumn('products', 'cost_price')) {
                    $table->dropIndex('products_cost_price_idx');
                }
                if (Schema::hasColumn('products', 'sort_order')) {
                    $table->dropIndex('products_sort_order_idx');
                }
                if (Schema::hasColumn('products', 'tax_class')) {
                    $table->dropIndex('products_tax_class_idx');
                }
                if (Schema::hasColumn('products', 'shipping_class')) {
                    $table->dropIndex('products_shipping_class_idx');
                }
                $table->dropIndex('products_status_type_idx');
                $table->dropIndex('products_visible_featured_idx');
                if (Schema::hasColumn('products', 'manage_stock')) {
                    $table->dropIndex('products_stock_tracking_idx');
                }
            });

            Schema::table('products', function (Blueprint $table) {
                $columns = [
                    'compare_price',
                    'cost_price',
                    'track_stock',
                    'allow_backorder',
                    'barcode',
                    'video_url',
                    'metadata',
                    'sort_order',
                    'tax_class',
                    'shipping_class',
                    'download_limit',
                    'download_expiry',
                    'external_url',
                    'button_text',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('products', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('product_translations')) {
            Schema::table('product_translations', function (Blueprint $table) {
                $columns = ['short_description', 'meta_keywords', 'alt_text'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('product_translations', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
