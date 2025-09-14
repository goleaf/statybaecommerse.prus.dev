<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\ProductGalleryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

final class SimpleSplitInTest extends TestCase
{
    use RefreshDatabase;

    public function test_laravel_collections_splitin_method_exists(): void
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        
        $this->assertTrue(method_exists($collection, 'splitIn'));
    }

    public function test_splitin_divides_collection_into_specified_groups(): void
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $result = $collection->splitIn(3);
        
        $this->assertCount(3, $result);
        $this->assertEquals(4, $result->get(0)->count());
        $this->assertEquals(4, $result->get(1)->count());
        $this->assertEquals(2, $result->get(2)->count());
    }

    public function test_splitin_distributes_items_evenly(): void
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]);
        $result = $collection->splitIn(3);
        
        $this->assertCount(3, $result);
        $this->assertEquals(4, $result->get(0)->count());
        $this->assertEquals(4, $result->get(1)->count());
        $this->assertEquals(3, $result->get(2)->count());
    }

    public function test_product_gallery_service_arrange_for_gallery(): void
    {
        $products = collect([
            (object) ['id' => 1, 'name' => 'Product 1'],
            (object) ['id' => 2, 'name' => 'Product 2'],
            (object) ['id' => 3, 'name' => 'Product 3'],
            (object) ['id' => 4, 'name' => 'Product 4'],
            (object) ['id' => 5, 'name' => 'Product 5'],
            (object) ['id' => 6, 'name' => 'Product 6'],
        ]);
        
        $galleryService = new ProductGalleryService();
        $result = $galleryService->arrangeForGallery($products, 3);
        
        $this->assertCount(3, $result);
        $this->assertEquals(1, $result->get(0)['column_id']);
        $this->assertEquals(2, $result->get(1)['column_id']);
        $this->assertEquals(3, $result->get(2)['column_id']);
        $this->assertEquals(2, $result->get(0)['item_count']);
        $this->assertEquals(2, $result->get(1)['item_count']);
        $this->assertEquals(2, $result->get(2)['item_count']);
    }

    public function test_product_gallery_service_arrange_for_masonry(): void
    {
        $products = collect([
            (object) ['id' => 1, 'name' => 'Product 1'],
            (object) ['id' => 2, 'name' => 'Product 2'],
            (object) ['id' => 3, 'name' => 'Product 3'],
            (object) ['id' => 4, 'name' => 'Product 4'],
            (object) ['id' => 5, 'name' => 'Product 5'],
        ]);
        
        $galleryService = new ProductGalleryService();
        $result = $galleryService->arrangeForMasonry($products, 2);
        
        $this->assertCount(2, $result);
        $this->assertEquals(3, $result->get(0)->count());
        $this->assertEquals(2, $result->get(1)->count());
    }

    public function test_splitin_with_empty_collection(): void
    {
        $collection = collect([]);
        $result = $collection->splitIn(3);
        
        // Empty collection returns empty result
        $this->assertCount(0, $result);
    }

    public function test_splitin_with_single_item(): void
    {
        $collection = collect([1]);
        $result = $collection->splitIn(3);
        
        // Single item with 3 groups returns 1 collection with 1 item
        $this->assertCount(1, $result);
        $this->assertEquals(1, $result->get(0)->count());
    }

    public function test_splitin_with_more_groups_than_items(): void
    {
        $collection = collect([1, 2, 3]);
        $result = $collection->splitIn(5);
        
        // 3 items with 5 groups returns 3 collections
        $this->assertCount(3, $result);
        $this->assertEquals(1, $result->get(0)->count());
        $this->assertEquals(1, $result->get(1)->count());
        $this->assertEquals(1, $result->get(2)->count());
    }

    public function test_splitin_with_string_data(): void
    {
        $collection = collect(['apple', 'banana', 'cherry', 'date', 'elderberry', 'fig', 'grape']);
        $result = $collection->splitIn(2);
        
        $this->assertCount(2, $result);
        $this->assertEquals(4, $result->get(0)->count());
        $this->assertEquals(3, $result->get(1)->count());
    }
}
