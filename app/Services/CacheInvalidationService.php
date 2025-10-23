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
        // Include shared home/navigation tags so both storefront widgets and
        // menu payloads flush together when product data changes.
        $productTags = CacheTagHelper::merge(
            CacheTagHelper::products(),
            [CacheKeys::homeTag()],
            [CacheKeys::navigationTag()]
        );

        $this->flushTags($productTags);

        // Execute the dedicated invalidator to clear any cache entries that
        // may have been written without tag metadata (e.g. array stores or
        // bespoke helpers that bypass the Cache facade helpers).
        app(\App\UseCases\Cache\InvalidateProductCache::class)();

        // Ensure every product mutation also refreshes dashboard caches so
        // Livewire components immediately observe the new catalogue totals.
        $this->flushDashboards();
    }

    /**
     * Flush storefront caches that depend on category hierarchies.
     */
    public function flushCategories(): void
    {
        // Categories influence both storefront widgets and navigation menus.
        $categoryTags = CacheTagHelper::merge(
            CacheTagHelper::categories(),
            [CacheKeys::homeTag()],
            [CacheKeys::navigationTag()]
        );

        $this->flushTags($categoryTags);

        // Always run the fallback invalidator to remove non-tagged payloads
        // such as legacy navigation trees populated directly via Cache::put().
        app(\App\UseCases\Cache\InvalidateCategoryCache::class)();
    }

    /**
     * Flush cached brand highlights when the catalogue changes.
     */
    public function flushBrands(): void
    {
        // Featured brand carousels live in storefront sections, so share the home tag.
        $brandTags = CacheTagHelper::merge(
            CacheTagHelper::brands(),
            [CacheKeys::homeTag()]
        );

        $this->flushTags($brandTags);

        foreach ([6, 8, 10, 12] as $limit) {
            Cache::forget(CacheKeys::brandTopList($limit));

            if (CacheTagHelper::supportsTags()) {
                Cache::tags(CacheTagHelper::brands())->forget(CacheKeys::brandTopList($limit));
            }
        }
    }

    /**
     * Flush curated collection widgets for the storefront home page.
     */
    public function flushCollections(): void
    {
        // Collections feed the home page carousels; merge with the shared home tag.
        $collectionTags = CacheTagHelper::merge(
            CacheTagHelper::collections(),
            [CacheKeys::homeTag()]
        );

        $this->flushTags($collectionTags);

        foreach ($this->supportedLocales() as $locale) {
            Cache::forget(CacheKeys::homeCollections($locale));

            if (CacheTagHelper::supportsTags()) {
                $collectionLocaleTags = CacheTagHelper::merge(
                    CacheTagHelper::collections(),
                    CacheTagHelper::locale($locale)
                );

                Cache::tags($collectionLocaleTags)->forget(CacheKeys::homeCollections($locale));
            }
        }
    }

    /**
     * Flush dashboard widgets and Livewire stats caches.
     */
    public function flushDashboards(): void
    {
        $ranges = ['1h', '24h', '7d', '30d'];

        foreach ($ranges as $range) {
            Cache::forget(CacheKeys::dashboardStats($range));
            Cache::forget(CacheKeys::dashboardActivity($range));
            Cache::forget(CacheKeys::dashboardPerformance($range));

            if (CacheTagHelper::supportsTags()) {
                Cache::tags(CacheTagHelper::dashboards())->forget(CacheKeys::dashboardStats($range));
                Cache::tags(CacheTagHelper::dashboards())->forget(CacheKeys::dashboardActivity($range));
                Cache::tags(CacheTagHelper::dashboards())->forget(CacheKeys::dashboardPerformance($range));
            }
        }

        Cache::forget(CacheKeys::dashboardSimplifiedSummary());

        if (CacheTagHelper::supportsTags()) {
            Cache::tags(CacheTagHelper::dashboards())->forget(CacheKeys::dashboardSimplifiedSummary());
        }
    }

    /**
     * Attempt to flush the given tags and gracefully handle unsupported stores.
     *
     * @param array<int, string> $tags
     */
    private function flushTags(array $tags): bool
    {
        if ($tags === []) {
            return false;
        }

        $store = Cache::getStore();

        if (! $store instanceof TaggableStore) {
            Log::warning('Cache tags unavailable; performing full cache flush', [
                'tags'   => $tags,
                'reason' => 'no_tags',
            ]);

            return false;
        }

        try {
            Cache::tags($tags)->flush();

            return true;
        } catch (Throwable $exception) {
            Log::warning('Failed to flush cache tags', [
                'tags'  => $tags,
                'error' => $exception->getMessage(),
            ]);
        }

        return false;
    }
}
