<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Observers\Concerns\ResolvesSupportedLocales;
use App\Support\Cache\CacheInvalidator;
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
            $this->flushProducts($model);

            return;
        }

        if ($model instanceof Category) {
            $this->flushCategories($model);

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
    public function flushProducts(?Product $product = null): void
    {
        if ($product instanceof Product) {
            app(CacheInvalidator::class)->productChanged($product);
        }

        // Include shared home/navigation tags so both storefront widgets and
        // menu payloads flush together when product data changes.
        $productTags = CacheTagHelper::merge(
            CacheTagHelper::products(),
            [CacheKeys::homeTag()],
            [CacheKeys::navigationTag()],
            [CacheKeys::productAggregateTag()]
        );

        $this->flushTags($productTags);

        // Execute the dedicated invalidator to clear any cache entries that
        // may have been written without tag metadata (e.g. array stores or
        // bespoke helpers that bypass the Cache facade helpers).
        app(\App\UseCases\Cache\InvalidateProductCache::class)($product);

        // Clear product payloads cached via the shared cache service which
        // relies on array stores during tests and in certain queue contexts.
        $this->flushSharedProductCaches($product);

        // Ensure every product mutation also refreshes dashboard caches so
        // Livewire components immediately observe the new catalogue totals.
        $this->flushDashboards();
    }

    /**
     * Flush storefront caches that depend on category hierarchies.
     */
    public function flushCategories(?Category $category = null): void
    {
        if ($category instanceof Category) {
            app(CacheInvalidator::class)->categoryChanged($category);
        }

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

        foreach ($this->supportedLocales() as $locale) {
            // Ensure both the legacy navigation cache keys and the new locale-aware
            // helpers are cleared so header menus immediately reflect brand edits.
            $navigationKey = CacheKeys::navigationFeaturedBrands($locale);
            $legacyNavigationKey = 'nav:featured_brands:'.$locale;

            Cache::forget($navigationKey);
            Cache::forget($legacyNavigationKey);

            if (CacheTagHelper::supportsTags()) {
                $brandLocaleTags = CacheTagHelper::merge(
                    CacheTagHelper::brands(),
                    CacheTagHelper::locale($locale)
                );

                Cache::tags($brandLocaleTags)->forget($navigationKey);
                Cache::tags($brandLocaleTags)->forget($legacyNavigationKey);
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
     * Remove cached product payloads stored via the shared cache service.
     */
    private function flushSharedProductCaches(?Product $product = null): void
    {
        $productId = $product?->getKey();

        if (! is_numeric($productId)) {
            $productId = null;
        } else {
            $productId = (int) $productId;
        }

        foreach ($this->supportedLocales() as $locale) {
            $tags = CacheTagHelper::merge(
                CacheTagHelper::products(),
                CacheTagHelper::locale($locale)
            );

            foreach ($this->currenciesForLocale($locale) as $currency) {
                $this->forgetSharedProductKey("featured_products.{$locale}.{$currency}", $tags);
                $this->forgetSharedProductKey("new_arrivals.{$locale}.{$currency}", $tags);
                $this->forgetSharedProductKey("home.featured_products.{$locale}.{$currency}", $tags);

                if ($productId !== null) {
                    $this->forgetSharedProductKey("related_products.{$productId}.{$locale}.{$currency}", $tags);
                }
            }
        }
    }

    /**
     * Resolve the currency codes that should be invalidated for a locale.
     *
     * @return array<int, string>
     */
    private function currenciesForLocale(string $locale): array
    {
        $mapping = config('shared.localization.locale_currency_mapping', []);

        $currencies = [];

        $configured = $mapping[$locale] ?? null;

        if (is_string($configured) && $configured !== '') {
            $currencies[] = $configured;
        } elseif (is_array($configured)) {
            foreach ($configured as $code) {
                if (is_string($code) && $code !== '') {
                    $currencies[] = $code;
                }
            }
        }

        foreach ([
            config('shared.localization.default_currency'),
            config('app.currency'),
            current_currency(),
        ] as $fallback) {
            if (is_string($fallback) && $fallback !== '') {
                $currencies[] = $fallback;
            }
        }

        if ($currencies === []) {
            $currencies[] = 'EUR';
        }

        $currencies = array_map(static fn (string $code): string => trim($code), $currencies);
        $currencies = array_filter($currencies, static fn (string $code): bool => $code !== '');
        $currencies = array_values(array_unique($currencies));

        return $currencies;
    }

    /**
     * Forget a shared cache key and clear the associated tag-aware entry.
     *
     * @param  array<int, string>  $tags
     */
    private function forgetSharedProductKey(string $key, array $tags): void
    {
        Cache::forget($key);

        if ($tags !== [] && CacheTagHelper::supportsTags()) {
            Cache::tags($tags)->forget($key);
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
