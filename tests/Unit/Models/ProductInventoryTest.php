<?php declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_stock_quantity_casting(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => '100',
            'low_stock_threshold' => '10',
        ]);

        $this->assertIsInt($product->stock_quantity);
        $this->assertIsInt($product->low_stock_threshold);
        $this->assertEquals(100, $product->stock_quantity);
        $this->assertEquals(10, $product->low_stock_threshold);
    }

    public function test_product_manage_stock_casting(): void
    {
        $product = Product::factory()->create([
            'manage_stock' => '1',
        ]);

        $this->assertIsBool($product->manage_stock);
        $this->assertTrue($product->manage_stock);
    }

    public function test_product_is_in_stock(): void
    {
        $product = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 10,
            'low_stock_threshold' => 5,
        ]);

        $this->assertTrue($product->isInStock());
    }

    public function test_product_is_low_stock(): void
    {
        $product = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 3,
            'low_stock_threshold' => 5,
        ]);

        $this->assertTrue($product->isLowStock());
    }

    public function test_product_is_out_of_stock(): void
    {
        $product = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 0,
        ]);

        $this->assertTrue($product->isOutOfStock());
    }

    public function test_product_not_tracked_is_always_in_stock(): void
    {
        $product = Product::factory()->create([
            'manage_stock' => false,
            'stock_quantity' => 0,
        ]);

        $this->assertTrue($product->isInStock());
        $this->assertFalse($product->isLowStock());
        $this->assertFalse($product->isOutOfStock());
    }

    public function test_product_stock_status_in_stock(): void
    {
        $product = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 10,
            'low_stock_threshold' => 5,
        ]);

        $this->assertEquals('in_stock', $product->getStockStatus());
    }

    public function test_product_stock_status_low_stock(): void
    {
        $product = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 3,
            'low_stock_threshold' => 5,
        ]);

        $this->assertEquals('low_stock', $product->getStockStatus());
    }

    public function test_product_stock_status_out_of_stock(): void
    {
        $product = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 0,
        ]);

        $this->assertEquals('out_of_stock', $product->getStockStatus());
    }

    public function test_product_stock_status_not_tracked(): void
    {
        $product = Product::factory()->create([
            'manage_stock' => false,
        ]);

        $this->assertEquals('not_tracked', $product->getStockStatus());
    }

    public function test_product_can_decrease_stock(): void
    {
        $product = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 10,
        ]);

        $result = $product->decreaseStock(3);

        $this->assertTrue($result);
        $this->assertEquals(7, $product->fresh()->stock_quantity);
    }

    public function test_product_cannot_decrease_stock_below_zero(): void
    {
        $product = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 5,
        ]);

        $result = $product->decreaseStock(10);

        $this->assertFalse($result);
        $this->assertEquals(5, $product->fresh()->stock_quantity);
    }

    public function test_product_can_increase_stock(): void
    {
        $product = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 10,
        ]);

        $product->increaseStock(5);

        $this->assertEquals(15, $product->fresh()->stock_quantity);
    }

    public function test_product_reserved_quantity(): void
    {
        $product = Product::factory()->create();

        // For simple products, reserved quantity should be 0
        $this->assertEquals(0, $product->reservedQuantity());
    }
}
