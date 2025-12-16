<?php

declare(strict_types=1);

namespace Tests\Performance;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * **Feature: performance-update, Property 11: Eager Loading Completeness**
 * **Validates: Requirements 6.2**
 *
 * Property-based test to ensure that when iterating over product lists,
 * no lazy-loading queries are triggered for relations (brand, media, categories, translations).
 */
final class EagerLoadingCompletenessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that withListRelations scope prevents lazy loading during product list iteration.
     */
    public function test_with_list_relations_prevents_lazy_loading(): void
    {
        // Set application locale for consistent behavior
        app()->setLocale('en');

        // Create test data with relations
        $brand = Brand::factory()->create(['name' => 'Test Brand']);
        $category = Category::factory()->create(['name' => 'Test Category', 'is_visible' => true]);

        $products = Product::factory()->count(5)->create([
            'brand_id'     => $brand->id,
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        // Associate products with categories
        foreach ($products as $product) {
            $product->categories()->attach($category->id);
        }

        // Create translations for all entities
        foreach ($products as $product) {
            $product->translations()->create([
                'locale'            => 'en',
                'name'              => "Product {$product->id} EN",
                'slug'              => "product-{$product->id}-en",
                'short_description' => "Short description for product {$product->id}",
            ]);
        }

        $brand->translations()->create([
            'locale' => 'en',
            'name'   => 'Test Brand EN',
            'slug'   => 'test-brand-en',
        ]);

        $category->translations()->create([
            'locale' => 'en',
            'name'   => 'Test Category EN',
            'slug'   => 'test-category-en',
        ]);

        // Load products with proper eager loading
        $loadedProducts = Product::query()
            ->forProductList()
            ->withListRelations()
            ->get();

        $this->assertCount(5, $loadedProducts);

        // Verify all expected relations are loaded
        foreach ($loadedProducts as $product) {
            $this->assertTrue($product->relationLoaded('brand'), 'Brand relation should be loaded');
            $this->assertTrue($product->relationLoaded('categories'), 'Categories relation should be loaded');
            $this->assertTrue($product->relationLoaded('translations'), 'Translations relation should be loaded');
            $this->assertTrue($product->relationLoaded('media'), 'Media relation should be loaded');
        }

        // Enable query logging to detect lazy loading
        DB::enableQueryLog();

        // Iterate through products and access all relations - this should NOT trigger any queries
        foreach ($loadedProducts as $product) {
            // Access brand relation
            $brandName = $product->brand?->name;
            $brandTranslatedName = $product->brand?->trans('name', 'en');

            // Access categories relation
            $categoryNames = $product->categories->pluck('name')->toArray();
            foreach ($product->categories as $category) {
                $categoryTranslatedName = $category->trans('name', 'en');
            }

            // Access translations relation
            $productTranslatedName = $product->trans('name', 'en');
            $productTranslatedSlug = $product->trans('slug', 'en');
            $productShortDesc = $product->trans('short_description', 'en');

            // Access media relation
            $mediaCount = $product->media->count();
            foreach ($product->media as $mediaItem) {
                $mediaName = $mediaItem->name;
            }
        }

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Filter out schema introspection queries (these are not lazy loading)
        $actualQueries = array_filter($queries, function ($query) {
            $sql = strtolower($query['query']);

            return ! str_contains($sql, 'pragma_table_xinfo') &&
                   ! str_contains($sql, 'sqlite_master') &&
                   ! str_contains($sql, 'information_schema') &&
                   ! str_contains($sql, 'show columns') &&
                   ! str_contains($sql, 'show tables');
        });

        // Property: No lazy loading queries should be triggered during iteration
        $this->assertEmpty($actualQueries,
            'Product list iteration triggered lazy loading queries: ' .
            json_encode(array_column($actualQueries, 'query'), JSON_PRETTY_PRINT)
        );
    }

    /**
     * Test that accessing nested relation translations doesn't trigger lazy loading.
     */
    public function test_nested_relation_translations_prevent_lazy_loading(): void
    {
        // Set application locale
        app()->setLocale('en');

        // Create test data
        $brand = Brand::factory()->create(['name' => 'Test Brand']);
        $category = Category::factory()->create(['name' => 'Test Category', 'is_visible' => true]);

        $product = Product::factory()->create([
            'brand_id'     => $brand->id,
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        $product->categories()->attach($category->id);

        // Create translations
        $product->translations()->create([
            'locale'            => 'en',
            'name'              => 'Product EN',
            'slug'              => 'product-en',
            'short_description' => 'Short description EN',
        ]);

        $brand->translations()->create([
            'locale' => 'en',
            'name'   => 'Brand EN',
            'slug'   => 'brand-en',
        ]);

        $category->translations()->create([
            'locale' => 'en',
            'name'   => 'Category EN',
            'slug'   => 'category-en',
        ]);

        // Load product with eager loading
        $loadedProduct = Product::query()
            ->forProductList()
            ->withListRelations()
            ->find($product->id);

        $this->assertNotNull($loadedProduct);

        // Verify nested relations are loaded
        $this->assertTrue($loadedProduct->relationLoaded('brand'));
        $this->assertTrue($loadedProduct->relationLoaded('categories'));
        if ($loadedProduct->brand) {
            $this->assertTrue($loadedProduct->brand->relationLoaded('translations'));
        }
        foreach ($loadedProduct->categories as $category) {
            $this->assertTrue($category->relationLoaded('translations'));
        }

        // Enable query logging
        DB::enableQueryLog();

        // Access nested relation translations - should not trigger lazy loading
        $brandTranslatedName = $loadedProduct->brand?->trans('name', 'en');
        $brandTranslatedSlug = $loadedProduct->brand?->trans('slug', 'en');

        foreach ($loadedProduct->categories as $category) {
            $categoryTranslatedName = $category->trans('name', 'en');
            $categoryTranslatedSlug = $category->trans('slug', 'en');
        }

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Filter out schema introspection queries
        $actualQueries = array_filter($queries, function ($query) {
            $sql = strtolower($query['query']);

            return ! str_contains($sql, 'pragma_table_xinfo') &&
                   ! str_contains($sql, 'sqlite_master') &&
                   ! str_contains($sql, 'information_schema');
        });

        // Property: Accessing nested relation translations should not trigger lazy loading
        $this->assertEmpty($actualQueries,
            'Accessing nested relation translations triggered lazy loading: ' .
            json_encode(array_column($actualQueries, 'query'), JSON_PRETTY_PRINT)
        );
    }

    /**
     * Test that different product list query patterns all prevent lazy loading.
     */
    public function test_various_product_list_patterns_prevent_lazy_loading(): void
    {
        // Set application locale
        app()->setLocale('en');

        // Create test data
        $brand = Brand::factory()->create(['name' => 'Test Brand']);
        $category = Category::factory()->create(['name' => 'Test Category', 'is_visible' => true]);

        $products = Product::factory()->count(10)->create([
            'brand_id'     => $brand->id,
            'is_visible'   => true,
            'is_featured'  => true,
            'published_at' => now()->subDay(),
        ]);

        foreach ($products as $product) {
            $product->categories()->attach($category->id);
            $product->translations()->create([
                'locale'            => 'en',
                'name'              => "Product {$product->id} EN",
                'slug'              => "product-{$product->id}-en",
                'short_description' => "Short description {$product->id}",
            ]);
        }

        $brand->translations()->create([
            'locale' => 'en',
            'name'   => 'Brand EN',
            'slug'   => 'brand-en',
        ]);

        $category->translations()->create([
            'locale' => 'en',
            'name'   => 'Category EN',
            'slug'   => 'category-en',
        ]);

        // Test different query patterns that should all prevent lazy loading
        $queryPatterns = [
            'basic_list'        => fn () => Product::query()->forProductList()->withListRelations()->limit(5)->get(),
            'category_products' => fn () => $category->products()->forProductList()->withListRelations()->limit(5)->get(),
            'brand_products'    => fn () => $brand->products()->forProductList()->withListRelations()->limit(5)->get(),
            'featured_products' => fn () => Product::query()->forProductList()->withListRelations()->where('is_featured', true)->limit(5)->get(),
        ];

        foreach ($queryPatterns as $patternName => $queryCallback) {
            // Execute the query
            $results = $queryCallback();
            $this->assertNotEmpty($results, "Query pattern '{$patternName}' should return results");

            // Enable query logging
            DB::enableQueryLog();

            // Iterate through results and access all relations
            foreach ($results as $product) {
                // Access all relations that should be eager loaded
                $brandName = $product->brand?->name;
                $brandTransName = $product->brand?->trans('name', 'en');

                $categoryNames = $product->categories->pluck('name')->toArray();
                foreach ($product->categories as $cat) {
                    $catTransName = $cat->trans('name', 'en');
                }

                $productTransName = $product->trans('name', 'en');
                $productShortDesc = $product->trans('short_description', 'en');

                $mediaCount = $product->media->count();
            }

            $queries = DB::getQueryLog();
            DB::disableQueryLog();

            // Filter out schema introspection queries
            $actualQueries = array_filter($queries, function ($query) {
                $sql = strtolower($query['query']);

                return ! str_contains($sql, 'pragma_table_xinfo') &&
                       ! str_contains($sql, 'sqlite_master') &&
                       ! str_contains($sql, 'information_schema');
            });

            // Property: Each query pattern should prevent lazy loading during iteration
            $this->assertEmpty($actualQueries,
                "Query pattern '{$patternName}' triggered lazy loading queries: " .
                json_encode(array_column($actualQueries, 'query'), JSON_PRETTY_PRINT)
            );
        }
    }

    /**
     * Test that eager loading works correctly with aggregated queries (withCount, withAvg).
     */
    public function test_eager_loading_with_aggregates_prevents_lazy_loading(): void
    {
        // Set application locale
        app()->setLocale('en');

        // Create test data
        $brand = Brand::factory()->create(['name' => 'Test Brand']);
        $category = Category::factory()->create(['name' => 'Test Category', 'is_visible' => true]);

        $product = Product::factory()->create([
            'brand_id'     => $brand->id,
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        $product->categories()->attach($category->id);

        // Create translations
        $product->translations()->create([
            'locale'            => 'en',
            'name'              => 'Product EN',
            'slug'              => 'product-en',
            'short_description' => 'Short description EN',
        ]);

        $brand->translations()->create([
            'locale' => 'en',
            'name'   => 'Brand EN',
            'slug'   => 'brand-en',
        ]);

        $category->translations()->create([
            'locale' => 'en',
            'name'   => 'Category EN',
            'slug'   => 'category-en',
        ]);

        // Load product with eager loading and aggregates (common pattern in product lists)
        $loadedProduct = Product::query()
            ->forProductList()
            ->withListRelations()
            ->withCount(['reviews' => fn ($q) => $q->where('is_approved', true)])
            ->withAvg(['reviews as average_rating' => fn ($q) => $q->where('is_approved', true)], 'rating')
            ->find($product->id);

        $this->assertNotNull($loadedProduct);

        // Verify relations are loaded
        $this->assertTrue($loadedProduct->relationLoaded('brand'));
        $this->assertTrue($loadedProduct->relationLoaded('categories'));
        $this->assertTrue($loadedProduct->relationLoaded('translations'));
        $this->assertTrue($loadedProduct->relationLoaded('media'));

        // Enable query logging
        DB::enableQueryLog();

        // Access all relations and aggregated data - should not trigger lazy loading
        $brandName = $loadedProduct->brand?->name;
        $brandTransName = $loadedProduct->brand?->trans('name', 'en');

        $categoryNames = $loadedProduct->categories->pluck('name')->toArray();
        foreach ($loadedProduct->categories as $cat) {
            $catTransName = $cat->trans('name', 'en');
        }

        $productTransName = $loadedProduct->trans('name', 'en');
        $productShortDesc = $loadedProduct->trans('short_description', 'en');

        $mediaCount = $loadedProduct->media->count();

        // Access aggregated data
        $reviewsCount = $loadedProduct->reviews_count ?? 0;
        $averageRating = $loadedProduct->average_rating ?? 0;

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Filter out schema introspection queries
        $actualQueries = array_filter($queries, function ($query) {
            $sql = strtolower($query['query']);

            return ! str_contains($sql, 'pragma_table_xinfo') &&
                   ! str_contains($sql, 'sqlite_master') &&
                   ! str_contains($sql, 'information_schema');
        });

        // Property: Accessing relations and aggregates should not trigger lazy loading
        $this->assertEmpty($actualQueries,
            'Accessing relations and aggregates triggered lazy loading: ' .
            json_encode(array_column($actualQueries, 'query'), JSON_PRETTY_PRINT)
        );
    }

    /**
     * Test that missing relations are handled gracefully without lazy loading.
     */
    public function test_missing_relations_handled_without_lazy_loading(): void
    {
        // Set application locale
        app()->setLocale('en');

        // Create product without brand and without categories
        $product = Product::factory()->create([
            'brand_id'     => null, // No brand
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        // Don't attach any categories
        // Don't create any translations
        // Don't create any media

        // Load product with eager loading
        $loadedProduct = Product::query()
            ->forProductList()
            ->withListRelations()
            ->find($product->id);

        $this->assertNotNull($loadedProduct);

        // Verify relations are loaded (even if empty)
        $this->assertTrue($loadedProduct->relationLoaded('brand'));
        $this->assertTrue($loadedProduct->relationLoaded('categories'));
        $this->assertTrue($loadedProduct->relationLoaded('translations'));
        $this->assertTrue($loadedProduct->relationLoaded('media'));

        // Enable query logging
        DB::enableQueryLog();

        // Access all relations even when they're null/empty - should not trigger lazy loading
        $brandName = $loadedProduct->brand?->name; // Should be null if brand_id is null
        $brandTransName = $loadedProduct->brand?->trans('name', 'en'); // Should be null if brand_id is null

        $categoryNames = $loadedProduct->categories->pluck('name')->toArray(); // Should be empty array
        foreach ($loadedProduct->categories as $cat) {
            $catTransName = $cat->trans('name', 'en'); // Should not execute
        }

        $productTransName = $loadedProduct->trans('name', 'en'); // Should fallback to base name
        $productShortDesc = $loadedProduct->trans('short_description', 'en'); // Should fallback

        $mediaCount = $loadedProduct->media->count(); // Should be 0

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Filter out schema introspection queries
        $actualQueries = array_filter($queries, function ($query) {
            $sql = strtolower($query['query']);

            return ! str_contains($sql, 'pragma_table_xinfo') &&
                   ! str_contains($sql, 'sqlite_master') &&
                   ! str_contains($sql, 'information_schema');
        });

        // Property: Accessing missing/empty relations should not trigger lazy loading
        $this->assertEmpty($actualQueries,
            'Accessing missing/empty relations triggered lazy loading: ' .
            json_encode(array_column($actualQueries, 'query'), JSON_PRETTY_PRINT)
        );

        // Verify expected values - brand might exist if brand_id was set by factory
        if ($loadedProduct->brand_id === null) {
            $this->assertNull($loadedProduct->brand);
        } else {
            // If brand exists, it should be loaded without lazy loading
            $this->assertNotNull($loadedProduct->brand);
        }
        $this->assertTrue($loadedProduct->categories->isEmpty());
        $this->assertTrue($loadedProduct->translations->isEmpty());
        $this->assertTrue($loadedProduct->media->isEmpty());
    }
}
