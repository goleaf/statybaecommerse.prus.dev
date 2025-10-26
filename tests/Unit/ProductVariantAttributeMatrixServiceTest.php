<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductVariantAttributeMatrixService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

final class ProductVariantAttributeMatrixServiceTest extends UnitTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        self::ensureSchema();
    }

    public function test_sync_creates_and_clears_pivot_relations(): void
    {
        $attribute = Attribute::factory()->create(['name' => 'Material', 'slug' => 'material-test']);
        $valueCotton = AttributeValue::factory()->for($attribute)->create(['value' => 'Cotton']);
        $valueSilk = AttributeValue::factory()->for($attribute)->create(['value' => 'Silk']);

        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'variant_attribute_matrix' => null]);

        ProductVariantAttributeMatrixService::sync($variant, ['attribute_' . $attribute->getKey() => $valueCotton->getKey()]);

        $this->assertDatabaseHas('product_variant_attributes', [
            'variant_id'         => $variant->getKey(),
            'attribute_id'       => $attribute->getKey(),
            'attribute_value_id' => $valueCotton->getKey(),
        ]);

        ProductVariantAttributeMatrixService::sync($variant->fresh(), ['attribute_' . $attribute->getKey() => $valueSilk->getKey()]);

        $this->assertDatabaseHas('product_variant_attributes', [
            'variant_id'         => $variant->getKey(),
            'attribute_id'       => $attribute->getKey(),
            'attribute_value_id' => $valueSilk->getKey(),
        ]);

        $this->assertDatabaseMissing('product_variant_attributes', [
            'variant_id'         => $variant->getKey(),
            'attribute_id'       => $attribute->getKey(),
            'attribute_value_id' => $valueCotton->getKey(),
        ]);

        ProductVariantAttributeMatrixService::sync($variant->fresh(), []);

        $this->assertDatabaseMissing('product_variant_attributes', [
            'variant_id'   => $variant->getKey(),
            'attribute_id' => $attribute->getKey(),
        ]);
    }

    private static bool $schemaEnsured = false;

    private static function ensureSchema(): void
    {
        if (self::$schemaEnsured) {
            return;
        }

        $schema = Schema::connection('sqlite');

        if (! $schema->hasTable('brands')) {
            $schema->create('brands', static function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('website')->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_featured')->default(false);
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! $schema->hasTable('attributes')) {
            $schema->create('attributes', static function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('type')->default('text');
                $table->text('description')->nullable();
                $table->text('validation_rules')->nullable();
                $table->text('default_value')->nullable();
                $table->boolean('is_required')->default(false);
                $table->boolean('is_filterable')->default(false);
                $table->boolean('is_searchable')->default(false);
                $table->boolean('is_visible')->default(true);
                $table->boolean('is_editable')->default(true);
                $table->boolean('is_sortable')->default(false);
                $table->integer('sort_order')->default(0);
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('group_name')->nullable();
                $table->string('icon')->nullable();
                $table->string('color')->nullable();
                $table->decimal('min_value', 10, 2)->nullable();
                $table->decimal('max_value', 10, 2)->nullable();
                $table->decimal('step_value', 10, 2)->nullable();
                $table->string('placeholder')->nullable();
                $table->text('help_text')->nullable();
                $table->json('meta_data')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! $schema->hasTable('attribute_values')) {
            $schema->create('attribute_values', static function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('attribute_id');
                $table->string('value');
                $table->string('slug');
                $table->string('attribute_value_type')->nullable();
                $table->string('valueable_type')->nullable();
                $table->unsignedBigInteger('valueable_id')->nullable();
                $table->string('color_code')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->boolean('is_searchable')->default(false);
                $table->string('display_value')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('attribute_id');
                $table->index('slug');
            });
        }

        if (! $schema->hasTable('attribute_value_translations')) {
            $schema->create('attribute_value_translations', static function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('attribute_value_id');
                $table->string('locale', 10);
                $table->string('value');
                $table->text('description')->nullable();
                $table->timestamps();

                $table->index('attribute_value_id');
                $table->index('locale');
                $table->unique(['attribute_value_id', 'locale']);
            });
        }

        if (! $schema->hasTable('products')) {
            $schema->create('products', static function (Blueprint $table): void {
                $table->id();
                $table->string('type')->default('simple');
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('sku')->nullable()->unique();
                $table->text('description')->nullable();
                $table->text('short_description')->nullable();
                $table->decimal('price', 10, 2)->nullable();
                $table->decimal('sale_price', 10, 2)->nullable();
                $table->decimal('compare_price', 10, 2)->nullable();
                $table->decimal('cost_price', 10, 2)->nullable();
                $table->unsignedBigInteger('brand_id')->nullable();
                $table->boolean('manage_stock')->default(true);
                $table->integer('stock_quantity')->default(0);
                $table->integer('low_stock_threshold')->default(0);
                $table->decimal('weight', 10, 2)->nullable();
                $table->decimal('length', 10, 2)->nullable();
                $table->decimal('width', 10, 2)->nullable();
                $table->decimal('height', 10, 2)->nullable();
                $table->boolean('is_visible')->default(true);
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_featured')->default(false);
                $table->string('status')->default('draft');
                $table->timestamp('published_at')->nullable();
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('brand_id');
                $table->index('status');
            });
        }

        if (! $schema->hasTable('product_variants')) {
            $schema->create('product_variants', static function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->string('name');
                $table->string('sku')->unique();
                $table->string('barcode')->nullable();
                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('compare_price', 10, 2)->nullable();
                $table->decimal('cost_price', 10, 2)->nullable();
                $table->integer('stock_quantity')->default(0);
                $table->integer('reserved_quantity')->default(0);
                $table->integer('available_quantity')->default(0);
                $table->integer('sold_quantity')->default(0);
                $table->decimal('weight', 10, 2)->nullable();
                $table->boolean('track_inventory')->default(true);
                $table->boolean('is_default')->default(false);
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_new')->default(false);
                $table->boolean('is_bestseller')->default(false);
                $table->boolean('is_on_sale')->default(false);
                $table->json('attributes')->nullable();
                $table->json('variant_attribute_matrix')->nullable();
                $table->json('variant_metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('product_id');
                $table->index(['product_id', 'is_enabled']);
            });
        }

        if (! $schema->hasTable('product_attributes')) {
            $schema->create('product_attributes', static function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('attribute_id');
                $table->unsignedBigInteger('attribute_value_id');
                $table->timestamps();

                $table->index('product_id');
                $table->index('attribute_id');
                $table->index('attribute_value_id');
            });
        }

        if (! $schema->hasTable('categories')) {
            $schema->create('categories', static function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_visible')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->index('parent_id');
                $table->index('is_visible');
            });
        }

        if (! $schema->hasTable('product_categories')) {
            $schema->create('product_categories', static function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('category_id');
                $table->timestamps();

                $table->index('product_id');
                $table->index('category_id');
            });
        }

        if (! $schema->hasTable('product_variant_attributes')) {
            $schema->create('product_variant_attributes', static function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('variant_id');
                $table->unsignedBigInteger('attribute_value_id');
                $table->unsignedBigInteger('attribute_id')->nullable();
                $table->timestamps();

                $table->index('variant_id');
                $table->index('attribute_value_id');
            });
        }

        if (! $schema->hasTable('variant_attribute_values')) {
            $schema->create('variant_attribute_values', static function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('variant_id');
                $table->unsignedBigInteger('attribute_id');
                $table->string('attribute_name')->nullable();
                $table->string('attribute_value');
                $table->string('attribute_value_display')->nullable();
                $table->string('attribute_value_lt')->nullable();
                $table->string('attribute_value_en')->nullable();
                $table->string('attribute_value_slug');
                $table->integer('sort_order')->default(0);
                $table->boolean('is_filterable')->default(true);
                $table->boolean('is_searchable')->default(true);
                $table->timestamps();

                $table->index('variant_id');
                $table->index('attribute_id');
                $table->index(['attribute_id', 'attribute_value_slug']);
            });
        }

        if (! $schema->hasTable('activity_log')) {
            $schema->create('activity_log', static function (Blueprint $table): void {
                $table->id();
                $table->string('log_name')->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->string('subject_type')->nullable();
                $table->unsignedBigInteger('causer_id')->nullable();
                $table->string('causer_type')->nullable();
                $table->json('properties')->nullable();
                $table->uuid('batch_uuid')->nullable();
                $table->string('event')->nullable();
                $table->timestamps();

                $table->index('log_name');
                $table->index(['subject_type', 'subject_id']);
                $table->index(['causer_type', 'causer_id']);
                $table->index('batch_uuid');
                $table->index('event');
            });
        }

        self::$schemaEnsured = true;
    }
}
