<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\OptimizedProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('OptimizedProductService N+1 Prevention', function () {
    beforeEach(function () {
        $this->service = app(OptimizedProductService::class);

        // Create test data
        $this->brand = Brand::factory()->create();
        $this->category = Category::factory()->create();

        $this->products = Product::factory()
            ->count(10)
            ->for($this->brand)
            ->for($this->category)
            ->create();

        // Create variants for each product
        $this->products->each(function ($product) {
            ProductVariant::factory()
                ->count(3)
                ->for($product)
                ->create();
        });
    });

    it('prevents N+1 queries when fetching products with details', function () {
        // Enable query logging
        DB::enableQueryLog();

        $products = $this->service->getProductsWithDetails();

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // Should be around 3-4 queries regardless of number of products
        // 1. Main products query with eager loading
        // 2. Load brands
        // 3. Load categories
        // 4. Load variants and related data
        expect($queryCount)->toBeLessThanOrEqual(6);
        expect($products)->toHaveCount(10);

        // Verify all relationships are loaded without additional queries
        DB::flushQueryLog();

        foreach ($products as $product) {
            // These should not trigger additional queries
            $brand = $product->brand;
            $category = $product->category;
            $variants = $product->variants;
            $reviewsCount = $product->reviews_count;

            expect($brand)->not->toBeNull();
            expect($category)->not->toBeNull();
            expect($variants)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
        }

        $additionalQueries = DB::getQueryLog();
        expect($additionalQueries)->toBeEmpty(); // No additional queries should be executed
    });

    it('uses efficient subqueries for price calculations', function () {
        DB::enableQueryLog();

        $products = $this->service->getProductsWithDetails();

        // Check that price calculations are done in the main query
        $firstProduct = $products->first();
        expect($firstProduct->lowest_price)->not->toBeNull();
        expect($firstProduct->highest_price)->not->toBeNull();

        // Verify no additional queries for price calculations
        $queries = DB::getQueryLog();
        $priceQueries = collect($queries)->filter(function ($query) {
            return str_contains(strtolower($query['query']), 'min(price)') ||
                   str_contains(strtolower($query['query']), 'max(price)');
        });

        // Price calculations should be part of the main query, not separate queries
        expect($priceQueries)->toHaveCount(0);
    });

    it('efficiently handles category filtering without N+1', function () {
        DB::enableQueryLog();

        $products = $this->service->getProductsByCategory($this->category->id, 5);

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // Should be minimal queries:
        // 1. Category descendants lookup (cached)
        // 2. Main products query with pagination
        // 3. Eager loading relationships
        expect($queryCount)->toBeLessThanOrEqual(5);
        expect($products->items())->not->toBeEmpty();
    });

    it('performs bulk operations efficiently', function () {
        $variants = ProductVariant::limit(5)->get();
        $priceUpdates = $variants->mapWithKeys(function ($variant) {
            return [$variant->id => $variant->price * 1.1]; // 10% increase
        })->toArray();

        DB::enableQueryLog();

        $updated = $this->service->bulkUpdatePrices($priceUpdates);

        $queries = DB::getQueryLog();

        // Should use batch update, not individual updates
        $updateQueries = collect($queries)->filter(function ($query) {
            return str_starts_with(strtoupper(trim($query['query'])), 'UPDATE');
        });

        expect($updateQueries)->toHaveCount(1); // Single batch update
        expect($updated)->toBe(5);
    });

    it('measures query performance under load', function () {
        // Create more test data
        $additionalProducts = Product::factory()
            ->count(100)
            ->for($this->brand)
            ->for($this->category)
            ->create();

        $additionalProducts->each(function ($product) {
            ProductVariant::factory()
                ->count(2)
                ->for($product)
                ->create();
        });

        $startTime = microtime(true);
        DB::enableQueryLog();

        $products = $this->service->getProductsWithDetails();

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        $queries = DB::getQueryLog();

        // Performance assertions
        expect($executionTime)->toBeLessThan(500); // Should complete within 500ms
        expect(count($queries))->toBeLessThan(10); // Should not exceed 10 queries regardless of data size
        expect($products)->toHaveCount(110); // All products loaded

        // Memory usage should be reasonable
        $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024; // MB
        expect($memoryUsage)->toBeLessThan(50); // Should not exceed 50MB
    });

    it('validates cache effectiveness', function () {
        $productId = $this->products->first()->id;

        // First call - should hit database
        DB::enableQueryLog();
        $product1 = $this->service->getProductDetails($productId);
        $firstCallQueries = count(DB::getQueryLog());

        // Second call - should hit cache
        DB::flushQueryLog();
        $product2 = $this->service->getProductDetails($productId);
        $secondCallQueries = count(DB::getQueryLog());

        expect($firstCallQueries)->toBeGreaterThan(0);
        expect($secondCallQueries)->toBe(0); // No queries on cache hit
        expect($product1->id)->toBe($product2->id);
    });
});
