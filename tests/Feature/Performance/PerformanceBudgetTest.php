<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * **Feature: performance-update, Performance Budget Enforcement**
 * **Validates: Requirements 10.1, 10.2**
 *
 * Ensures that key storefront pages stay within defined performance budgets
 * for query count, memory usage, and response time.
 */
final class PerformanceBudgetTest extends TestCase
{
    use RefreshDatabase;

    private array $queryBudgets;
    private array $memoryBudgets;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->queryBudgets = config('performance.monitoring.query_budgets', [
            'home' => 15,
            'category' => 20,
            'product' => 25,
            'search' => 10,
        ]);
        
        $this->memoryBudgets = config('performance.monitoring.memory_budgets', [
            'home' => 64,
            'category' => 96,
            'product' => 128,
            'search' => 48,
        ]);

        $this->seedTestData();
    }

    public function test_home_page_stays_within_query_budget(): void
    {
        Cache::flush();
        
        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $response = $this->get('/lt');
        
        $response->assertOk();
        expect($queryCount)->toBeLessThanOrEqual($this->queryBudgets['home'])
            ->and($queryCount)->toBeGreaterThan(0, 'Should execute some queries on cold cache');
    }

    public function test_home_page_zero_queries_on_warm_cache(): void
    {
        // Warm the cache first
        $this->get('/lt');
        
        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $response = $this->get('/lt');
        
        $response->assertOk();
        expect($queryCount)->toBe(0, 'Warm cache should require zero database queries');
    }

    public function test_category_page_stays_within_query_budget(): void
    {
        Cache::flush();
        
        $category = Category::factory()->create(['slug' => 'test-category']);
        
        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $response = $this->get("/lt/categories/{$category->slug}");
        
        $response->assertOk();
        expect($queryCount)->toBeLessThanOrEqual($this->queryBudgets['category']);
    }

    public function test_product_page_stays_within_query_budget(): void
    {
        Cache::flush();
        
        $product = Product::factory()->create(['slug' => 'test-product']);
        
        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $response = $this->get("/lt/products/{$product->slug}");
        
        $response->assertOk();
        expect($queryCount)->toBeLessThanOrEqual($this->queryBudgets['product']);
    }

    public function test_search_page_stays_within_query_budget(): void
    {
        Cache::flush();
        
        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $response = $this->get('/lt/search?q=test');
        
        $response->assertOk();
        expect($queryCount)->toBeLessThanOrEqual($this->queryBudgets['search']);
    }

    public function test_home_page_memory_usage_within_budget(): void
    {
        $initialMemory = memory_get_usage(true);
        
        $response = $this->get('/lt');
        
        $peakMemory = memory_get_peak_usage(true);
        $memoryUsedMB = ($peakMemory - $initialMemory) / 1024 / 1024;
        
        $response->assertOk();
        expect($memoryUsedMB)->toBeLessThanOrEqual($this->memoryBudgets['home']);
    }

    public function test_facet_counting_efficiency(): void
    {
        Cache::flush();
        
        // Create test data that would trigger N+1 without optimization
        Brand::factory()->count(10)->create();
        Collection::factory()->count(8)->create();
        Category::factory()->count(12)->create();
        
        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $response = $this->get('/lt/categories');
        
        $response->assertOk();
        
        // Should use aggregated queries, not N+1 pattern
        expect($queryCount)->toBeLessThanOrEqual(5, 'Facet counting should use ≤5 queries per requirements');
    }

    public function test_eager_loading_prevents_n_plus_one(): void
    {
        Cache::flush();
        
        // Create products with relations that could trigger N+1
        $products = Product::factory()->count(5)->create();
        foreach ($products as $product) {
            $product->brand()->associate(Brand::factory()->create());
            $product->categories()->attach(Category::factory()->create());
            $product->save();
        }
        
        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $response = $this->get('/lt/products');
        
        $response->assertOk();
        
        // Should not have N+1 queries for relations
        expect($queryCount)->toBeLessThan(20, 'Should not have N+1 queries for product relations');
    }

    private function seedTestData(): void
    {
        // Create minimal test data for performance tests
        Brand::factory()->count(3)->create();
        Category::factory()->count(3)->create();
        Collection::factory()->count(2)->create();
        Product::factory()->count(5)->create();
    }
}