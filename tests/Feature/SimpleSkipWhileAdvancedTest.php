<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\ProductGalleryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

final class SimpleSkipWhileAdvancedTest extends TestCase
{
    use RefreshDatabase;

    private ProductGalleryService $galleryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->galleryService = new ProductGalleryService();
    }

    public function test_basic_skip_while_functionality(): void
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        $subset = $collection->skipWhile(function (int $item) {
            return $item <= 5;
        });

        $this->assertEquals([5 => 6, 6 => 7, 7 => 8, 8 => 9, 9 => 10], $subset->all());
        $this->assertCount(5, $subset);
    }

    public function test_skip_while_with_objects(): void
    {
        $products = collect([
            (object) ['id' => 1, 'name' => '', 'is_visible' => true, 'price' => 100],
            (object) ['id' => 2, 'name' => 'Valid Product', 'is_visible' => false, 'price' => 200],
            (object) ['id' => 3, 'name' => 'Another Valid', 'is_visible' => true, 'price' => 0],
            (object) ['id' => 4, 'name' => 'Good Product', 'is_visible' => true, 'price' => 300],
        ]);

        $filteredProducts = $products->skipWhile(function ($product) {
            return empty($product->name) || 
                   !$product->is_visible ||
                   $product->price <= 0;
        });

        $this->assertCount(1, $filteredProducts);
        $this->assertEquals(4, $filteredProducts->first()->id);
    }

    public function test_product_gallery_service_basic_filtering(): void
    {
        $products = collect([
            (object) [
                'id' => 1, 
                'name' => '', 
                'is_visible' => true, 
                'price' => 100, 
                'slug' => 'product-1'
            ],
            (object) [
                'id' => 2, 
                'name' => 'Valid Product', 
                'is_visible' => true, 
                'price' => 200, 
                'slug' => 'product-2'
            ],
            (object) [
                'id' => 3, 
                'name' => 'Another Valid', 
                'is_visible' => true, 
                'price' => 300, 
                'slug' => 'product-3'
            ],
        ]);

        $result = $this->galleryService->arrangeForGallery($products, 2);

        // Should have 2 valid products (id 2 and 3) split into 2 columns
        $this->assertCount(2, $result);
        $this->assertGreaterThan(0, $result->count());
        
        // Test that the result is a collection with proper structure
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertTrue($result->isNotEmpty());
    }

    public function test_stock_filtering_basic(): void
    {
        $products = collect([
            (object) [
                'id' => 1, 
                'name' => 'Out of Stock', 
                'is_visible' => true, 
                'price' => 100, 
                'slug' => 'product-1',
                'stock_quantity' => 0,
                'is_available' => false
            ],
            (object) [
                'id' => 2, 
                'name' => 'In Stock', 
                'is_visible' => true, 
                'price' => 200, 
                'slug' => 'product-2',
                'stock_quantity' => 10,
                'is_available' => true
            ],
        ]);

        $filteredProducts = $this->galleryService->arrangeWithStockFiltering($products, true, 5);

        // Should only have 1 product (id 2) with stock >= 5
        $this->assertCount(1, $filteredProducts);
        $this->assertEquals(2, $filteredProducts->first()->id);
    }

    public function test_date_filtering_basic(): void
    {
        $now = now();
        $products = collect([
            (object) [
                'id' => 1, 
                'name' => 'Old Product', 
                'is_visible' => true, 
                'price' => 100, 
                'slug' => 'product-1',
                'created_at' => $now->copy()->subDays(400)
            ],
            (object) [
                'id' => 2, 
                'name' => 'Recent Product', 
                'is_visible' => true, 
                'price' => 200, 
                'slug' => 'product-2',
                'created_at' => $now->copy()->subDays(15)
            ],
        ]);

        $dateFilters = [
            'new_arrivals_days' => 30,
            'exclude_old' => true
        ];

        $filteredProducts = $this->galleryService->arrangeWithDateFiltering($products, $dateFilters);

        // Should only have 1 product (id 2) within 30 days
        $this->assertCount(1, $filteredProducts);
        $this->assertEquals(2, $filteredProducts->first()->id);
    }

    public function test_skip_while_with_split_in_combination(): void
    {
        $products = collect([
            (object) ['id' => 1, 'name' => '', 'is_visible' => true, 'price' => 100, 'slug' => 'product-1'],
            (object) ['id' => 2, 'name' => 'Valid Product 1', 'is_visible' => true, 'price' => 200, 'slug' => 'product-2'],
            (object) ['id' => 3, 'name' => 'Valid Product 2', 'is_visible' => true, 'price' => 300, 'slug' => 'product-3'],
            (object) ['id' => 4, 'name' => 'Valid Product 3', 'is_visible' => true, 'price' => 400, 'slug' => 'product-4'],
        ]);

        $validProducts = $products->skipWhile(function ($product) {
            return empty($product->name) || 
                   !$product->is_visible ||
                   $product->price <= 0 ||
                   empty($product->slug);
        });

        $splitProducts = $validProducts->splitIn(2);

        // Should have 2 columns with 2 products each
        $this->assertCount(2, $splitProducts);
        $this->assertEquals(2, $splitProducts->first()->count());
        $this->assertEquals(1, $splitProducts->last()->count());
        $this->assertEquals(2, $splitProducts->first()->first()->id);
        $this->assertEquals(4, $splitProducts->last()->first()->id);
    }

    public function test_skip_while_performance(): void
    {
        $products = collect();
        
        // Add 1000 invalid products first
        for ($i = 1; $i <= 1000; $i++) {
            $products->push((object) [
                'id' => $i,
                'name' => '',
                'is_visible' => false,
                'price' => 0,
                'slug' => ''
            ]);
        }
        
        // Add 10 valid products at the end
        for ($i = 1001; $i <= 1010; $i++) {
            $products->push((object) [
                'id' => $i,
                'name' => "Valid Product {$i}",
                'is_visible' => true,
                'price' => 100,
                'slug' => "product-{$i}"
            ]);
        }

        $startTime = microtime(true);
        
        $validProducts = $products->skipWhile(function ($product) {
            return empty($product->name) || 
                   !$product->is_visible ||
                   $product->price <= 0 ||
                   empty($product->slug);
        });

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertCount(10, $validProducts);
        $this->assertEquals(1001, $validProducts->first()->id);
        $this->assertEquals(1010, $validProducts->last()->id);
        $this->assertLessThan(1.0, $executionTime);
    }

    public function test_skip_while_with_complex_conditions(): void
    {
        $products = collect([
            (object) [
                'id' => 1, 
                'name' => 'Product 1', 
                'is_visible' => true, 
                'price' => 100, 
                'slug' => 'product-1',
                'stock_quantity' => 5,
                'is_available' => true
            ],
            (object) [
                'id' => 2, 
                'name' => 'Product 2', 
                'is_visible' => true, 
                'price' => 200, 
                'slug' => 'product-2',
                'stock_quantity' => 0,
                'is_available' => false
            ],
            (object) [
                'id' => 3, 
                'name' => 'Product 3', 
                'is_visible' => true, 
                'price' => 300, 
                'slug' => 'product-3',
                'stock_quantity' => 10,
                'is_available' => true
            ],
        ]);

        // Apply multiple skipWhile conditions
        $filteredProducts = $products->skipWhile(function ($product) {
            // Skip products that are out of stock
            if ($product->stock_quantity <= 0 || !$product->is_available) {
                return true;
            }
            
            return false;
        });

        // Should have all 3 products since the first one is in stock
        $this->assertCount(3, $filteredProducts);
        $this->assertEquals(1, $filteredProducts->first()->id);
        $this->assertEquals(3, $filteredProducts->last()->id);
    }
}
