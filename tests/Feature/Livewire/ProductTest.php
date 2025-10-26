<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_factory_creates_valid_product(): void
    {
        $product = Product::factory()->create([
            'is_visible'          => true,
            'published_at'        => now()->subDay(),
            'status'              => 'published',
            'stock_quantity'      => 10,
            'manage_stock'        => true,
            'low_stock_threshold' => 5,
        ]);

        $this->assertTrue($product->isPublished());
        $this->assertTrue($product->isInStock());
        $this->assertFalse($product->isLowStock());
        $this->assertSame('in_stock', $product->stock_status);
    }

    public function test_product_relationships_are_accessible(): void
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()
            ->hasAttached($category, [], 'categories')
            ->create(['brand_id' => $brand->id]);

        $this->assertTrue($product->brand->is($brand));
        $this->assertTrue($product->categories->contains($category));
    }

    public function test_product_reviews_and_average_rating(): void
    {
        $product = Product::factory()->create();

        Review::factory()->approved()->count(3)->create([
            'product_id' => $product->id,
            'rating'     => 5,
        ]);

        $product->loadAvg('reviews as average_rating', 'rating');
        $product->loadCount('reviews');

        $this->assertSame(3, $product->reviews_count);
        $this->assertSame(5.0, (float) $product->average_rating);
    }

    public function test_product_discount_percentage_accessor(): void
    {
        $product = Product::factory()->create([
            'price'         => '80.00',
            'compare_price' => '100.00',
        ]);

        $this->assertSame(20.0, $product->discount_percentage);
    }
}
