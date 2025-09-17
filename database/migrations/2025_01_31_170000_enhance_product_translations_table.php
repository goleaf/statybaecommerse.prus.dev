<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('product_translations')) {
            Schema::table('product_translations', function (Blueprint $table) {
                if (!Schema::hasColumn('product_translations', 'short_description')) {
                    $table->text('short_description')->nullable()->after('summary');
                }
                if (!Schema::hasColumn('product_translations', 'meta_keywords')) {
                    $table->json('meta_keywords')->nullable()->after('seo_description');
                }
                if (!Schema::hasColumn('product_translations', 'alt_text')) {
                    $table->string('alt_text')->nullable()->after('meta_keywords');
                }
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $stringColumns = ['barcode', 'video_url', 'tax_class', 'shipping_class', 'external_url', 'button_text'];
                foreach ($stringColumns as $col) {
                    if (!Schema::hasColumn('products', $col)) {
                        $table->string($col)->nullable();
                    }
                }

                $decimalColumns = ['compare_price', 'cost_price'];
                foreach ($decimalColumns as $col) {
                    if (!Schema::hasColumn('products', $col)) {
                        $table->decimal($col, 15, 2)->nullable();
                    }
                }

                $boolColumns = ['track_stock', 'allow_backorder', 'is_requestable', 'hide_add_to_cart'];
                foreach ($boolColumns as $col) {
                    if (!Schema::hasColumn('products', $col)) {
                        $table->boolean($col)->default(in_array($col, ['track_stock'], true));
                    }
                }

                $intColumns = ['sort_order', 'download_limit', 'download_expiry', 'requests_count', 'minimum_quantity'];
                foreach ($intColumns as $col) {
                    if (!Schema::hasColumn('products', $col)) {
                        $table->integer($col)->default(match ($col) {
                            'sort_order' => 0,
                            'requests_count' => 0,
                            'minimum_quantity' => 1,
                            default => 0,
                        });
                    }
                }

                $jsonColumns = ['metadata', 'request_message'];
                foreach ($jsonColumns as $col) {
                    if (!Schema::hasColumn('products', $col)) {
                        $table->json($col)->nullable();
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_translations')) {
            Schema::table('product_translations', function (Blueprint $table) {
                foreach (['short_description', 'meta_keywords', 'alt_text'] as $column) {
                    if (Schema::hasColumn('product_translations', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $columns = [
                    'barcode',
                    'compare_price',
                    'cost_price',
                    'track_stock',
                    'allow_backorder',
                    'video_url',
                    'metadata',
                    'sort_order',
                    'tax_class',
                    'shipping_class',
                    'download_limit',
                    'download_expiry',
                    'external_url',
                    'button_text',
                    'is_requestable',
                    'requests_count',
                    'minimum_quantity',
                    'hide_add_to_cart',
                    'request_message',
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('products', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
