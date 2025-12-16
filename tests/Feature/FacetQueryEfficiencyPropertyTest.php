<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Services\FacetCountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use RuntimeException;
use Tests\TestCase;

/**
 * Property Test 5: Facet Query Efficiency
 * Validates: Requirements 4.1, 4.2
 */
final class FacetQueryEfficiencyPropertyTest extends TestCase
{
    use RefreshDatabase;

    public function test_facet_counting_uses_bounded_queries(): void
    {
        // Property: Facet counting should execute ≤ 6 queries per request

        // Create minimal test data using factories
        $brand = Brand::factory()->create(['is_enabled' => true]);
        $category = Category::factory()->create(['is_visible' => true]);
        $collection = Collection::factory()->create(['is_visible' => true]);

        $product = Product::factory()->create([
            'brand_id'     => $brand->id,
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        // Associate with categories and collections
        $product->categories()->attach($category->id);
        $product->collections()->attach($collection->id);

        $facetService = app(FacetCountingService::class);
        $baseQuery = Product::query()->withoutGlobalScopes()->where('is_visible', true);

        // Clear query log and track queries
        DB::flushQueryLog();
        DB::enableQueryLog();

        // Execute facet counting
        $facetService->resetQueryCount();
        $brandFacets = $facetService->getBrandFacets($baseQuery);
        $collectionFacets = $facetService->getCollectionFacets($baseQuery);
        $categoryFacets = $facetService->getCategoryFacets($baseQuery);

        $queries = DB::getQueryLog();
        $actualQueries = count($queries);

        // Debug: Show actual queries if test fails
        if ($actualQueries > 6) {
            $queryList = collect($queries)->map(fn ($q) => $q['query'])->join("\n");
            $this->fail("Expected ≤ 6 queries but got {$actualQueries}:\n{$queryList}");
        }

        // Property: Should execute ≤ 6 queries per request (2 per facet type: count + details)
        $this->assertLessThanOrEqual(6, $actualQueries, 'Facet counting should use ≤ 6 queries per request');

        // Property: Service should track query count correctly
        $this->assertLessThanOrEqual(6, $facetService->getQueryCount(), 'Service query count should be ≤ 6');

        // Property: Should return valid facet data
        $this->assertIsArray($brandFacets);
        $this->assertIsArray($collectionFacets);
        $this->assertIsArray($categoryFacets);

        // Property: Each facet should have required structure
        foreach ($brandFacets as $facet) {
            $this->assertArrayHasKey('id', $facet);
            $this->assertArrayHasKey('name', $facet);
            $this->assertArrayHasKey('count', $facet);
            $this->assertIsInt($facet['id']);
            $this->assertIsString($facet['name']);
            $this->assertIsInt($facet['count']);
        }
    }

    public function test_facet_counting_prevents_n_plus_one_patterns(): void
    {
        // Property: Adding more entities should not increase query count linearly

        // Create varying amounts of test data
        $smallDataset = 5;
        $largeDataset = 50;

        // Test with small dataset
        Brand::factory()->count($smallDataset)->create(['is_enabled' => true]);
        Category::factory()->count($smallDataset)->create(['is_visible' => true]);
        Collection::factory()->count($smallDataset)->create(['is_visible' => true]);

        $facetService = app(FacetCountingService::class);
        $baseQuery = Product::query()->visible();

        DB::enableQueryLog();
        $facetService->resetQueryCount();
        $facetService->getBrandFacets($baseQuery);
        $smallDatasetQueries = $facetService->getQueryCount();

        // Clear and test with large dataset
        DB::flushQueryLog();
        Brand::factory()->count($largeDataset - $smallDataset)->create(['is_enabled' => true]);
        Category::factory()->count($largeDataset - $smallDataset)->create(['is_visible' => true]);
        Collection::factory()->count($largeDataset - $smallDataset)->create(['is_visible' => true]);

        $facetService->resetQueryCount();
        $facetService->getBrandFacets($baseQuery);
        $largeDatasetQueries = $facetService->getQueryCount();

        // Property: Query count should remain constant regardless of data size
        $this->assertEquals(
            $smallDatasetQueries,
            $largeDatasetQueries,
            'Query count should not increase with dataset size (prevents N+1 patterns)'
        );
    }

    public function test_facet_service_enforces_query_budget(): void
    {
        // Property: Service should prevent excessive queries

        $facetService = app(FacetCountingService::class);

        // Simulate exceeding query budget
        $facetService->resetQueryCount();

        // Use reflection to set query count to maximum
        $reflection = new ReflectionClass($facetService);
        $queryCountProperty = $reflection->getProperty('queryCount');
        $queryCountProperty->setAccessible(true);
        $queryCountProperty->setValue($facetService, 6); // Set to max (6)

        $baseQuery = Product::query()->withoutGlobalScopes()->where('is_visible', true);

        // Property: Should throw exception when budget exceeded
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Query budget exceeded');

        $facetService->getBrandFacets($baseQuery);
    }

    public function test_all_facets_method_efficiency(): void
    {
        // Property: getAllFacets should be more efficient than individual calls

        // Create test data
        Brand::factory()->count(5)->create(['is_enabled' => true]);
        Category::factory()->count(5)->create(['is_visible' => true]);
        Collection::factory()->count(5)->create(['is_visible' => true]);

        $facetService = app(FacetCountingService::class);
        $baseQuery = Product::query()->visible();

        // Test individual calls
        DB::enableQueryLog();
        $facetService->resetQueryCount();
        $facetService->getBrandFacets($baseQuery);
        $facetService->getCollectionFacets($baseQuery);
        $facetService->getCategoryFacets($baseQuery);
        $individualCallQueries = $facetService->getQueryCount();

        // Test combined call
        DB::flushQueryLog();
        $facetService->resetQueryCount();
        $allFacets = $facetService->getAllFacets($baseQuery);
        $combinedCallQueries = $facetService->getQueryCount();

        // Property: Combined call should use same or fewer queries
        $this->assertLessThanOrEqual(
            $individualCallQueries,
            $combinedCallQueries,
            'getAllFacets should be as efficient as individual calls'
        );

        // Property: Should return all facet types
        $this->assertArrayHasKey('brands', $allFacets);
        $this->assertArrayHasKey('collections', $allFacets);
        $this->assertArrayHasKey('categories', $allFacets);
    }

    public function test_facet_counts_accuracy(): void
    {
        // Property: Facet counts should be accurate

        // Create test data with known relationships
        $brand = Brand::factory()->create(['is_enabled' => true]);
        $category = Category::factory()->create(['is_visible' => true]);
        $collection = Collection::factory()->create(['is_visible' => true]);

        $products = Product::factory()->count(3)->create([
            'brand_id'     => $brand->id,
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        foreach ($products as $product) {
            $product->categories()->attach($category->id);
            $product->collections()->attach($collection->id);
        }

        $facetService = app(FacetCountingService::class);
        $baseQuery = Product::query()->visible()->whereNotNull('published_at');

        $brandFacets = $facetService->getBrandFacets($baseQuery);
        $categoryFacets = $facetService->getCategoryFacets($baseQuery);
        $collectionFacets = $facetService->getCollectionFacets($baseQuery);

        // Property: Counts should match actual product relationships
        $brandFacet = collect($brandFacets)->firstWhere('id', $brand->id);
        $categoryFacet = collect($categoryFacets)->firstWhere('id', $category->id);
        $collectionFacet = collect($collectionFacets)->firstWhere('id', $collection->id);

        $this->assertNotNull($brandFacet);
        $this->assertNotNull($categoryFacet);
        $this->assertNotNull($collectionFacet);

        $this->assertEquals(3, $brandFacet['count'], 'Brand facet count should match product count');
        $this->assertEquals(3, $categoryFacet['count'], 'Category facet count should match product count');
        $this->assertEquals(3, $collectionFacet['count'], 'Collection facet count should match product count');
    }
}
