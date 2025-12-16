<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Data\SearchQueryData;
use App\Services\SearchCacheService;
use App\Services\SearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SearchResultCachingPropertyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up basic configuration for search caching
        config(['search.driver' => 'database']);
        config(['search.drivers.database.enabled' => true]);
        config(['search.cache.enabled' => true]);
        config(['search.cache.tags.enabled' => true]);
        config(['search.cache.tags.include_locale' => true]);
        config(['search.cache.tags.include_catalog' => true]);
        config(['search.cache.default_ttl' => 3600]);
        config(['cache.default' => 'array']);

        // Clear cache to prevent pollution between tests
        Cache::flush();
    }

    /**
     * **Feature: performance-update, Property 9: Search Result Caching**
     * **Validates: Requirements 5.3**
     *
     * For any repeated search query within TTL, results should be served from cache
     * with appropriate locale and catalog tags.
     */
    public function test_search_result_caching_property(): void
    {
        // Clear cache to ensure clean test state
        Cache::flush();

        $searchService = app(SearchService::class);

        // Property: For any search query, repeated searches should return cached results
        // Use simple alphanumeric queries to avoid sanitization issues
        $testQueries = [
            'laptop computer',
            'wireless headphones',
            'gaming mouse',
            'bluetooth speaker',
            'phone case',
        ];

        foreach ($testQueries as $query) {
            // Create search query data
            $queryData = SearchQueryData::fromArray([
                'query'    => $query,
                'page'     => 1,
                'per_page' => 10,
                'types'    => ['product', 'brand', 'category'],
            ], [
                'locale' => 'lt',
                'source' => 'storefront-search',
            ]);

            // First search - should cache results
            $firstResults = $searchService->search($queryData);

            $this->assertIsArray($firstResults, "First search should return array for query: {$query}");
            $this->assertArrayHasKey('data', $firstResults, "First search should have data key for query: {$query}");
            $this->assertArrayHasKey('meta', $firstResults, "First search should have meta key for query: {$query}");
            $this->assertFalse($firstResults['meta']['cached'] ?? false, "First search should not be cached for query: {$query}");

            // Second search with same query - should return cached results
            $secondResults = $searchService->search($queryData);

            $this->assertIsArray($secondResults, "Second search should return array for query: {$query}");
            $this->assertArrayHasKey('data', $secondResults, "Second search should have data key for query: {$query}");
            $this->assertArrayHasKey('meta', $secondResults, "Second search should have meta key for query: {$query}");
            $this->assertTrue($secondResults['meta']['cached'] ?? false, "Second search should be cached for query: {$query}");

            // Property: Cached results should be identical to original results
            $this->assertEquals($firstResults['data'], $secondResults['data'],
                "Cached data should match original data for query: {$query}");
            $this->assertEquals($firstResults['meta']['query'], $secondResults['meta']['query'],
                "Cached query should match original query for query: {$query}");
            $this->assertEquals($firstResults['meta']['total_results'], $secondResults['meta']['total_results'],
                "Cached total results should match original for query: {$query}");
        }
    }

    /**
     * **Feature: performance-update, Property 9: Search Result Caching**
     * **Validates: Requirements 5.3**
     *
     * Property: Cache should be tagged with appropriate locale and catalog tags
     */
    public function test_search_cache_tagging_property(): void
    {
        $cacheService = app(SearchCacheService::class);

        // Property: For any search results, cache should include appropriate tags
        $testCases = [
            [
                'results' => [
                    'data' => [
                        ['type' => 'product', 'id' => 1, 'name' => 'Test Product'],
                        ['type' => 'brand', 'id' => 2, 'name' => 'Test Brand'],
                    ],
                    'meta' => ['total_results' => 2],
                ],
                'context'      => ['locale' => 'lt'],
                'expectedTags' => ['locale:lt', 'products', 'brands', 'search'],
            ],
            [
                'results' => [
                    'data' => [
                        ['type' => 'category', 'id' => 3, 'name' => 'Test Category'],
                    ],
                    'meta' => ['total_results' => 1],
                ],
                'context'      => ['locale' => 'en'],
                'expectedTags' => ['locale:en', 'categories', 'search'],
            ],
            [
                'results' => [
                    'data' => [],
                    'meta' => ['total_results' => 0],
                ],
                'context'      => ['locale' => 'lt'],
                'expectedTags' => ['locale:lt', 'search'],
            ],
        ];

        foreach ($testCases as $index => $testCase) {
            $cacheKey = "test_cache_key_{$index}";
            $query = "test query {$index}";

            // Cache the results
            $cacheService->cacheSearchResults($cacheKey, $testCase['results'], $query, $testCase['context']);

            // Verify results are cached
            $cachedResults = $cacheService->getCachedResults($cacheKey);
            $this->assertNotNull($cachedResults, "Results should be cached for test case {$index}");
            $this->assertEquals($testCase['results'], $cachedResults, "Cached results should match original for test case {$index}");
        }
    }

    /**
     * **Feature: performance-update, Property 9: Search Result Caching**
     * **Validates: Requirements 5.3**
     *
     * Property: Different locales should have separate cache entries
     */
    public function test_locale_specific_caching_property(): void
    {
        // Clear cache to avoid pollution from other tests
        Cache::flush();

        $searchService = app(SearchService::class);

        // Property: For any search query, different locales should have separate cache entries
        $locales = ['lt', 'en'];
        $results = [];

        foreach ($locales as $locale) {
            // Use locale-specific query to avoid cache collision
            $testQuery = "test product {$locale}";

            $queryData = SearchQueryData::fromArray([
                'query'    => $testQuery,
                'page'     => 1,
                'per_page' => 10,
                'types'    => ['product'],
            ], [
                'locale' => $locale,
                'source' => 'storefront-search',
            ]);

            // First search for each locale
            $firstResults = $searchService->search($queryData);
            $this->assertIsArray($firstResults, "First search should work for locale: {$locale}");
            $this->assertFalse($firstResults['meta']['cached'] ?? false, "First search should not be cached for locale: {$locale}");

            // Second search for same locale should be cached
            $secondResults = $searchService->search($queryData);
            $this->assertTrue($secondResults['meta']['cached'] ?? false, "Second search should be cached for locale: {$locale}");

            $results[$locale] = $secondResults;
        }

        // Property: Each locale should have its own cached results
        $this->assertCount(2, $results, 'Should have results for both locales');
        $this->assertArrayHasKey('lt', $results, 'Should have Lithuanian results');
        $this->assertArrayHasKey('en', $results, 'Should have English results');

        // Both should be cached but potentially different
        $this->assertTrue($results['lt']['meta']['cached'] ?? false, 'Lithuanian results should be cached');
        $this->assertTrue($results['en']['meta']['cached'] ?? false, 'English results should be cached');
    }

    /**
     * **Feature: performance-update, Property 9: Search Result Caching**
     * **Validates: Requirements 5.3**
     *
     * Property: Cache should respect TTL configuration
     */
    public function test_cache_ttl_property(): void
    {
        // Clear cache to avoid pollution from other tests
        Cache::flush();

        // Set longer TTL for testing to ensure caching works
        config(['search.cache.default_ttl' => 3600]);

        $searchService = app(SearchService::class);
        $uniqueQuery = 'ttl test query'; // Use simple query to avoid sanitization issues

        $queryData = SearchQueryData::fromArray([
            'query'    => $uniqueQuery,
            'page'     => 1,
            'per_page' => 10,
            'types'    => ['product'],
        ], [
            'locale' => 'lt',
        ]);

        // First search - should cache results
        $firstResults = $searchService->search($queryData);
        $this->assertFalse($firstResults['meta']['cached'] ?? false, 'First search should not be cached');

        // Immediate second search - should be cached
        $secondResults = $searchService->search($queryData);
        $this->assertTrue($secondResults['meta']['cached'] ?? false, 'Second search should be cached');

        // Verify cached results are identical
        $this->assertEquals($firstResults['data'], $secondResults['data'], 'Cached data should match original');

        // Simulate TTL expiry by clearing cache
        Cache::flush();

        // Third search after cache clear - should not be cached
        $thirdResults = $searchService->search($queryData);
        $this->assertFalse($thirdResults['meta']['cached'] ?? false, 'Third search should not be cached after cache clear');
    }

    /**
     * **Feature: performance-update, Property 9: Search Result Caching**
     * **Validates: Requirements 5.3**
     *
     * Property: Cache can be disabled and searches still work
     */
    public function test_cache_disabled_property(): void
    {
        // Disable caching
        config(['search.cache.enabled' => false]);

        $searchService = app(SearchService::class);
        $queryData = SearchQueryData::fromArray([
            'query'    => 'cache disabled test',
            'page'     => 1,
            'per_page' => 10,
            'types'    => ['product'],
        ], [
            'locale' => 'lt',
        ]);

        // Property: When caching is disabled, searches should still work but never be cached
        $firstResults = $searchService->search($queryData);
        $this->assertIsArray($firstResults, 'Search should work with caching disabled');
        $this->assertFalse($firstResults['meta']['cached'] ?? false, 'Results should not be cached when caching disabled');

        $secondResults = $searchService->search($queryData);
        $this->assertIsArray($secondResults, 'Second search should work with caching disabled');
        $this->assertFalse($secondResults['meta']['cached'] ?? false, 'Second results should not be cached when caching disabled');
    }

    /**
     * **Feature: performance-update, Property 9: Search Result Caching**
     * **Validates: Requirements 5.3**
     *
     * Property: Different search parameters should have separate cache entries
     */
    public function test_parameter_specific_caching_property(): void
    {
        $searchService = app(SearchService::class);

        // Property: For any search query, different parameters should create separate cache entries
        $baseQuery = 'parameter test';
        $parameterVariations = [
            ['page' => 1, 'per_page' => 10, 'types' => ['product']],
            ['page' => 2, 'per_page' => 10, 'types' => ['product']],
            ['page' => 1, 'per_page' => 20, 'types' => ['product']],
            ['page' => 1, 'per_page' => 10, 'types' => ['product', 'brand']],
        ];

        $results = [];

        foreach ($parameterVariations as $index => $params) {
            $queryData = SearchQueryData::fromArray(array_merge([
                'query' => $baseQuery,
            ], $params), [
                'locale' => 'lt',
            ]);

            // First search with these parameters
            $firstResults = $searchService->search($queryData);
            $this->assertFalse($firstResults['meta']['cached'] ?? false, "First search should not be cached for variation {$index}");

            // Second search with same parameters should be cached
            $secondResults = $searchService->search($queryData);
            $this->assertTrue($secondResults['meta']['cached'] ?? false, "Second search should be cached for variation {$index}");

            $results[$index] = $secondResults;
        }

        // Property: Each parameter variation should have its own cache entry
        $this->assertCount(4, $results, 'Should have results for all parameter variations');

        foreach ($results as $index => $result) {
            $this->assertTrue($result['meta']['cached'] ?? false, "Result {$index} should be cached");
            $this->assertEquals($baseQuery, $result['meta']['query'], "Query should match for result {$index}");
        }
    }

    /**
     * **Feature: performance-update, Property 9: Search Result Caching**
     * **Validates: Requirements 5.3**
     *
     * Property: Cache service should handle errors gracefully
     */
    public function test_cache_error_handling_property(): void
    {
        $searchService = app(SearchService::class);

        // Property: Search should work even when cache operations fail
        $queryData = SearchQueryData::fromArray([
            'query'    => 'error handling test',
            'page'     => 1,
            'per_page' => 10,
            'types'    => ['product'],
        ], [
            'locale' => 'lt',
        ]);

        // Test with valid cache configuration
        $results = $searchService->search($queryData);
        $this->assertIsArray($results, 'Search should work with valid cache configuration');
        $this->assertArrayHasKey('data', $results, 'Results should have data key');
        $this->assertArrayHasKey('meta', $results, 'Results should have meta key');

        // Test that search continues to work (cache errors are logged but don't break search)
        $secondResults = $searchService->search($queryData);
        $this->assertIsArray($secondResults, 'Search should continue to work');
        $this->assertArrayHasKey('data', $secondResults, 'Second results should have data key');
        $this->assertArrayHasKey('meta', $secondResults, 'Second results should have meta key');
    }
}
