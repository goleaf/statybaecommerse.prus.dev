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
use Tests\TestCase;

final class CacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

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
}
