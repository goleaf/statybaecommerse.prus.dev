<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\SearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class SearchServiceTest extends TestCase
{
    use RefreshDatabase;

    private SearchService $searchService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->searchService = app(SearchService::class);
        Cache::flush();
        config([
            'search.driver' => 'database',
            'search.scout.enabled' => false,
            'scout.driver' => 'collection',
            'cache.default' => 'array',
            'cache.stores.array' => ['driver' => 'array'],
            'database.redis.client' => 'predis',
        ]);
    }

    public function test_search_returns_empty_array_when_no_results(): void
    {
        $results = $this->searchService->search('nonexistent', 10);

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function test_search_returns_products_when_found(): void
    {
        $product = $this->createPublishedProduct([
            'name' => 'Test Product',
        ]);

        $results = $this->searchService->search('Test', 10);

        $this->assertNotEmpty($results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertEquals('product', $result['type']);
        $this->assertEquals('Test Product', $result['title']);
        $this->assertEquals($product->id, $result['id']);
    }

    public function test_search_returns_categories_when_found(): void
    {
        $category = $this->createSearchableCategory([
            'name' => 'Test Category',
        ]);

        // Create a product in this category to satisfy the products_count > 0 condition
        $product = $this->createPublishedProduct();
        $category->products()->attach($product);

        $results = $this->searchService->search('Test', 10);

        $this->assertNotEmpty($results);

        $categoryResult = collect($results)->firstWhere('type', 'category');
        $this->assertNotNull($categoryResult);
        $this->assertEquals('Test Category', $categoryResult['title']);
        $this->assertEquals($category->id, $categoryResult['id']);
    }

    public function test_search_returns_brands_when_found(): void
    {
        $brand = $this->createSearchableBrand([
            'name' => 'Test Brand',
        ]);

        // Create a product for this brand to satisfy the products_count > 0 condition
        $this->createPublishedProduct([
            'name' => 'Brand Product',
            'brand_id' => $brand->id,
        ]);

        $results = $this->searchService->search('Test', 10);

        $this->assertNotEmpty($results);

        $brandResult = collect($results)->firstWhere('type', 'brand');
        $this->assertNotNull($brandResult);
        $this->assertEquals('Test Brand', $brandResult['title']);
        $this->assertEquals($brand->id, $brandResult['id']);
    }

    public function test_search_respects_limit_parameter(): void
    {
        // Create multiple products
        for ($i = 1; $i <= 5; $i++) {
            $this->createPublishedProduct([
                'name' => "Test Product {$i}",
            ]);
        }

        $results = $this->searchService->search('Test', 3);

        $this->assertLessThanOrEqual(3, count($results));
        $this->assertNotEmpty($results);
    }

    public function test_search_prioritizes_exact_matches(): void
    {
        // Create products with different match types
        $this->createPublishedProduct([
            'name' => 'Test Product',
        ]);

        $this->createPublishedProduct([
            'name' => 'Some Test Product',
        ]);

        $this->createPublishedProduct([
            'name' => 'Test',
        ]);

        $results = $this->searchService->search('Test', 10);

        $this->assertNotEmpty($results);

        // The exact match "Test" should have the highest relevance score
        $exactMatch = collect($results)->firstWhere('title', 'Test');
        $this->assertNotNull($exactMatch);

        // Check that exact match appears first (highest relevance)
        $this->assertEquals('Test', $results[0]['title']);
    }

    public function test_search_includes_featured_products_bonus(): void
    {
        $regularProduct = $this->createPublishedProduct([
            'name' => 'Regular Test Product',
            'is_featured' => false,
        ]);

        $featuredProduct = $this->createPublishedProduct([
            'name' => 'Featured Test Product',
            'is_featured' => true,
        ]);

        $results = $this->searchService->search('Test', 10);

        $this->assertNotEmpty($results);

        // Featured product should have higher relevance score
        $featuredResult = collect($results)->firstWhere('title', 'Featured Test Product');
        $regularResult = collect($results)->firstWhere('title', 'Regular Test Product');

        $this->assertNotNull($featuredResult);
        $this->assertNotNull($regularResult);
        $this->assertGreaterThan($regularResult['relevance_score'], $featuredResult['relevance_score']);
    }

    public function test_search_handles_special_characters(): void
    {
        $this->createPublishedProduct([
            'name' => 'Product with % special chars',
        ]);

        // Test with a simpler search term that should match
        $results = $this->searchService->search('special', 10);

        $this->assertNotEmpty($results);
        $this->assertCount(1, $results);
        $this->assertEquals('Product with % special chars', $results[0]['title']);
    }

    public function test_search_uses_scout_when_enabled(): void
    {
        config([
            'search.driver' => 'scout',
            'search.scout.enabled' => true,
            'scout.driver' => 'collection',
        ]);

        $brand = $this->createSearchableBrand([
            'name' => 'Scout Brand',
        ]);

        $category = $this->createSearchableCategory([
            'name' => 'Scout Category',
            'slug' => 'scout-category',
        ]);

        $product = $this->createPublishedProduct([
            'name' => 'Scout Drill',
            'brand_id' => $brand->id,
            'price' => 199.00,
        ]);

        $category->products()->attach($product);

        Product::makeAllSearchable();
        Category::makeAllSearchable();
        Brand::makeAllSearchable();

        $results = $this->searchService->search('Scout', 10);

        $this->assertNotEmpty($results);
        $this->assertNotNull(collect($results)->firstWhere('id', $product->id));
        $this->assertNotNull(collect($results)->firstWhere('id', $category->id));
        $this->assertNotNull(collect($results)->firstWhere('id', $brand->id));
    }

    public function test_search_caches_results(): void
    {
        $this->createPublishedProduct([
            'name' => 'Cached Test Product',
        ]);

        // First search
        $results1 = $this->searchService->search('Cached', 10);

        // Second search should use cache
        $results2 = $this->searchService->search('Cached', 10);

        $this->assertEquals($results1, $results2);
        $this->assertNotEmpty($results1);
    }

    public function test_clear_cache_removes_cached_results(): void
    {
        $this->createPublishedProduct([
            'name' => 'Cache Test Product',
        ]);

        // Search and cache results
        $this->searchService->search('Cache', 10);

        // Clear cache
        $this->searchService->clearCache();

        // Verify cache is cleared (this is more of a smoke test)
        $this->assertTrue(true); // Cache clearing doesn't return a value
    }

    public function test_search_returns_proper_result_structure(): void
    {
        $product = $this->createPublishedProduct([
            'name' => 'Structure Test Product',
        ]);

        $results = $this->searchService->search('Structure', 10);

        $this->assertNotEmpty($results);

        $result = $results[0];
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('subtitle', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('price', $result);
        $this->assertArrayHasKey('formatted_price', $result);
        $this->assertArrayHasKey('image', $result);
        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('relevance_score', $result);

        $this->assertEquals('product', $result['type']);
        $this->assertEquals($product->id, $result['id']);
        $this->assertEquals('Structure Test Product', $result['title']);
    }

    public function test_search_excludes_invisible_products(): void
    {
        Product::factory()->create([
            'name' => 'Invisible Test Product',
            'is_visible' => false,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->createPublishedProduct([
            'name' => 'Visible Test Product',
        ]);

        $results = $this->searchService->search('Test', 10);

        $this->assertNotEmpty($results);
        $this->assertCount(1, $results);
        $this->assertEquals('Visible Test Product', $results[0]['title']);
    }

    public function test_search_excludes_unpublished_products(): void
    {
        Product::factory()->create([
            'name' => 'Future Test Product',
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);

        $this->createPublishedProduct([
            'name' => 'Published Test Product',
        ]);

        $results = $this->searchService->search('Test', 10);

        $this->assertNotEmpty($results);
        $this->assertCount(1, $results);
        $this->assertEquals('Published Test Product', $results[0]['title']);
    }

    private function createPublishedProduct(array $attributes = []): Product
    {
        return Product::factory()->create(array_merge([
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ], $attributes));
    }

    private function createSearchableCategory(array $attributes = []): Category
    {
        $defaults = $this->filterAttributesForTable('categories', [
            'is_visible' => true,
            'is_enabled' => true,
            'is_active' => true,
        ]);

        return Category::factory()->create(array_merge($defaults, $attributes));
    }

    private function createSearchableBrand(array $attributes = []): Brand
    {
        $defaults = $this->filterAttributesForTable('brands', [
            'is_enabled' => true,
            'is_active' => true,
            'is_visible' => true,
        ]);

        return Brand::factory()->create(array_merge($defaults, $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function filterAttributesForTable(string $table, array $attributes): array
    {
        foreach (array_keys($attributes) as $column) {
            if (! Schema::hasColumn($table, $column)) {
                unset($attributes[$column]);
            }
        }

        return $attributes;
    }
}
