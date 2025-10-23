<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Components\LiveDashboard;
use App\Livewire\Home\CollectionsShowcase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Services\CacheService;
use App\Services\Shared\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class CacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure every test starts with a clean cache store for deterministic assertions.
        Cache::flush();
    }

    public function test_product_caches_are_invalidated_on_update(): void
    {
        $product = Product::factory()->create([
            'name'         => 'Original Product',
            'is_visible'   => true,
            'is_featured'  => true,
            'published_at' => now()->subDay(),
        ]);

        $service = app(ProductService::class);
        $initialName = $service->getFeaturedProducts()->first()->name;

        $product->update(['name' => 'Updated Product']);

        $updatedName = $service->getFeaturedProducts()->first()->name;

        $this->assertSame('Original Product', $initialName);
        $this->assertSame('Updated Product', $updatedName);
    }

    public function test_product_caches_refresh_without_tag_support(): void
    {
        $this->usingArrayCache(function (): void {
            $product = Product::factory()->create([
                'name'         => 'Array Driver Product',
                'is_visible'   => true,
                'is_featured'  => true,
                'published_at' => now()->subDay(),
            ]);

            $service = app(ProductService::class);
            $initialName = $service->getFeaturedProducts()->first()->name;

            $product->update(['name' => 'Updated Array Product']);

            $updatedName = $service->getFeaturedProducts()->first()->name;

            $this->assertSame('Array Driver Product', $initialName);
            $this->assertSame('Updated Array Product', $updatedName);
        });
    }

    public function test_category_navigation_cache_is_invalidated(): void
    {
        $category = Category::factory()->create([
            'name'       => 'Original Category',
            'is_visible' => true,
        ]);

        $initialNames = CacheService::getNavigationCategories()->pluck('name');
        $this->assertTrue($initialNames->contains('Original Category'));

        $category->update(['name' => 'Updated Category']);

        $updatedNames = CacheService::getNavigationCategories()->pluck('name');
        $this->assertTrue($updatedNames->contains('Updated Category'));
        $this->assertFalse($updatedNames->contains('Original Category'));
    }

    public function test_brand_cache_is_invalidated_after_update(): void
    {
        $brand = Brand::factory()->create([
            'name'        => 'Original Brand',
            'is_visible'  => true,
            'is_featured' => true,
        ]);

        $initialBrands = CacheService::getTopBrands()->pluck('name');
        $this->assertTrue($initialBrands->contains('Original Brand'));

        $brand->update(['name' => 'Updated Brand']);

        $updatedBrands = CacheService::getTopBrands()->pluck('name');
        $this->assertTrue($updatedBrands->contains('Updated Brand'));
        $this->assertFalse($updatedBrands->contains('Original Brand'));
    }

    public function test_brand_cache_is_invalidated_without_tag_support(): void
    {
        $this->usingArrayCache(function (): void {
            $brand = Brand::factory()->create([
                'name'        => 'Array Driver Brand',
                'is_visible'  => true,
                'is_featured' => true,
            ]);

            $initialBrands = CacheService::getTopBrands()->pluck('name');
            $this->assertTrue($initialBrands->contains('Array Driver Brand'));

            $brand->update(['name' => 'Updated Array Brand']);

            $updatedBrands = CacheService::getTopBrands()->pluck('name');
            $this->assertTrue($updatedBrands->contains('Updated Array Brand'));
            $this->assertFalse($updatedBrands->contains('Array Driver Brand'));
        });
    }

    public function test_collection_cache_warms_after_changes(): void
    {
        $collection = Collection::factory()->create([
            'name' => 'Original Collection',
        ]);

        $component = app(CollectionsShowcase::class);
        $initialNames = $component->collections()->pluck('name');
        $this->assertTrue($initialNames->contains('Original Collection'));

        $collection->update(['name' => 'Updated Collection']);

        $updatedNames = $component->collections()->pluck('name');
        $this->assertTrue($updatedNames->contains('Updated Collection'));
        $this->assertFalse($updatedNames->contains('Original Collection'));
    }

    public function test_dashboard_caches_refresh_on_product_creation(): void
    {
        $visibleProduct = Product::factory()->create([
            'is_visible'   => true,
            'is_featured'  => true,
            'published_at' => now()->subDay(),
        ]);

        $dashboard = app(LiveDashboard::class);
        $initialStats = $dashboard->realTimeStats();
        $this->assertSame(
            $visibleProduct->newQuery()->where('is_visible', true)->count(),
            $initialStats['products']['total'],
        );

        Product::factory()->create([
            'is_visible'   => true,
            'is_featured'  => true,
            'published_at' => now()->subDay(),
        ]);

        $updatedStats = $dashboard->realTimeStats();
        $this->assertSame(
            Product::query()->where('is_visible', true)->count(),
            $updatedStats['products']['total'],
        );
    }

    /**
     * Execute the provided callback while forcing the cache driver to the array store.
     *
     * @param callable(): void $callback
     */
    private function usingArrayCache(callable $callback): void
    {
        $manager = app('cache');
        $originalDriver = method_exists($manager, 'getDefaultDriver') ? $manager->getDefaultDriver() : config('cache.default');
        $originalConfig = config('cache.default');

        Cache::flush();
        if (method_exists($manager, 'setDefaultDriver')) {
            $manager->setDefaultDriver('array');
        }
        config()->set('cache.default', 'array');
        Cache::flush();

        try {
            $callback();
        } finally {
            Cache::flush();
            if (method_exists($manager, 'setDefaultDriver')) {
                $manager->setDefaultDriver((string) $originalDriver);
            }
            config()->set('cache.default', $originalConfig);
            Cache::flush();
        }
    }
}
