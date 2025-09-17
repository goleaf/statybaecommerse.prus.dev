<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Enhance product_variants (forward-only)
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                // Multilingual names/descriptions
                if (!Schema::hasColumn('product_variants', 'variant_name_lt')) {
                    $table->string('variant_name_lt')->nullable()->after('name');
                }
                if (!Schema::hasColumn('product_variants', 'variant_name_en')) {
                    $table->string('variant_name_en')->nullable()->after('variant_name_lt');
                }
                if (!Schema::hasColumn('product_variants', 'description_lt')) {
                    $table->text('description_lt')->nullable()->after('variant_name_en');
                }
                if (!Schema::hasColumn('product_variants', 'description_en')) {
                    $table->text('description_en')->nullable()->after('description_lt');
                }

                // Pricing (normalized EUR precision)
                foreach (['wholesale_price', 'member_price', 'promotional_price'] as $col) {
                    if (!Schema::hasColumn('product_variants', $col)) {
                        $table->decimal($col, 15, 2)->nullable()->after('cost_price');
                    }
                }

                // Promotions
                if (!Schema::hasColumn('product_variants', 'is_on_sale')) {
                    $table->boolean('is_on_sale')->default(false)->after('is_enabled');
                }
                if (!Schema::hasColumn('product_variants', 'sale_start_date')) {
                    $table->timestamp('sale_start_date')->nullable()->after('is_on_sale');
                }
                if (!Schema::hasColumn('product_variants', 'sale_end_date')) {
                    $table->timestamp('sale_end_date')->nullable()->after('sale_start_date');
                }

                // Inventory advanced
                foreach (['reserved_quantity', 'available_quantity', 'sold_quantity'] as $col) {
                    if (!Schema::hasColumn('product_variants', $col)) {
                        $table->integer($col)->default(0)->after('stock_quantity');
                    }
                }

                // SEO
                foreach (['seo_title_lt' => 'string', 'seo_title_en' => 'string', 'seo_description_lt' => 'text', 'seo_description_en' => 'text'] as $name => $type) {
                    if (!Schema::hasColumn('product_variants', $name)) {
                        match ($type) {
                            'string' => $table->string($name)->nullable()->after('variant_metadata'),
                            'text' => $table->text($name)->nullable()->after('variant_metadata'),
                            default => null,
                        };
                    }
                }

                // Performance
                foreach (['views_count' => 'integer', 'clicks_count' => 'integer', 'conversion_rate' => 'decimal'] as $name => $type) {
                    if (!Schema::hasColumn('product_variants', $name)) {
                        match ($type) {
                            'integer' => $table->integer($name)->default(0)->after('sold_quantity'),
                            'decimal' => $table->decimal($name, 5, 4)->default(0)->after('clicks_count'),
                            default => null,
                        };
                    }
                }

                // Features
                foreach (['is_featured', 'is_new', 'is_bestseller'] as $name) {
                    if (!Schema::hasColumn('product_variants', $name)) {
                        $table->boolean($name)->default(false)->after('is_on_sale');
                    }
                }
                if (!Schema::hasColumn('product_variants', 'variant_combination_hash')) {
                    $table->string('variant_combination_hash', 64)->nullable()->after('variant_metadata');
                }

                // Indexes
                try {
                    $table->index(['product_id', 'is_enabled', 'is_featured']);
                } catch (\Throwable $e) {
                }
                try {
                    $table->index(['product_id', 'variant_type', 'size']);
                } catch (\Throwable $e) {
                }
                try {
                    $table->index(['is_on_sale', 'sale_start_date', 'sale_end_date']);
                } catch (\Throwable $e) {
                }
                try {
                    $table->index(['is_featured', 'is_new', 'is_bestseller']);
                } catch (\Throwable $e) {
                }
                try {
                    $table->index(['views_count', 'clicks_count', 'conversion_rate']);
                } catch (\Throwable $e) {
                }
                try {
                    $table->index(['variant_combination_hash']);
                } catch (\Throwable $e) {
                }
            });
        }

        // variant_attribute_values
        if (Schema::hasTable('product_variants') && Schema::hasTable('attributes') && !Schema::hasTable('variant_attribute_values')) {
            Schema::create('variant_attribute_values', function (Blueprint $table): void {
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

                $table->foreign('variant_id')->references('id')->on('product_variants')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('attribute_id')->references('id')->on('attributes')->cascadeOnUpdate()->cascadeOnDelete();
                $table->unique(['variant_id', 'attribute_id']);
                $table->index(['attribute_id', 'attribute_value']);
                $table->index(['variant_id', 'sort_order']);
                $table->index(['is_filterable', 'is_searchable']);
            });
        }

        // variant_price_history
        if (Schema::hasTable('product_variants') && !Schema::hasTable('variant_price_history')) {
            Schema::create('variant_price_history', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('variant_id');
                $table->decimal('old_price', 15, 2);
                $table->decimal('new_price', 15, 2);
                $table->string('price_type')->default('regular');
                $table->string('change_reason')->nullable();
                $table->unsignedBigInteger('changed_by')->nullable();
                $table->timestamp('effective_from')->nullable();
                $table->timestamp('effective_until')->nullable();
                $table->timestamps();

                $table->foreign('variant_id')->references('id')->on('product_variants')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('changed_by')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
                $table->index(['variant_id', 'price_type', 'created_at']);
                $table->index(['effective_from', 'effective_until']);
            });
        }

        // variant_stock_history
        if (Schema::hasTable('product_variants') && !Schema::hasTable('variant_stock_history')) {
            Schema::create('variant_stock_history', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('variant_id');
                $table->integer('old_quantity');
                $table->integer('new_quantity');
                $table->integer('quantity_change');
                $table->string('change_type')->default('adjustment');
                $table->string('change_reason')->nullable();
                $table->unsignedBigInteger('changed_by')->nullable();
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->timestamps();

                $table->foreign('variant_id')->references('id')->on('product_variants')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('changed_by')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
                $table->index(['variant_id', 'change_type', 'created_at']);
                $table->index(['reference_type', 'reference_id']);
            });
        }

        // variant_analytics
        if (Schema::hasTable('product_variants') && !Schema::hasTable('variant_analytics')) {
            Schema::create('variant_analytics', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('variant_id');
                $table->date('date');
                $table->integer('views')->default(0);
                $table->integer('clicks')->default(0);
                $table->integer('add_to_cart')->default(0);
                $table->integer('purchases')->default(0);
                $table->decimal('revenue', 15, 2)->default(0);
                $table->decimal('conversion_rate', 5, 4)->default(0);
                $table->timestamps();

                $table->foreign('variant_id')->references('id')->on('product_variants')->cascadeOnUpdate()->cascadeOnDelete();
                $table->unique(['variant_id', 'date']);
                $table->index(['date', 'views', 'clicks']);
                $table->index(['variant_id', 'conversion_rate']);
            });
        }

        // variant_recommendations
        if (Schema::hasTable('product_variants') && !Schema::hasTable('variant_recommendations')) {
            Schema::create('variant_recommendations', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('variant_id');
                $table->unsignedBigInteger('recommended_variant_id');
                $table->string('recommendation_type')->default('similar');
                $table->decimal('confidence_score', 3, 2)->default(0);
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('variant_id')->references('id')->on('product_variants')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('recommended_variant_id')->references('id')->on('product_variants')->cascadeOnUpdate()->cascadeOnDelete();
                $table->unique(['variant_id', 'recommended_variant_id']);
                $table->index(['recommendation_type', 'confidence_score'], 'vr_type_confidence_idx');
                $table->index(['is_active', 'sort_order']);
            });
        }

        // variant_bundles
        if (Schema::hasTable('product_variants') && !Schema::hasTable('variant_bundles')) {
            Schema::create('variant_bundles', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('variant_id');
                $table->unsignedBigInteger('bundled_variant_id');
                $table->integer('quantity')->default(1);
                $table->decimal('discount_percentage', 5, 2)->default(0);
                $table->decimal('fixed_discount', 15, 2)->default(0);
                $table->boolean('is_required')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('variant_id')->references('id')->on('product_variants')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('bundled_variant_id')->references('id')->on('product_variants')->cascadeOnUpdate()->cascadeOnDelete();
                $table->index(['variant_id', 'sort_order']);
                $table->index(['bundled_variant_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('variant_bundles');
        Schema::dropIfExists('variant_recommendations');
        Schema::dropIfExists('variant_analytics');
        Schema::dropIfExists('variant_stock_history');
        Schema::dropIfExists('variant_price_history');
        Schema::dropIfExists('variant_attribute_values');

        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                $columns = [
                    'variant_name_lt',
                    'variant_name_en',
                    'description_lt',
                    'description_en',
                    'wholesale_price',
                    'member_price',
                    'promotional_price',
                    'is_on_sale',
                    'sale_start_date',
                    'sale_end_date',
                    'reserved_quantity',
                    'available_quantity',
                    'sold_quantity',
                    'seo_title_lt',
                    'seo_title_en',
                    'seo_description_lt',
                    'seo_description_en',
                    'views_count',
                    'clicks_count',
                    'conversion_rate',
                    'is_featured',
                    'is_new',
                    'is_bestseller',
                    'variant_combination_hash',
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
