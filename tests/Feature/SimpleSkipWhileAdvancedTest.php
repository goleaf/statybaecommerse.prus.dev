<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\SearchService;
use App\Services\DataFilteringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SimpleSkipWhileAdvancedTest extends TestCase
{
    use RefreshDatabase;

    public function test_skipwhile_basic_functionality(): void
    {
        // Test basic skipWhile functionality with a simple collection
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        $result = $collection->skipWhile(function ($item) {
            return $item < 5;
        });

        $this->assertEquals([5, 6, 7, 8, 9, 10], $result->values()->all());
    }

    public function test_skipwhile_with_strings(): void
    {
        $collection = collect(['apple', 'banana', 'cherry', 'date', 'elderberry']);

        $result = $collection->skipWhile(function ($item) {
            return strlen($item) <= 5;
        });

        $this->assertEquals(['banana', 'cherry', 'date', 'elderberry'], $result->values()->all());
    }

    public function test_skipwhile_with_objects(): void
    {
        $collection = collect([
            (object) ['name' => '', 'active' => true],
            (object) ['name' => 'Valid', 'active' => false],
            (object) ['name' => 'Another', 'active' => true],
            (object) ['name' => 'Good', 'active' => true],
        ]);

        $result = $collection->skipWhile(function ($item) {
            return empty($item->name) || !$item->active;
        });

        $this->assertCount(2, $result);
        $this->assertEquals('Another', $result->first()->name);
        $this->assertEquals('Good', $result->last()->name);
    }

    public function test_skipwhile_with_arrays(): void
    {
        $collection = collect([
            ['id' => 1, 'name' => '', 'active' => true],
            ['id' => 2, 'name' => 'Valid', 'active' => false],
            ['id' => 3, 'name' => 'Another', 'active' => true],
            ['id' => 4, 'name' => 'Good', 'active' => true],
        ]);

        $result = $collection->skipWhile(function ($item) {
            return empty($item['name']) || !$item['active'];
        });

        $this->assertCount(2, $result);
        $this->assertEquals('Another', $result->first()['name']);
        $this->assertEquals('Good', $result->last()['name']);
    }

    public function test_skipwhile_handles_empty_collection(): void
    {
        $collection = collect([]);

        $result = $collection->skipWhile(function ($item) {
            return $item < 5;
        });

        $this->assertCount(0, $result);
    }

    public function test_skipwhile_handles_all_skipped_items(): void
    {
        $collection = collect([1, 2, 3, 4, 5]);

        $result = $collection->skipWhile(function ($item) {
            return $item < 10; // All items will be skipped
        });

        $this->assertCount(0, $result);
    }

    public function test_skipwhile_handles_no_skipped_items(): void
    {
        $collection = collect([1, 2, 3, 4, 5]);

        $result = $collection->skipWhile(function ($item) {
            return $item < 0; // No items will be skipped
        });

        $this->assertCount(5, $result);
        $this->assertEquals([1, 2, 3, 4, 5], $result->values()->all());
    }

    public function test_skipwhile_with_complex_conditions(): void
    {
        $collection = collect([
            ['price' => 5, 'category' => 'electronics', 'in_stock' => true],
            ['price' => 50, 'category' => 'electronics', 'in_stock' => false],
            ['price' => 100, 'category' => 'clothing', 'in_stock' => true],
            ['price' => 200, 'category' => 'electronics', 'in_stock' => true],
        ]);

        $result = $collection->skipWhile(function ($item) {
            return $item['price'] < 10 || 
                   $item['category'] !== 'electronics' || 
                   !$item['in_stock'];
        });

        $this->assertCount(1, $result);
        $this->assertEquals(200, $result->first()['price']);
    }

    public function test_skipwhile_performance_with_large_collection(): void
    {
        // Create a large collection
        $collection = collect(range(1, 1000));

        $startTime = microtime(true);
        $result = $collection->skipWhile(function ($item) {
            return $item < 500;
        });
        $endTime = microtime(true);

        $this->assertCount(501, $result);
        $this->assertEquals(500, $result->first());
        $this->assertEquals(1000, $result->last());
        
        // Performance should be reasonable (less than 0.1 seconds for 1000 items)
        $this->assertLessThan(0.1, $endTime - $startTime);
    }

    public function test_skipwhile_with_data_filtering_service(): void
    {
        $service = new DataFilteringService();
        
        $products = collect([
            (object) ['name' => '', 'is_visible' => true, 'price' => 100, 'slug' => 'product-1', 'stock_quantity' => 5, 'is_published' => true],
            (object) ['name' => 'Valid Product', 'is_visible' => false, 'price' => 200, 'slug' => 'product-2', 'stock_quantity' => 10, 'is_published' => true],
            (object) ['name' => 'Another Valid', 'is_visible' => true, 'price' => 0, 'slug' => 'product-3', 'stock_quantity' => 15, 'is_published' => true],
            (object) ['name' => 'Good Product', 'is_visible' => true, 'price' => 300, 'slug' => 'product-4', 'stock_quantity' => 20, 'is_published' => true],
        ]);

        $filteredProducts = $service->filterQualityProducts($products);

        // Should skip first 3 products and only return the last one
        $this->assertCount(1, $filteredProducts);
        $this->assertEquals('product-4', $filteredProducts->first()->slug);
    }

    public function test_skipwhile_with_search_service(): void
    {
        $service = new SearchService();
        
        // Test that the service returns an array (basic functionality test)
        $results = $service->search('test');
        
        $this->assertIsArray($results);
        // Results can be empty or have items - both are valid
    }
}