<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Components\LiveDashboard;
use App\Livewire\Home\CollectionsShowcase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Services\CacheInvalidationService;
use App\Services\CacheService;
use App\Services\Shared\ProductService;
use App\Support\Cache\CacheKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(CacheInvalidationService::class)]
final class CacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::clear();
    }

    public function test_flush_dashboards_clears_known_keys(): void
    {
        Cache::put(CacheKeys::dashboardStats('24h'), ['value' => 1], 600);
        Cache::put(CacheKeys::dashboardActivity('24h'), ['value' => 1], 600);
        Cache::put(CacheKeys::dashboardPerformance('24h'), ['value' => 1], 600);
        Cache::put(CacheKeys::dashboardSimplifiedSummary(), ['value' => 1], 600);

        app(CacheInvalidationService::class)->flushDashboards();

        $this->assertFalse(Cache::has(CacheKeys::dashboardStats('24h')));
        $this->assertFalse(Cache::has(CacheKeys::dashboardActivity('24h')));
        $this->assertFalse(Cache::has(CacheKeys::dashboardPerformance('24h')));
        $this->assertFalse(Cache::has(CacheKeys::dashboardSimplifiedSummary()));
    }

    public function test_flush_products_forgets_featured_lists_on_array_store(): void
    {
        Cache::put(CacheKeys::productFeaturedList(8), ['value' => 1], 600);
        Cache::put(CacheKeys::productLatestList(6), ['value' => 1], 600);

        app(CacheInvalidationService::class)->flushProducts();

        $this->assertFalse(Cache::has(CacheKeys::productFeaturedList(8)));
        $this->assertFalse(Cache::has(CacheKeys::productLatestList(6)));
    }

    public function test_product_update_flushes_featured_product_cache(): void
    {
        $product = Product::factory()->published()->featured()->create([
            'name'       => 'Original Product',
            'is_visible' => true,
        ]);

        $service = app(ProductService::class);

        $initialName = $service->getFeaturedProducts()->first()->name;

        $product->update(['name' => 'Updated Product']);

        $updatedName = $service->getFeaturedProducts()->first()->name;

        $this->assertSame('Original Product', $initialName);
        $this->assertSame('Updated Product', $updatedName);
    }

    public function test_category_navigation_cache_is_refreshed_on_update(): void
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

    public function test_brand_cache_updates_after_edit(): void
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

    public function test_collection_cache_invalidation_reloads_widget(): void
    {
        $initialLocale = app()->getLocale();
        app()->setLocale('lt');

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

        app()->setLocale($initialLocale);
    }

    public function test_dashboard_stats_cache_is_cleared_on_product_creation(): void
    {
        Product::factory()->published()->featured()->create([
            'is_visible' => true,
        ]);

        /** @var LiveDashboard $dashboard */
        $dashboard = app(LiveDashboard::class);
        $initialStats = $dashboard->realTimeStats();

        Product::factory()->published()->featured()->create([
            'is_visible' => true,
        ]);

        $updatedStats = $dashboard->realTimeStats();

        $this->assertSame(
            Product::query()->where('is_visible', true)->count(),
            $updatedStats['products']['total']
        );
        $this->assertNotSame($initialStats['products']['total'], $updatedStats['products']['total']);
    }
}
