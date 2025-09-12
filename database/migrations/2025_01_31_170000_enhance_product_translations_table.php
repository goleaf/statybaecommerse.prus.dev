<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_translations')) {
            Schema::table('product_translations', function (Blueprint $table) {
                // Add missing translation fields if they don't exist
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

        // Ensure the products table has all necessary fields
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                // Add missing fields if they don't exist
                $fields = [
                    'barcode' => 'string',
                    'compare_price' => 'decimal:2',
                    'cost_price' => 'decimal:2',
                    'track_stock' => 'boolean',
                    'allow_backorder' => 'boolean',
                    'video_url' => 'string',
                    'metadata' => 'json',
                    'sort_order' => 'integer',
                    'tax_class' => 'string',
                    'shipping_class' => 'string',
                    'download_limit' => 'integer',
                    'download_expiry' => 'integer',
                    'external_url' => 'string',
                    'button_text' => 'string',
                    'is_requestable' => 'boolean',
                    'requests_count' => 'integer',
                    'minimum_quantity' => 'integer',
                    'hide_add_to_cart' => 'boolean',
                    'request_message' => 'text',
                ];

                foreach ($fields as $field => $type) {
                    if (!Schema::hasColumn('products', $field)) {
                        match ($type) {
                            'string' => $table->string($field)->nullable(),
                            'text' => $table->text($field)->nullable(),
                            'boolean' => $table->boolean($field)->default(false),
                            'integer' => $table->integer($field)->default(0),
                            'decimal:2' => $table->decimal($field, 10, 2)->nullable(),
                            'json' => $table->json($field)->nullable(),
                        };
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_translations')) {
            Schema::table('product_translations', function (Blueprint $table) {
                $table->dropColumn(['short_description', 'meta_keywords', 'alt_text']);
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn([
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
                ]);
            });
        }
    }
};


