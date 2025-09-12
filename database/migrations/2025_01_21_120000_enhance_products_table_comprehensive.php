<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                // Add missing pricing fields
                if (! Schema::hasColumn('products', 'compare_price')) {
                    $table->decimal('compare_price', 10, 2)->nullable()->after('price');
                }
                if (! Schema::hasColumn('products', 'cost_price')) {
                    $table->decimal('cost_price', 10, 2)->nullable()->after('compare_price');
                }

                // Add missing inventory fields
                if (! Schema::hasColumn('products', 'track_stock')) {
                    $table->boolean('track_stock')->default(true)->after('manage_stock');
                }
                if (! Schema::hasColumn('products', 'allow_backorder')) {
                    $table->boolean('allow_backorder')->default(false)->after('track_stock');
                }

                // Add missing product fields
                if (! Schema::hasColumn('products', 'barcode')) {
                    $table->string('barcode')->nullable()->after('sku');
                }
                if (! Schema::hasColumn('products', 'video_url')) {
                    $table->string('video_url')->nullable()->after('seo_description');
                }
                if (! Schema::hasColumn('products', 'metadata')) {
                    $table->json('metadata')->nullable()->after('video_url');
                }
                if (! Schema::hasColumn('products', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('metadata');
                }

                // Add e-commerce specific fields
                if (! Schema::hasColumn('products', 'tax_class')) {
                    $table->string('tax_class')->nullable()->after('sort_order');
                }
                if (! Schema::hasColumn('products', 'shipping_class')) {
                    $table->string('shipping_class')->nullable()->after('tax_class');
                }

                // Add digital product fields
                if (! Schema::hasColumn('products', 'download_limit')) {
                    $table->integer('download_limit')->nullable()->after('shipping_class');
                }
                if (! Schema::hasColumn('products', 'download_expiry')) {
                    $table->integer('download_expiry')->nullable()->after('download_limit');
                }
                if (! Schema::hasColumn('products', 'external_url')) {
                    $table->string('external_url')->nullable()->after('download_expiry');
                }
                if (! Schema::hasColumn('products', 'button_text')) {
                    $table->string('button_text')->nullable()->after('external_url');
                }
            });
        }

        // Update product_translations table to include all necessary fields
        if (Schema::hasTable('product_translations')) {
            Schema::table('product_translations', function (Blueprint $table) {
                // Add missing translation fields
                if (! Schema::hasColumn('product_translations', 'short_description')) {
                    $table->text('short_description')->nullable()->after('summary');
                }
                if (! Schema::hasColumn('product_translations', 'meta_keywords')) {
                    $table->json('meta_keywords')->nullable()->after('seo_description');
                }
                if (! Schema::hasColumn('product_translations', 'alt_text')) {
                    $table->string('alt_text')->nullable()->after('meta_keywords');
                }
            });
        }

        // Add indexes for better performance
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $indexes = [
                    'products_barcode_idx' => ['barcode'],
                    'products_compare_price_idx' => ['compare_price'],
                    'products_cost_price_idx' => ['cost_price'],
                    'products_sort_order_idx' => ['sort_order'],
                    'products_tax_class_idx' => ['tax_class'],
                    'products_shipping_class_idx' => ['shipping_class'],
                    'products_status_type_idx' => ['status', 'type'],
                    'products_visible_featured_idx' => ['is_visible', 'is_featured'],
                    'products_stock_tracking_idx' => ['manage_stock', 'track_stock'],
                ];

                foreach ($indexes as $indexName => $columns) {
                    if (! $this->indexExists('products', $indexName)) {
                        $table->index($columns, $indexName);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products')) {
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
                $columns = [
                    'short_description',
                    'meta_keywords',
                    'alt_text',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('product_translations', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes($table);

        return array_key_exists($indexName, $indexes);
    }
};
