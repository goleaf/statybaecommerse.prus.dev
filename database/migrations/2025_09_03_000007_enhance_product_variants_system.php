<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enhance product_variants table with size-dependent pricing
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                // Add size-specific fields
                if (! Schema::hasColumn('product_variants', 'size')) {
                    $table->string('size')->nullable()->after('name');
                }
                if (! Schema::hasColumn('product_variants', 'size_unit')) {
                    $table->string('size_unit', 10)->default('cm')->after('size');
                }
                if (! Schema::hasColumn('product_variants', 'size_display')) {
                    $table->string('size_display')->nullable()->after('size_unit');
                }

                // Add pricing modifiers
                if (! Schema::hasColumn('product_variants', 'size_price_modifier')) {
                    $table->decimal('size_price_modifier', 8, 4)->default(0)->after('cost_price');
                }
                if (! Schema::hasColumn('product_variants', 'size_weight_modifier')) {
                    $table->decimal('size_weight_modifier', 8, 4)->default(0)->after('weight');
                }

                // Add variant-specific fields
                if (! Schema::hasColumn('product_variants', 'variant_type')) {
                    $table->enum('variant_type', ['size', 'color', 'material', 'style', 'custom'])->default('size')->after('size_display');
                }
                if (! Schema::hasColumn('product_variants', 'is_default_variant')) {
                    $table->boolean('is_default_variant')->default(false)->after('is_enabled');
                }
                if (! Schema::hasColumn('product_variants', 'variant_sku_suffix')) {
                    $table->string('variant_sku_suffix')->nullable()->after('sku');
                }

                // Add inventory tracking
                if (! Schema::hasColumn('product_variants', 'track_inventory')) {
                    $table->boolean('track_inventory')->default(true)->after('quantity');
                }
                if (! Schema::hasColumn('product_variants', 'allow_backorder')) {
                    $table->boolean('allow_backorder')->default(false)->after('track_inventory');
                }
                if (! Schema::hasColumn('product_variants', 'low_stock_threshold')) {
                    $table->integer('low_stock_threshold')->default(5)->after('allow_backorder');
                }

                // Add variant metadata
                if (! Schema::hasColumn('product_variants', 'variant_metadata')) {
                    $afterColumn = Schema::hasColumn('product_variants', 'status')
                        ? 'status'
                        : (Schema::hasColumn('product_variants', 'attributes') ? 'attributes' : 'is_enabled');

                    $table->json('variant_metadata')->nullable()->after($afterColumn);
                }

                // Add indexes
                $table->index(['product_id', 'variant_type']);
                $table->index(['product_id', 'size']);
                $table->index(['is_default_variant']);
                $table->index(['variant_sku_suffix']);
            });
        }

        // Create product_variant_attributes table if it doesn't exist
        if (! Schema::hasTable('product_variant_attributes')) {
            Schema::create('product_variant_attributes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('variant_id');
                $table->unsignedBigInteger('attribute_id');
                $table->unsignedBigInteger('attribute_value_id');
                $table->timestamps();

                $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
                $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
                $table->foreign('attribute_value_id')->references('id')->on('attribute_values')->onDelete('cascade');
                $table->unique(['variant_id', 'attribute_id'], 'variant_attribute_unique');
                $table->index(['variant_id', 'attribute_value_id']);
            });
        }

        // Create variant_pricing_rules table for dynamic pricing
        if (! Schema::hasTable('variant_pricing_rules')) {
            Schema::create('variant_pricing_rules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->string('rule_name');
                $table->string('rule_type')->default('size_based');  // size_based, quantity_based, customer_group_based
                $table->json('conditions');  // Rule conditions
                $table->json('pricing_modifiers');  // Price modifiers
                $table->boolean('is_active')->default(true);
                $table->integer('priority')->default(0);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->index(['product_id', 'rule_type', 'is_active']);
                $table->index(['priority']);
            });
        }

        // Create variant_inventories table if it doesn't exist
        if (! Schema::hasTable('variant_inventories')) {
            Schema::create('variant_inventories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('variant_id');
                $table->string('warehouse_code')->default('main');
                $table->integer('stock')->default(0);
                $table->integer('reserved')->default(0);
                $table->integer('available')->default(0);
                $table->integer('reorder_point')->default(0);
                $table->integer('reorder_quantity')->default(0);
                $table->timestamp('last_restocked_at')->nullable();
                $table->timestamps();

                $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
                $table->unique(['variant_id', 'warehouse_code']);
                $table->index(['warehouse_code', 'stock']);
                $table->index(['reorder_point']);
            });
        }

        // Create variant_images table for variant-specific images
        if (! Schema::hasTable('variant_images')) {
            Schema::create('variant_images', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('variant_id');
                $table->string('image_path');
                $table->string('alt_text')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_primary')->default(false);
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
                $table->index(['variant_id', 'sort_order']);
                $table->index(['is_primary']);
            });
        }

        // Create variant_combinations table for managing variant combinations
        if (! Schema::hasTable('variant_combinations')) {
            Schema::create('variant_combinations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->json('attribute_combinations');  // Store all possible combinations
                $table->boolean('is_available')->default(true);
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->index(['product_id', 'is_available']);
            });
        }
    }

    public function down(): void
    {
        // Drop variant_combinations table
        if (Schema::hasTable('variant_combinations')) {
            Schema::dropIfExists('variant_combinations');
        }

        // Drop variant_images table
        if (Schema::hasTable('variant_images')) {
            Schema::dropIfExists('variant_images');
        }

        // Drop variant_inventories table
        if (Schema::hasTable('variant_inventories')) {
            Schema::dropIfExists('variant_inventories');
        }

        // Drop variant_pricing_rules table
        if (Schema::hasTable('variant_pricing_rules')) {
            Schema::dropIfExists('variant_pricing_rules');
        }

        // Drop product_variant_attributes table
        if (Schema::hasTable('product_variant_attributes')) {
            Schema::dropIfExists('product_variant_attributes');
        }

        // Remove added columns from product_variants table
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $columns = [
                    'size', 'size_unit', 'size_display', 'size_price_modifier', 'size_weight_modifier',
                    'variant_type', 'is_default_variant', 'variant_sku_suffix', 'track_inventory',
                    'allow_backorder', 'low_stock_threshold', 'variant_metadata',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('product_variants', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
