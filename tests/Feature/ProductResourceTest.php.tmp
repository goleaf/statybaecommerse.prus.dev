<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_can_create_product(): void
    {
        $brand = Brand::factory()->create();

        $product = Product::factory()->create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TEST-001',
            'price' => 99.99,
            'brand_id' => $brand->id,
            'status' => 'published',
            'is_visible' => true,
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TEST-001',
            'price' => 99.99,
            'brand_id' => $brand->id,
            'status' => 'published',
            'is_visible' => true,
        ]);
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create();
        $newBrand = Brand::factory()->create();

        $product->update([
            'name' => 'Updated Product',
            'price' => 149.99,
            'brand_id' => $newBrand->id,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'price' => 149.99,
            'brand_id' => $newBrand->id,
        ]);
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $product->delete();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_product_sku_must_be_unique(): void
    {
        $existingProduct = Product::factory()->create(['sku' => 'UNIQUE-001']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Product::factory()->create(['sku' => 'UNIQUE-001']);
    }

    public function test_product_slug_must_be_unique(): void
    {
        $existingProduct = Product::factory()->create(['slug' => 'unique-product']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Product::factory()->create(['slug' => 'unique-product']);
    }

    public function test_can_toggle_product_visibility(): void
    {
        $product = Product::factory()->create(['is_visible' => false]);

        $product->update(['is_visible' => true]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_visible' => true,
        ]);
    }

    public function test_can_toggle_product_featured_status(): void
    {
        $product = Product::factory()->create(['is_featured' => false]);

        $product->update(['is_featured' => true]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_featured' => true,
        ]);
    }

    public function test_product_can_have_brand(): void
    {
        $brand = Brand::factory()->create();
        $product = Product::factory()->create(['brand_id' => $brand->id]);

        $this->assertEquals($brand->id, $product->brand_id);
        $this->assertInstanceOf(Brand::class, $product->brand);
    }

    public function test_product_can_calculate_discount_percentage(): void
    {
        $product = Product::factory()->create([
            'price' => 80.0,
            'compare_price' => 100.0,
        ]);

        $this->assertEquals(20.0, $product->discount_percentage);
    }

    public function test_product_stock_status(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 5,
            'low_stock_threshold' => 10,
            'manage_stock' => true,
        ]);

        $this->assertEquals('low_stock', $product->stock_status);
        $this->assertTrue($product->is_low_stock);
        $this->assertFalse($product->is_out_of_stock);
    }

    public function test_product_out_of_stock_status(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 0,
            'manage_stock' => true,
        ]);

        $this->assertEquals('out_of_stock', $product->stock_status);
        $this->assertTrue($product->is_out_of_stock);
        $this->assertFalse($product->is_in_stock);
    }
}
