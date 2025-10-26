<?php

declare(strict_types=1);

namespace Tests\Feature\Components;

use App\Livewire\ProductVariantShowcase;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantAttributeValue;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Tests\Feature\TestCase;

final class ProductVariantShowcaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => 'base64:' . base64_encode(str_repeat('a', 32))]);

        foreach ([
            'variant_attribute_values',
            'attribute_values',
            'attributes',
            'product_variants',
            'products',
            'product_categories',
            'category_product',
            'categories',
            'brands',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('brands', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('category_id');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->nullable();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(0);
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('manage_stock')->default(false);
            $table->boolean('track_stock')->default(false);
            $table->boolean('allow_backorder')->default(false);
            $table->string('status')->default('draft');
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_variants', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('name')->nullable();
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('available_quantity')->default(0);
            $table->decimal('weight', 10, 2)->nullable();
            $table->boolean('track_inventory')->default(true);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_on_sale')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);
            $table->boolean('is_bestseller')->default(false);
            $table->integer('low_stock_threshold')->default(5);
            $table->json('attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('attributes', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('attribute_values', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('attribute_id');
            $table->string('value');
            $table->string('display_value')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('variant_attribute_values', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('variant_id');
            $table->unsignedBigInteger('attribute_id')->nullable();
            $table->unsignedBigInteger('attribute_value_id')->nullable();
            $table->string('attribute_name')->nullable();
            $table->string('attribute_value')->nullable();
            $table->string('attribute_value_display')->nullable();
            $table->timestamps();
        });

        Brand::flushEventListeners();
        Product::flushEventListeners();
        ProductVariant::flushEventListeners();
        ProductVariant::retrieved(function (ProductVariant $variant): void {
            $variant->price = (float) $variant->price;
            $variant->compare_price = $variant->compare_price !== null ? (float) $variant->compare_price : null;
            $variant->promotional_price = $variant->promotional_price !== null ? (float) $variant->promotional_price : null;
        });
        Attribute::flushEventListeners();
        Attribute::resolveRelationUsing('attributeValues', fn (Attribute $attribute) => $attribute->values());
        AttributeValue::flushEventListeners();
        AttributeValue::resolveRelationUsing('variantAttributeValues', fn (AttributeValue $value) => $value->hasMany(VariantAttributeValue::class, 'attribute_value_id'));

        View::composer('livewire.product-variant-showcase', function ($view): void {
            if (! $view->offsetExists('attributes')) {
                $view->with('attributes', collect());
            }
        });
    }

    public function test_variant_metrics_do_not_trigger_additional_queries(): void
    {
        $product = Product::factory()->create([
            'is_visible'   => true,
            'status'       => 'published',
            'published_at' => now(),
        ]);

        $variants = [
            ['available_quantity' => 15, 'track_inventory' => true],
            ['available_quantity' => 2, 'track_inventory' => true],
            ['available_quantity' => 0, 'track_inventory' => true],
        ];

        foreach ($variants as $attributes) {
            ProductVariant::factory()->for($product)->create(array_merge([
                'stock_quantity'    => $attributes['available_quantity'],
                'reserved_quantity' => 0,
                'is_default'        => false,
            ], $attributes));
        }

        $component = Livewire::test(ProductVariantShowcase::class);

        $connection = DB::connection();
        $connection->enableQueryLog();
        $connection->flushQueryLog();

        $component->call('selectProduct', $product->id);

        $component->set('productVariants', collect());
        $component->set('selectedVariant', null);
        if ($component->instance()->selectedProduct) {
            $component->instance()->selectedProduct->setRelation('variants', collect());
        }

        $this->assertSame(3, $component->instance()->variantCounts['total_variants']);

        $connection->flushQueryLog();

        $accessors = [
            fn () => $component->instance()->variantCounts['total_variants'],
            fn () => $component->instance()->variantCounts['in_stock'],
            fn () => $component->instance()->variantCounts['low_stock'],
            fn () => $component->instance()->variantCounts['out_of_stock'],
        ];

        $queryCounts = [];

        foreach (range(1, count($accessors)) as $limit) {
            $connection->flushQueryLog();

            foreach (array_slice($accessors, 0, $limit) as $accessor) {
                $accessor();
            }

            $queryCounts[] = count($connection->getQueryLog());
        }

        $uniqueCounts = array_unique($queryCounts);

        $this->assertCount(1, $uniqueCounts);
        $this->assertSame(0, array_values($uniqueCounts)[0]);
    }
}
