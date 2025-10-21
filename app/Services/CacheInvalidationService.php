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
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Service that keeps cache invalidation consistent across catalog features.
 */
final class CacheInvalidationService
{
    use ResolvesSupportedLocales;

    /**
     * Limits used by brand driven cache entries when cache tags are unavailable.
     *
     * @var array<int, int>
     */
    private const COMMON_BRAND_LIMITS = [4, 6, 8, 10, 12];

    /**
     * Limits used by category cache entries when cache tags are unavailable.
     *
     * @var array<int, int>
     */
    private const COMMON_CATEGORY_LIMITS = [4, 6, 8, 10, 12];

    /**
     * Limits used for featured/latest product lists when tag support is unavailable.
     *
     * @var array<int, int>
     */
    private const PRODUCT_LIST_LIMITS = [4, 6, 8, 10, 12];

    /**
     * Limits used by product shelf widgets when cache tags are unavailable.
     *
     * @var array<int, int>
     */
    private const SHELF_LIMITS = [4, 6, 8, 10, 12];

    /**
     * Presets supported by product shelf widgets when cache tags are unavailable.
     *
     * @var array<int, string>
     */
    private const SHELF_PRESETS = ['featured', 'latest', 'sale', 'trending'];

    /**
     * Metrics exposed on dashboard widgets for explicit cache eviction.
     *
     * @var array<int, string>
     */
    private const DASHBOARD_METRICS = [
        'orders_today',
        'revenue_last_seven_days',
        'new_users_today',
        'low_stock_items',
    ];

    /**
     * Windows used by product sales series caches for fallback invalidation.
     *
     * @var array<int, int>
     */
    private const PRODUCT_SERIES_WINDOWS = [3, 7, 14, 30];

    /**
     * Limits used by navigation category lookups without tag support.
     *
     * @var array<int, int>
     */
    private const NAVIGATION_CATEGORY_LIMITS = [6, 8, 10];

    /**
     * Flush cache tags that correspond to the given model instance.
     */
    public function flushForModel(Model $model): void
    {
        $tags = $this->tagsForModel($model);
        $fallback = $this->fallbackForModel($model);

        if ($tags === []) {
            $this->runFallback($fallback);

            return;
        }

        $this->flushTags($tags, $fallback);
    }

    /**
     * Force refresh of all product centric caches.
     */
    public function flushProducts(): void
    {
        $this->flushTags(CacheTagHelper::products(), $this->fallbackForProducts());
    }

    /**
     * Force refresh of all category centric caches.
     */
    public function flushCategories(): void
    {
        $this->flushTags(CacheTagHelper::categories(), $this->fallbackForCategories());
    }

    /**
     * Force refresh of all brand centric caches.
     */
    public function flushBrands(): void
    {
        $this->flushTags(CacheTagHelper::brands(), $this->fallbackForBrands());
    }

    /**
     * Force refresh of collection centric caches.
     */
    public function flushCollections(): void
    {
        $this->flushTags(CacheTagHelper::collections(), $this->fallbackForCollections());
    }

    /**
     * Force refresh of dashboard caches.
     */
    public function flushDashboards(): void
    {
        $this->flushTags(CacheTagHelper::dashboards(), $this->fallbackForDashboards());
    }

    /**
     * Determine which tag groups should be cleared for a model instance.
     *
     * @return array<int, string>
     */
    private function tagsForModel(Model $model): array
    {
        if ($model instanceof Product) {
            return CacheTagHelper::merge(
                CacheTagHelper::products(),
                CacheTagHelper::categories(),
                CacheTagHelper::brands(),
                CacheTagHelper::collections(),
                CacheTagHelper::dashboards(),
            );
        }

        if ($model instanceof Category) {
            return CacheTagHelper::merge(
                CacheTagHelper::categories(),
                CacheTagHelper::products(),
                CacheTagHelper::dashboards(),
            );
        }

        if ($model instanceof Brand) {
            return CacheTagHelper::merge(
                CacheTagHelper::brands(),
                CacheTagHelper::products(),
                CacheTagHelper::dashboards(),
            );
        }

        if ($model instanceof Collection) {
            return CacheTagHelper::merge(
                CacheTagHelper::collections(),
                CacheTagHelper::products(),
                CacheTagHelper::dashboards(),
            );
        }

        return [];
    }

    /**
     * Attempt to flush a set of tags with graceful degradation when unsupported.
     *
     * @param array<int, string> $tags
     */
    private function flushTags(array $tags, ?Closure $fallback = null): void
    {
        if ($tags === []) {
            return;
        }

        if (Cache::supportsTags()) {
            try {
                Cache::tags($tags)->flush();

                return;
            } catch (Throwable $exception) {
                Log::warning('Failed to flush cache tags', [
                    'tags'  => $tags,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        if ($this->runFallback($fallback)) {
            return;
        }

        Cache::flush();
    }

    /**
     * Determine which fallback routine should run for a model instance.
     */
    private function fallbackForModel(Model $model): ?Closure
    {
        if ($model instanceof Product) {
            $productId = (int) $model->getKey();

            return function () use ($productId): void {
                $this->fallbackForProducts($productId)();
            };
        }

        if ($model instanceof Category) {
            return $this->fallbackForCategories();
        }

        if ($model instanceof Brand) {
            return $this->fallbackForBrands();
        }

        if ($model instanceof Collection) {
            return $this->fallbackForCollections();
        }

        return null;
    }

    /**
     * Build a fallback routine for product-related caches.
     */
    private function fallbackForProducts(?int $productId = null): Closure
    {
        return function () use ($productId): void {
            $this->forgetProductSummaries();
            $this->forgetBrandAggregates();
            $this->forgetCategoryAggregates();
            $this->forgetNavigationCaches();
            $this->forgetHomeShelves();
            $this->forgetCollectionWidgets();
            $this->forgetDashboardMetrics();

            if ($productId !== null) {
                foreach (self::PRODUCT_SERIES_WINDOWS as $days) {
                    Cache::forget(CacheKeys::productSalesSeries($productId, $days));
                }
            }
        };
    }

    /**
     * Build a fallback routine for category caches.
     */
    private function fallbackForCategories(): Closure
    {
        return function (): void {
            $this->forgetCategoryAggregates();
            $this->forgetNavigationCaches();
        };
    }

    /**
     * Build a fallback routine for brand caches.
     */
    private function fallbackForBrands(): Closure
    {
        return function (): void {
            $this->forgetBrandAggregates();
        };
    }

    /**
     * Build a fallback routine for collection caches.
     */
    private function fallbackForCollections(): Closure
    {
        return function (): void {
            $this->forgetCollectionWidgets();
        };
    }

    /**
     * Build a fallback routine for dashboard caches.
     */
    private function fallbackForDashboards(): Closure
    {
        return function (): void {
            $this->forgetDashboardMetrics();
        };
    }

    /**
     * Forget common brand aggregate caches without tag support.
     */
    private function forgetBrandAggregates(): void
    {
        foreach (self::COMMON_BRAND_LIMITS as $limit) {
            Cache::forget(CacheKeys::brandTopList($limit));
        }

        foreach ($this->supportedLocales() as $locale) {
            Cache::forget(sprintf('navigation.featured_brands.%s', $locale));
        }
    }

    /**
     * Forget common category aggregate caches without tag support.
     */
    private function forgetCategoryAggregates(): void
    {
        foreach (self::COMMON_CATEGORY_LIMITS as $limit) {
            Cache::forget(CacheKeys::categoryPopularList($limit));
        }

        Cache::forget(CacheKeys::categoryNavigationTree());
    }

    /**
     * Forget product summary caches such as featured/latest lists.
     */
    private function forgetProductSummaries(): void
    {
        Cache::forget(CacheKeys::productTotalCount());
        Cache::forget(CacheKeys::productVisibleCount());

        foreach (self::PRODUCT_LIST_LIMITS as $limit) {
            Cache::forget(CacheKeys::productFeaturedList($limit));
            Cache::forget(CacheKeys::productLatestList($limit));
        }

        foreach ($this->supportedLocales() as $locale) {
            Cache::forget(CacheKeys::homeFeaturedProducts($locale));
            Cache::forget(CacheKeys::homeLatestProducts($locale));
        }
    }

    /**
     * Forget navigation caches shared across storefront widgets.
     */
    private function forgetNavigationCaches(): void
    {
        foreach ($this->supportedLocales() as $locale) {
            Cache::forget(CacheKeys::homeCategoryTree($locale));
            Cache::forget(CacheKeys::homeCatalogueCategories($locale));

            foreach (self::NAVIGATION_CATEGORY_LIMITS as $limit) {
                Cache::forget(CacheKeys::navigationCategories($limit, $locale));
            }
        }
    }

    /**
     * Forget home shelf caches for known presets without tag support.
     */
    private function forgetHomeShelves(): void
    {
        foreach ($this->supportedLocales() as $locale) {
            foreach (self::SHELF_PRESETS as $preset) {
                foreach (self::SHELF_LIMITS as $limit) {
                    Cache::forget(CacheKeys::homeShelf($preset, $limit, $locale));
                }
            }
        }
    }

    /**
     * Forget collection showcase caches without tag support.
     */
    private function forgetCollectionWidgets(): void
    {
        foreach ($this->supportedLocales() as $locale) {
            Cache::forget(CacheKeys::homeCollections($locale));
        }
    }

    /**
     * Forget dashboard metric caches without tag support.
     */
    private function forgetDashboardMetrics(): void
    {
        foreach (self::DASHBOARD_METRICS as $metric) {
            foreach ($this->supportedLocales() as $locale) {
                Cache::forget(CacheKeys::dashboardMetric($metric, $locale));
            }
        }
    }

    /**
     * Safely execute a fallback routine and report failures.
     */
    private function runFallback(?Closure $fallback): bool
    {
        if ($fallback === null) {
            return false;
        }

        try {
            $fallback();

            return true;
        } catch (Throwable $exception) {
            Log::warning('Cache invalidation fallback failed', [
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
