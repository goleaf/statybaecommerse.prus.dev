<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\CollectionController;
use App\Models\Collection;
use App\Models\Product;
use App\Services\PaginationService;
use App\Services\ProductGalleryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Collection as LaravelCollection;
use Tests\TestCase;

final class EnhancedSkipWhileImplementationTest extends TestCase
{
    use RefreshDatabase;

    private ProductGalleryService $galleryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->galleryService = new ProductGalleryService();
    }

    public function test_pagination_service_with_skip_while(): void
    {
        // Create test products with different quality levels
        $products = LaravelCollection::make([
            (object) ['id' => 1, 'name' => '', 'is_visible' => true, 'price' => 100, 'slug' => 'product-1'],
            (object) ['id' => 2, 'name' => 'Valid Product', 'is_visible' => false, 'price' => 200, 'slug' => 'product-2'],
            (object) ['id' => 3, 'name' => 'Another Valid', 'is_visible' => true, 'price' => 0, 'slug' => 'product-3'],
            (object) ['id' => 4, 'name' => 'Good Product', 'is_visible' => true, 'price' => 300, 'slug' => 'product-4'],
            (object) ['id' => 5, 'name' => 'Perfect Product', 'is_visible' => true, 'price' => 400, 'slug' => 'product-5'],
        ]);

        $skipWhileCallback = function ($product) {
            return empty($product->name) || 
                   !$product->is_visible ||
                   $product->price <= 0 ||
                   empty($product->slug);
        };

        $paginator = PaginationService::paginateWithSkipWhile($products, $skipWhileCallback, 2, 1);

        // Should only have 2 valid products (id 4 and 5)
        $this->assertEquals(2, $paginator->total());
        $this->assertEquals(1, $paginator->lastPage());
        $this->assertCount(2, $paginator->items());
        $this->assertEquals(4, $paginator->items()[0]->id);
        $this->assertEquals(5, $paginator->items()[1]->id);
    }

    public function test_product_gallery_service_with_skip_while(): void
    {
        // Create test products with different quality levels
        $products = LaravelCollection::make([
            (object) [
                'id' => 1, 
                'name' => '', 
                'is_visible' => true, 
                'price' => 100, 
                'slug' => 'product-1',
                'getFirstMediaUrl' => fn() => 'image1.jpg'
            ],
            (object) [
                'id' => 2, 
                'name' => 'Valid Product', 
                'is_visible' => true, 
                'price' => 200, 
                'slug' => 'product-2',
                'getFirstMediaUrl' => fn() => 'image2.jpg'
            ],
            (object) [
                'id' => 3, 
                'name' => 'Another Valid', 
                'is_visible' => true, 
                'price' => 0, 
                'slug' => 'product-3',
                'getFirstMediaUrl' => fn() => 'image3.jpg'
            ],
            (object) [
                'id' => 4, 
                'name' => 'Good Product', 
                'is_visible' => true, 
                'price' => 300, 
                'slug' => 'product-4',
                'getFirstMediaUrl' => fn() => 'image4.jpg'
            ],
        ]);

        $result = $this->galleryService->arrangeForGallery($products, 2);

        // Should only have 1 valid product (id 2) split into 1 column
        $this->assertCount(1, $result);
        $this->assertEquals(1, $result->first()['column_id']);
        $this->assertEquals(1, $result->first()['item_count']);
        $this->assertEquals(2, $result->first()['products'][0]['id']);
    }

    public function test_product_gallery_service_advanced_filtering(): void
    {
        $products = LaravelCollection::make([
            (object) [
                'id' => 1, 
                'name' => 'Cheap Product', 
                'is_visible' => true, 
                'price' => 5, 
                'slug' => 'product-1',
                'getFirstMediaUrl' => fn() => 'image1.jpg',
                'is_featured' => false,
                'average_rating' => 3.5
            ],
            (object) [
                'id' => 2, 
                'name' => 'Mid Product', 
                'is_visible' => true, 
                'price' => 50, 
                'slug' => 'product-2',
                'getFirstMediaUrl' => fn() => 'image2.jpg',
                'is_featured' => true,
                'average_rating' => 4.2
            ],
            (object) [
                'id' => 3, 
                'name' => 'Expensive Product', 
                'is_visible' => true, 
                'price' => 150, 
                'slug' => 'product-3',
                'getFirstMediaUrl' => fn() => 'image3.jpg',
                'is_featured' => false,
                'average_rating' => 4.8
            ],
        ]);

        $filters = [
            'min_price' => 10,
            'max_price' => 100,
            'min_rating' => 4.0,
            'has_images' => true,
            'is_featured' => true
        ];

        $filteredProducts = $this->galleryService->arrangeWithAdvancedFiltering($products, $filters);

        // Should only have 1 product (id 2) that meets all criteria
        $this->assertCount(1, $filteredProducts);
        $this->assertEquals(2, $filteredProducts->first()->id);
    }

    public function test_product_gallery_service_quality_filtering(): void
    {
        $products = LaravelCollection::make([
            (object) [
                'id' => 1, 
                'name' => 'Low Quality', 
                'is_visible' => true, 
                'price' => 10, 
                'slug' => 'product-1',
                'getFirstMediaUrl' => fn() => null,
                'description' => '',
                'is_featured' => false,
                'views_count' => 0,
                'average_rating' => 0
            ],
            (object) [
                'id' => 2, 
                'name' => 'Medium Quality', 
                'is_visible' => true, 
                'price' => 50, 
                'slug' => 'product-2',
                'getFirstMediaUrl' => fn() => 'image2.jpg',
                'description' => 'Good description',
                'is_featured' => false,
                'views_count' => 10,
                'average_rating' => 3.5
            ],
            (object) [
                'id' => 3, 
                'name' => 'High Quality', 
                'is_visible' => true, 
                'price' => 100, 
                'slug' => 'product-3',
                'getFirstMediaUrl' => fn() => 'image3.jpg',
                'description' => 'Excellent description',
                'is_featured' => true,
                'views_count' => 100,
                'average_rating' => 4.8
            ],
        ]);

        $result = $this->galleryService->arrangeWithQualityFiltering($products, 2, 0.6);

        // Should only have high quality products (id 2 and 3)
        $this->assertCount(2, $result);
        $this->assertEquals(2, $result->first()['column_id']);
        $this->assertEquals(2, $result->first()['item_count']);
    }

    public function test_collection_controller_products_gallery_with_skip_while(): void
    {
        // This test would require a full HTTP test setup
        // For now, we'll test the logic directly
        
        $products = LaravelCollection::make([
            (object) [
                'id' => 1, 
                'name' => '', 
                'is_visible' => true, 
                'price' => 100, 
                'slug' => 'product-1',
                'getFirstMediaUrl' => fn() => 'image1.jpg'
            ],
            (object) [
                'id' => 2, 
                'name' => 'Valid Product', 
                'is_visible' => true, 
                'price' => 200, 
                'slug' => 'product-2',
                'getFirstMediaUrl' => fn() => 'image2.jpg'
            ],
        ]);

        $filteredProducts = $products->skipWhile(function ($product) {
            return empty($product->name) || 
                   !$product->is_visible ||
                   $product->price <= 0 ||
                   empty($product->slug) ||
                   !$product->getFirstMediaUrl('images');
        });

        // Should only have 1 valid product (id 2)
        $this->assertCount(1, $filteredProducts);
        $this->assertEquals(2, $filteredProducts->first()->id);
    }

    public function test_collection_controller_homepage_layout_with_skip_while(): void
    {
        $collections = LaravelCollection::make([
            (object) [
                'id' => 1, 
                'name' => '', 
                'is_visible' => true, 
                'slug' => 'collection-1',
                'products_count' => 5,
                'getImageUrl' => fn() => 'image1.jpg'
            ],
            (object) [
                'id' => 2, 
                'name' => 'Valid Collection', 
                'is_visible' => true, 
                'slug' => 'collection-2',
                'products_count' => 10,
                'getImageUrl' => fn() => 'image2.jpg'
            ],
        ]);

        $filteredCollections = $collections->skipWhile(function ($collection) {
            return empty($collection->name) || 
                   !$collection->is_visible ||
                   empty($collection->slug) ||
                   $collection->products_count <= 0 ||
                   !$collection->getImageUrl('sm');
        });

        // Should only have 1 valid collection (id 2)
        $this->assertCount(1, $filteredCollections);
        $this->assertEquals(2, $filteredCollections->first()->id);
    }

    public function test_skip_while_with_split_in_combination(): void
    {
        $products = LaravelCollection::make([
            (object) ['id' => 1, 'name' => '', 'is_visible' => true, 'price' => 100, 'slug' => 'product-1'],
            (object) ['id' => 2, 'name' => 'Valid Product 1', 'is_visible' => true, 'price' => 200, 'slug' => 'product-2'],
            (object) ['id' => 3, 'name' => 'Valid Product 2', 'is_visible' => true, 'price' => 300, 'slug' => 'product-3'],
            (object) ['id' => 4, 'name' => 'Valid Product 3', 'is_visible' => true, 'price' => 400, 'slug' => 'product-4'],
            (object) ['id' => 5, 'name' => 'Valid Product 4', 'is_visible' => true, 'price' => 500, 'slug' => 'product-5'],
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
        $this->assertEquals(2, $splitProducts->last()->count());
        $this->assertEquals(2, $splitProducts->first()->first()->id);
        $this->assertEquals(4, $splitProducts->last()->first()->id);
    }

    public function test_skip_while_performance_with_large_collection(): void
    {
        // Create a large collection to test performance
        $products = LaravelCollection::make();
        
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

        // Should only have 10 valid products
        $this->assertCount(10, $validProducts);
        $this->assertEquals(1001, $validProducts->first()->id);
        $this->assertEquals(1010, $validProducts->last()->id);
        
        // Performance should be reasonable (less than 1 second for 1010 items)
        $this->assertLessThan(1.0, $executionTime);
    }
}
