<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_collection_payload_contains_expected_fields(): void
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()
            ->hasAttached($category, [], 'categories')
            ->create([
                'brand_id'     => $brand->id,
                'is_visible'   => true,
                'published_at' => now()->subDay(),
                'status'       => 'published',
            ]);

        $product->load(['brand', 'categories']);

        $response = $this->getJson(route('api.products.catalog'));
        $response->assertOk();

        $item = $response->json('data.items.0');

        // Ensure the lightweight product contract exposes the expected scalar values for clients.
        $this->assertSame($product->id, $item['id']);
        $this->assertSame($product->name, $item['name']);
        $this->assertSame($brand->name, $item['brand']);
        $this->assertContains($category->name, $item['categories']);
    }

    public function test_product_availability_helpers(): void
    {
        $product = Product::factory()->create([
            'manage_stock'        => true,
            'stock_quantity'      => 10,
            'low_stock_threshold' => 5,
        ]);

        $this->assertTrue($product->isInStock());
        $this->assertFalse($product->isLowStock());

        $product->update(['stock_quantity' => 2]);

        $this->assertTrue($product->fresh()->isLowStock());
    }

    public function test_product_related_data_queries(): void
    {
        $product = Product::factory()->create();

        ProductImage::factory()->count(2)->create([
            'product_id' => $product->id,
        ]);

        Review::factory()->approved()->count(2)->create([
            'product_id' => $product->id,
            'rating'     => 4,
        ]);

        $product->loadCount(['images', 'reviews']);
        $product->loadAvg('reviews as average_rating', 'rating');

        $this->assertSame(2, $product->images_count);
        $this->assertSame(2, $product->reviews_count);
        $this->assertSame(4.0, (float) $product->average_rating);
    }
}
