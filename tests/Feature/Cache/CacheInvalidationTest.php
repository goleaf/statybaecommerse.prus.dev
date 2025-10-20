<?php

declare(strict_types=1);

namespace Tests\Feature\Cache;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\TagAwareCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_mutation_flushes_expected_tags(): void
    {
        $fake = TagAwareCache::fake();

        $category = Category::factory()->create();
        $product = Product::factory()->published()->create([
            'brand_id' => Brand::factory(),
            'is_visible' => true,
            'published_at' => now(),
        ]);

        $product->categories()->attach($category->id);
        $product->update(['name' => 'Updated Name']);

        $fake->assertFlushed([
            CacheKeys::homeTag(),
            CacheKeys::productAggregateTag(),
            CacheKeys::productTag($product->id),
            CacheKeys::brandTag((int) $product->brand_id),
            CacheKeys::categoryTag($category->id),
        ]);
    }

    public function test_variant_price_change_flushes_product_tags(): void
    {
        $fake = TagAwareCache::fake();

        $product = Product::factory()->published()->create([
            'brand_id' => Brand::factory(),
            'is_visible' => true,
            'published_at' => now(),
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 10.0,
        ]);

        $variant->update(['price' => 15.0]);

        $fake->assertFlushed([
            CacheKeys::homeTag(),
            CacheKeys::productAggregateTag(),
            CacheKeys::productTag($product->id),
        ]);
    }

    public function test_variant_stock_change_flushes_product_tags(): void
    {
        $fake = TagAwareCache::fake();

        $product = Product::factory()->published()->create([
            'brand_id' => Brand::factory(),
            'is_visible' => true,
            'published_at' => now(),
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock_quantity' => 5,
        ]);

        $variant->update(['stock_quantity' => 8]);

        $fake->assertFlushed([
            CacheKeys::homeTag(),
            CacheKeys::productAggregateTag(),
            CacheKeys::productTag($product->id),
        ]);
    }

    public function test_category_update_flushes_category_tag(): void
    {
        $fake = TagAwareCache::fake();

        $category = Category::factory()->create(['name' => 'Root']);

        $category->update(['name' => 'Updated Root']);

        $fake->assertFlushed([
            CacheKeys::homeTag(),
            CacheKeys::categoryTag($category->id),
        ]);
    }
}
