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

                if (! Schema::hasColumn('products', 'allow_backorder')) {
                    $table->boolean('allow_backorder')->default(false)->after('manage_stock');
                }

                // Add missing product fields
                if (! Schema::hasColumn('products', 'barcode')) {
                    $table->string('barcode')->nullable()->after('sku');
                }
                if (! Schema::hasColumn('products', 'metadata')) {
                    $table->json('metadata')->nullable()->after('seo_description');
                }

                // Add e-commerce specific fields
                if (! Schema::hasColumn('products', 'shipping_class')) {
                    $table->string('shipping_class')->nullable()->after('metadata');
                }

                // Add digital product fields
                if (! Schema::hasColumn('products', 'external_url')) {
                    $table->string('external_url')->nullable()->after('shipping_class');
                }
            });
        }

        // Update product_translations table to include all necessary fields
        if (Schema::hasTable('product_translations')) {
            Schema::table('product_translations', function (Blueprint $table) {
                // Add missing translation fields
                if (! Schema::hasColumn('product_translations', 'short_description')) {
                    $table->text('short_description')->nullable()->after('description');
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
                    'products_barcode_idx'          => ['barcode'],
                    'products_compare_price_idx'    => ['compare_price'],
                    'products_cost_price_idx'       => ['cost_price'],
                    'products_shipping_class_idx'   => ['shipping_class'],
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
                    'allow_backorder',
                    'barcode',
                    'metadata',
                    'shipping_class',
                    'external_url',
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
        $connection = Schema::getConnection();

        // Avoid hard dependency on doctrine/dbal in environments where it's not installed.
        if (! method_exists($connection, 'getDoctrineSchemaManager')) {
            return false;
        }

        try {
            $indexes = $connection->getDoctrineSchemaManager()->listTableIndexes($table);

            return array_key_exists($indexName, $indexes);
        } catch (\Throwable) {
            // If the doctrine layer is unavailable or errors, assume the index does not exist
            // and let the schema builder attempt to create it.
            return false;
        }
    }
};
