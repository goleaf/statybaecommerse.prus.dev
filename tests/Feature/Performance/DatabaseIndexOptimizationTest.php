<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * **Feature: performance-update, Property 15: Database Index Optimization**
 * **Validates: Requirements 8.1**
 *
 * Ensures appropriate database indexes exist and are utilized for storefront query patterns.
 */
final class DatabaseIndexOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_visibility_index_exists(): void
    {
        $indexes = $this->getTableIndexes('products');

        expect($indexes)->toContain('products_storefront_visibility_idx')
            ->and($indexes)->toContain('products_price_visibility_idx')
            ->and($indexes)->toContain('products_stock_visibility_idx')
            ->and($indexes)->toContain('products_popularity_idx');
    }

    public function test_product_translations_indexes_exist(): void
    {
        $indexes = $this->getTableIndexes('product_translations');

        expect($indexes)->toContain('product_translations_locale_product_idx');

        // Full-text index only for MySQL
        if (config('database.default') === 'mysql') {
            expect($indexes)->toContain('product_translations_search_idx');
        }
    }

    public function test_brand_collection_category_indexes_exist(): void
    {
        expect($this->getTableIndexes('brands'))->toContain('brands_active_sort_idx');
        expect($this->getTableIndexes('collections'))->toContain('collections_visibility_sort_idx');
        expect($this->getTableIndexes('categories'))->toContain('categories_hierarchy_idx');
    }

    public function test_pivot_table_indexes_exist(): void
    {
        expect($this->getTableIndexes('product_categories'))->toContain('product_categories_category_product_idx');
        expect($this->getTableIndexes('product_collections'))->toContain('product_collections_collection_product_idx');
    }

    public function test_storefront_queries_use_indexes_efficiently(): void
    {
        // Skip for SQLite as EXPLAIN QUERY PLAN format differs
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('Index usage testing not reliable on SQLite');
        }

        $this->seedTestData();

        // Test product visibility query uses index
        $query = Product::query()
            ->where('is_visible', true)
            ->where('is_enabled', true)
            ->whereNotNull('published_at')
            ->orderBy('published_at', 'desc');

        $explain = DB::select('EXPLAIN ' . $query->toSql(), $query->getBindings());

        // Should use the storefront visibility index
        $usesIndex = collect($explain)->contains(function ($row) {
            return str_contains(strtolower($row->Extra ?? ''), 'index') ||
                   str_contains(strtolower($row->key ?? ''), 'storefront_visibility');
        });

        expect($usesIndex)->toBeTrue('Product visibility query should use index');
    }

    public function test_category_hierarchy_query_performance(): void
    {
        $this->seedTestData();

        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        // Query that should use hierarchy index
        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->with(['children' => function ($query) {
                $query->where('is_visible', true)->orderBy('sort_order');
            }])
            ->get();

        expect($categories)->not->toBeEmpty()
            ->and($queryCount)->toBeLessThanOrEqual(3, 'Category hierarchy should be efficient');
    }

    public function test_product_filtering_query_performance(): void
    {
        $this->seedTestData();

        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        // Complex filtering query that should use multiple indexes
        $products = Product::query()
            ->where('is_visible', true)
            ->where('price', '>=', 10)
            ->where('price', '<=', 100)
            ->where('stock_quantity', '>', 0)
            ->whereHas('categories', function ($query) {
                $query->where('is_visible', true);
            })
            ->whereHas('brand', function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('view_count', 'desc')
            ->limit(20)
            ->get();

        expect($queryCount)->toBeLessThanOrEqual(5, 'Complex product filtering should be efficient');
    }

    public function test_search_query_performance(): void
    {
        $this->seedTestData();

        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        // Search query that should use translation indexes
        $searchTerm = 'test';
        $products = Product::query()
            ->whereHas('translations', function ($query) use ($searchTerm) {
                $query->where('locale', 'lt')
                    ->where(function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('description', 'like', "%{$searchTerm}%");
                    });
            })
            ->where('is_visible', true)
            ->limit(10)
            ->get();

        expect($queryCount)->toBeLessThanOrEqual(2, 'Search queries should be efficient');
    }

    private function getTableIndexes(string $table): array
    {
        if (config('database.default') === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list({$table})");

            return collect($indexes)->pluck('name')->toArray();
        }

        if (config('database.default') === 'mysql') {
            $indexes = DB::select("SHOW INDEX FROM {$table}");

            return collect($indexes)->pluck('Key_name')->unique()->toArray();
        }

        return [];
    }

    private function seedTestData(): void
    {
        $brands = Brand::factory()->count(5)->create(['is_active' => true]);
        $categories = Category::factory()->count(8)->create(['is_visible' => true]);
        $collections = Collection::factory()->count(3)->create([
            'is_visible' => true,
            'is_enabled' => true,
        ]);

        $products = Product::factory()->count(20)->create([
            'is_visible'     => true,
            'is_enabled'     => true,
            'published_at'   => now(),
            'price'          => fake()->numberBetween(10, 200),
            'stock_quantity' => fake()->numberBetween(1, 100),
            'view_count'     => fake()->numberBetween(0, 1000),
        ]);

        // Associate products with brands and categories
        foreach ($products as $product) {
            $product->brand()->associate($brands->random());
            $product->categories()->attach($categories->random(2));
            $product->save();
        }
    }
}
