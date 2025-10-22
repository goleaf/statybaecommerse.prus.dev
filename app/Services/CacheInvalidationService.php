<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Observers\Concerns\ResolvesSupportedLocales;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTagHelper;
use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Centralised cache invalidation orchestrator for storefront and dashboard data.
 *
 * The legacy observers relied on bespoke cache clearing routines which made it
 * difficult to guarantee consistency across the various widgets. By funnelling
 * everything through this service we can invalidate tag-aware stores eagerly
 * while still providing deterministic fallbacks for array/file stores used in
 * tests.
 */
final class CacheInvalidationService
{
    use ResolvesSupportedLocales;

    /**
     * Flush cache tags for a specific model instance.
     */
    public function flushForModel(Model $model): void
    {
        if ($model instanceof Product) {
            $this->flushProducts();

            return;
        }

        if ($model instanceof Category) {
            $this->flushCategories();

            return;
        }

        if ($model instanceof Brand) {
            $this->flushBrands();

            return;
        }

        if ($model instanceof Collection) {
            $this->flushCollections();
        }
    }

    /**
     * Flush storefront caches that depend on product listings.
     */
    public function flushProducts(): void
    {
        if ($this->flushTags(CacheTagHelper::products())) {
            return;
        }

        app(\App\UseCases\Cache\InvalidateProductCache::class)();
    }

    /**
     * Flush storefront caches that depend on category hierarchies.
     */
    public function flushCategories(): void
    {
        if ($this->flushTags(CacheTagHelper::categories())) {
            return;
        }

        app(\App\UseCases\Cache\InvalidateCategoryCache::class)();
    }

    /**
     * Flush cached brand highlights when the catalogue changes.
     */
    public function flushBrands(): void
    {
        if ($this->flushTags(CacheTagHelper::brands())) {
            return;
        }

        foreach ([6, 8, 10, 12] as $limit) {
            Cache::forget(CacheKeys::brandTopList($limit));
        }
    }

    /**
     * Flush curated collection widgets for the storefront home page.
     */
    public function flushCollections(): void
    {
        if ($this->flushTags(CacheTagHelper::collections())) {
            return;
        }

        foreach ($this->supportedLocales() as $locale) {
            Cache::forget(CacheKeys::homeCollections($locale));
        }
    }

    /**
     * Flush dashboard widgets and Livewire stats caches.
     */
    public function flushDashboards(): void
    {
        if ($this->flushTags(CacheTagHelper::dashboards())) {
            return;
        }

        $ranges = ['1h', '24h', '7d', '30d'];

        foreach ($ranges as $range) {
            Cache::forget(CacheKeys::dashboardStats($range));
            Cache::forget(CacheKeys::dashboardActivity($range));
            Cache::forget(CacheKeys::dashboardPerformance($range));
        }

        Cache::forget(CacheKeys::dashboardSimplifiedSummary());
    }

    /**
     * Attempt to flush the given tags and gracefully handle unsupported stores.
     *
     * @param  array<int, string>  $tags
     */
    private function flushTags(array $tags): bool
    {
        if ($tags === []) {
            return false;
        }

        $store = Cache::getStore();

        if (! $store instanceof TaggableStore) {
            return false;
        }

        try {
            Cache::tags($tags)->flush();

            return true;
        } catch (Throwable $exception) {
            Log::warning('Failed to flush cache tags', [
                'tags' => $tags,
                'error' => $exception->getMessage(),
            ]);
        }

        return false;
    }
}
