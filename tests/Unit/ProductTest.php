<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_be_created(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-123',
            'price' => 99.99,
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'TEST-123',
            'price' => 99.99,
        ]);
    }

    public function test_product_belongs_to_brand(): void
    {
        $brand = Brand::factory()->create();
        $product = Product::factory()->create(['brand_id' => $brand->id]);

        $this->assertInstanceOf(Brand::class, $product->brand);
        $this->assertEquals($brand->id, $product->brand->id);
    }

    public function test_product_can_have_many_categories(): void
    {
        $product = Product::factory()->create();
        $categories = Category::factory()->count(3)->create();

        $product->categories()->attach($categories->pluck('id'));

        $this->assertCount(3, $product->categories);
        $this->assertInstanceOf(Category::class, $product->categories->first());
    }

    public function test_product_can_have_many_reviews(): void
    {
        $product = Product::factory()->create();
        \App\Models\Review::factory()->count(5)->create(['product_id' => $product->id]);

        $this->assertCount(5, $product->reviews);
        $this->assertInstanceOf(Review::class, $product->reviews->first());
    }

    public function test_product_is_published_method(): void
    {
        $publishedProduct = Product::factory()->create([
            'is_visible' => true,
            'published_at' => now()->subDay(),
        ]);

        $unpublishedProduct = Product::factory()->create([
            'is_visible' => false,
            'published_at' => now()->subDay(),
        ]);

        $futureProduct = Product::factory()->create([
            'is_visible' => true,
            'published_at' => now()->addDay(),
        ]);

        $this->assertTrue($publishedProduct->isPublished());
        $this->assertFalse($unpublishedProduct->isPublished());
        $this->assertFalse($futureProduct->isPublished());
    }

    public function test_product_stock_calculations(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 100,
            'manage_stock' => true,
        ]);

        $this->assertEquals(100, $product->availableQuantity());
        $this->assertFalse($product->isOutOfStock());

        $outOfStockProduct = Product::factory()->create([
            'stock_quantity' => 0,
            'manage_stock' => true,
        ]);

        $this->assertTrue($outOfStockProduct->isOutOfStock());
    }

    public function test_product_casts_work_correctly(): void
    {
        $product = Product::factory()->create([
            'price' => 99.99,
            'is_visible' => true,
            'published_at' => '2025-01-01 12:00:00',
        ]);

        $this->assertTrue(is_numeric($product->price)); // Decimal cast returns string in Laravel
        $this->assertIsBool($product->is_visible);
        $this->assertInstanceOf(\Carbon\Carbon::class, $product->published_at);
    }

    public function test_product_media_collections(): void
    {
        $product = Product::factory()->create();

        // Test that product implements HasMedia
        $this->assertInstanceOf(\Spatie\MediaLibrary\HasMedia::class, $product);
        
        // Test that product can handle media
        $this->assertTrue(method_exists($product, 'registerMediaCollections'));
        $this->assertTrue(method_exists($product, 'registerMediaConversions'));
    }
}