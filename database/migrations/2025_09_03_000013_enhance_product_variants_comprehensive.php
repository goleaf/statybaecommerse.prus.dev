<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enhance product_variants table with comprehensive features
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                // Add comprehensive variant attributes
                if (! Schema::hasColumn('product_variants', 'variant_name_lt')) {
                    $table->string('variant_name_lt')->nullable()->after('name');
                }
                if (! Schema::hasColumn('product_variants', 'variant_name_en')) {
                    $table->string('variant_name_en')->nullable()->after('variant_name_lt');
                }

                // Add multi-language descriptions
                if (! Schema::hasColumn('product_variants', 'description_lt')) {
                    $table->text('description_lt')->nullable()->after('variant_name_en');
                }
                if (! Schema::hasColumn('product_variants', 'description_en')) {
                    $table->text('description_en')->nullable()->after('description_lt');
                }

                // Add advanced pricing
                if (! Schema::hasColumn('product_variants', 'wholesale_price')) {
                    $table->decimal('wholesale_price', 10, 4)->nullable()->after('cost_price');
                }
                if (! Schema::hasColumn('product_variants', 'member_price')) {
                    $table->decimal('member_price', 10, 4)->nullable()->after('wholesale_price');
                }
                if (! Schema::hasColumn('product_variants', 'promotional_price')) {
                    $table->decimal('promotional_price', 10, 4)->nullable()->after('member_price');
                }

                // Add promotional settings
                if (! Schema::hasColumn('product_variants', 'is_on_sale')) {
                    $table->boolean('is_on_sale')->default(false)->after('is_enabled');
                }
                if (! Schema::hasColumn('product_variants', 'sale_start_date')) {
                    $table->timestamp('sale_start_date')->nullable()->after('is_on_sale');
                }
                if (! Schema::hasColumn('product_variants', 'sale_end_date')) {
                    $table->timestamp('sale_end_date')->nullable()->after('sale_start_date');
                }

                // Add advanced inventory tracking
                if (! Schema::hasColumn('product_variants', 'reserved_quantity')) {
                    $table->integer('reserved_quantity')->default(0)->after('stock_quantity');
                }
                if (! Schema::hasColumn('product_variants', 'available_quantity')) {
                    $table->integer('available_quantity')->default(0)->after('reserved_quantity');
                }
                if (! Schema::hasColumn('product_variants', 'sold_quantity')) {
                    $table->integer('sold_quantity')->default(0)->after('available_quantity');
                }

                // Add SEO fields
                if (! Schema::hasColumn('product_variants', 'seo_title_lt')) {
                    $table->string('seo_title_lt')->nullable()->after('variant_metadata');
                }
                if (! Schema::hasColumn('product_variants', 'seo_title_en')) {
                    $table->string('seo_title_en')->nullable()->after('seo_title_lt');
                }
                if (! Schema::hasColumn('product_variants', 'seo_description_lt')) {
                    $table->text('seo_description_lt')->nullable()->after('seo_title_en');
                }
                if (! Schema::hasColumn('product_variants', 'seo_description_en')) {
                    $table->text('seo_description_en')->nullable()->after('seo_description_lt');
                }

                // Add performance tracking
                if (! Schema::hasColumn('product_variants', 'views_count')) {
                    $table->integer('views_count')->default(0)->after('sold_quantity');
                }
                if (! Schema::hasColumn('product_variants', 'clicks_count')) {
                    $table->integer('clicks_count')->default(0)->after('views_count');
                }
                if (! Schema::hasColumn('product_variants', 'conversion_rate')) {
                    $table->decimal('conversion_rate', 5, 4)->default(0)->after('clicks_count');
                }

                // Add variant-specific features
                if (! Schema::hasColumn('product_variants', 'is_featured')) {
                    $table->boolean('is_featured')->default(false)->after('is_on_sale');
                }
                if (! Schema::hasColumn('product_variants', 'is_new')) {
                    $table->boolean('is_new')->default(false)->after('is_featured');
                }
                if (! Schema::hasColumn('product_variants', 'is_bestseller')) {
                    $table->boolean('is_bestseller')->default(false)->after('is_new');
                }

                // Add variant combinations
                if (! Schema::hasColumn('product_variants', 'variant_combination_hash')) {
                    $table->string('variant_combination_hash', 64)->nullable()->after('variant_metadata');
                }

                // Add indexes for performance
                $table->index(['product_id', 'is_enabled', 'is_featured']);
                $table->index(['product_id', 'variant_type', 'size']);
                $table->index(['is_on_sale', 'sale_start_date', 'sale_end_date']);
                $table->index(['is_featured', 'is_new', 'is_bestseller']);
                $table->index(['views_count', 'clicks_count', 'conversion_rate']);
                $table->index(['variant_combination_hash']);
            });
        }

        // Create variant_attribute_values table for better attribute management
        if (! Schema::hasTable('variant_attribute_values')) {
            Schema::create('variant_attribute_values', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('variant_id');
                $table->unsignedBigInteger('attribute_id');
                $table->string('attribute_name');
                $table->string('attribute_value');
                $table->string('attribute_value_display')->nullable();
                $table->string('attribute_value_lt')->nullable();
                $table->string('attribute_value_en')->nullable();
                $table->string('attribute_value_slug');
                $table->integer('sort_order')->default(0);
                $table->boolean('is_filterable')->default(true);
                $table->boolean('is_searchable')->default(true);
                $table->timestamps();

                $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
                $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');

                $table->unique(['variant_id', 'attribute_id']);
                $table->index(['attribute_id', 'attribute_value']);
                $table->index(['variant_id', 'sort_order']);
                $table->index(['is_filterable', 'is_searchable']);
            });
        }

        // Create variant_price_history table for price tracking
        if (! Schema::hasTable('variant_price_history')) {
            Schema::create('variant_price_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('variant_id');
                $table->decimal('old_price', 10, 4);
                $table->decimal('new_price', 10, 4);
                $table->string('price_type')->default('regular'); // regular, sale, wholesale, member
                $table->string('change_reason')->nullable();
                $table->unsignedBigInteger('changed_by')->nullable();
                $table->timestamp('effective_from')->nullable();
                $table->timestamp('effective_until')->nullable();
                $table->timestamps();

                $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
                $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');

                $table->index(['variant_id', 'price_type', 'created_at']);
                $table->index(['effective_from', 'effective_until']);
            });
        }

        // Create variant_stock_history table for stock tracking
        if (! Schema::hasTable('variant_stock_history')) {
            Schema::create('variant_stock_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('variant_id');
                $table->integer('old_quantity');
                $table->integer('new_quantity');
                $table->integer('quantity_change');
                $table->string('change_type')->default('adjustment'); // adjustment, sale, return, restock
                $table->string('change_reason')->nullable();
                $table->unsignedBigInteger('changed_by')->nullable();
                $table->string('reference_type')->nullable(); // order, return, adjustment
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->timestamps();

                $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
                $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');

                $table->index(['variant_id', 'change_type', 'created_at']);
                $table->index(['reference_type', 'reference_id']);
            });
        }

        // Create variant_analytics table for performance tracking
        if (! Schema::hasTable('variant_analytics')) {
            Schema::create('variant_analytics', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('variant_id');
                $table->date('date');
                $table->integer('views')->default(0);
                $table->integer('clicks')->default(0);
                $table->integer('add_to_cart')->default(0);
                $table->integer('purchases')->default(0);
                $table->decimal('revenue', 10, 4)->default(0);
                $table->decimal('conversion_rate', 5, 4)->default(0);
                $table->timestamps();

                $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');

                $table->unique(['variant_id', 'date']);
                $table->index(['date', 'views', 'clicks']);
                $table->index(['variant_id', 'conversion_rate']);
            });
        }

        // Create variant_recommendations table for related variants
        if (! Schema::hasTable('variant_recommendations')) {
            Schema::create('variant_recommendations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('variant_id');
                $table->unsignedBigInteger('recommended_variant_id');
                $table->string('recommendation_type')->default('similar'); // similar, complementary, upsell, cross_sell
                $table->decimal('confidence_score', 3, 2)->default(0);
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
                $table->foreign('recommended_variant_id')->references('id')->on('product_variants')->onDelete('cascade');

                $table->unique(['variant_id', 'recommended_variant_id']);
                $table->index(['recommendation_type', 'confidence_score'], 'variant_recommend_type_idx');
                $table->index(['is_active', 'sort_order']);
            });
        }

        // Create variant_bundles table for bundle products
        if (! Schema::hasTable('variant_bundles')) {
            Schema::create('variant_bundles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('variant_id');
                $table->unsignedBigInteger('bundled_variant_id');
                $table->integer('quantity')->default(1);
                $table->decimal('discount_percentage', 5, 2)->default(0);
                $table->decimal('fixed_discount', 10, 4)->default(0);
                $table->boolean('is_required')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
                $table->foreign('bundled_variant_id')->references('id')->on('product_variants')->onDelete('cascade');

                $table->index(['variant_id', 'sort_order']);
                $table->index(['bundled_variant_id']);
            });
        }
    }

    public function down(): void
    {
        // Drop new tables
        if (Schema::hasTable('variant_bundles')) {
            Schema::dropIfExists('variant_bundles');
        }

        if (Schema::hasTable('variant_recommendations')) {
            Schema::dropIfExists('variant_recommendations');
        }

        if (Schema::hasTable('variant_analytics')) {
            Schema::dropIfExists('variant_analytics');
        }

        if (Schema::hasTable('variant_stock_history')) {
            Schema::dropIfExists('variant_stock_history');
        }

        if (Schema::hasTable('variant_price_history')) {
            Schema::dropIfExists('variant_price_history');
        }

        if (Schema::hasTable('variant_attribute_values')) {
            Schema::dropIfExists('variant_attribute_values');
        }

        // Remove added columns from product_variants table
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $columns = [
                    'variant_name_lt', 'variant_name_en', 'description_lt', 'description_en',
                    'wholesale_price', 'member_price', 'promotional_price',
                    'is_on_sale', 'sale_start_date', 'sale_end_date',
                    'reserved_quantity', 'available_quantity', 'sold_quantity',
                    'seo_title_lt', 'seo_title_en', 'seo_description_lt', 'seo_description_en',
                    'views_count', 'clicks_count', 'conversion_rate',
                    'is_featured', 'is_new', 'is_bestseller', 'variant_combination_hash',
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
