<?php

declare(strict_types=1);

namespace App\Support\Cache;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Services\Shared\CacheService;
use App\Support\Localization\LocaleResolver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Warms critical caches to ensure zero database queries for warm cache scenarios.
 */
final class CacheWarmer
{
    public function __construct(
        private readonly CacheService $cacheService,
        private readonly LocaleResolver $localeResolver
    ) {}

    /**
     * Warm all critical storefront caches.
     */
    public function warmStorefront(): void
    {
        $locales = $this->localeResolver->getSupportedLocales();

        foreach ($locales as $locale) {
            $this->warmHomePageCaches($locale);
            $this->warmNavigationCaches($locale);
            $this->warmCatalogCaches($locale);
        }

        Log::info('Storefront caches warmed', ['locales' => $locales]);
    }

    /**
     * Warm home page specific caches.
     */
    private function warmHomePageCaches(string $locale): void
    {
        // Warm featured products
        $this->cacheService->rememberCollection(
            "featured_products.{$locale}",
            fn () => Product::query()
                ->with(['brand', 'media', 'translations'])
                ->published()
                ->where('is_featured', true)
                ->limit(8)
                ->get(),
            3600,
            CacheTagHelper::merge(
                CacheTagHelper::products(),
                CacheTagHelper::locale($locale),
                [CacheKeys::homeTag()]
            )
        );

        // Warm featured collections
        $this->cacheService->rememberCollection(
            "featured_collections.{$locale}",
            fn () => Collection::query()
                ->with(['products', 'translations'])
                ->where('is_visible', true)
                ->where('is_enabled', true)
                ->orderBy('sort_order')
                ->limit(6)
                ->get(),
            3600,
            CacheTagHelper::merge(
                CacheTagHelper::collections(),
                CacheTagHelper::locale($locale),
                [CacheKeys::homeTag()]
            )
        );

        // Warm featured brands
        $this->cacheService->rememberCollection(
            "featured_brands.{$locale}",
            fn () => Brand::query()
                ->with(['products', 'translations'])
                ->whereHas('products', function ($query) {
                    $query->published();
                })
                ->limit(12)
                ->get(),
            3600,
            CacheTagHelper::merge(
                CacheTagHelper::brands(),
                CacheTagHelper::locale($locale),
                [CacheKeys::homeTag()]
            )
        );
    }

    /**
     * Warm navigation specific caches.
     */
    private function warmNavigationCaches(string $locale): void
    {
        // Warm main navigation categories
        $this->cacheService->rememberCollection(
            "navigation_categories.{$locale}",
            fn () => Category::query()
                ->with(['children', 'translations'])
                ->whereNull('parent_id')
                ->where('is_visible', true)
                ->orderBy('sort_order')
                ->get(),
            7200, // 2 hours - navigation changes less frequently
            CacheTagHelper::merge(
                CacheTagHelper::categories(),
                CacheTagHelper::locale($locale),
                [CacheKeys::navigationTag()]
            )
        );
    }

    /**
     * Warm catalog specific caches.
     */
    private function warmCatalogCaches(string $locale): void
    {
        // Warm category facet counts
        $this->cacheService->rememberDefault(
            "category_facets.{$locale}",
            function () {
                return [
                    'brands' => Brand::query()
                        ->whereHas('products', function ($query) {
                            $query->published();
                        })
                        ->withCount(['products' => function ($query) {
                            $query->published();
                        }])
                        ->get()
                        ->pluck('products_count', 'id')
                        ->toArray(),

                    'collections' => Collection::query()
                        ->where('is_visible', true)
                        ->where('is_enabled', true)
                        ->withCount(['products' => function ($query) {
                            $query->published();
                        }])
                        ->get()
                        ->pluck('products_count', 'id')
                        ->toArray(),

                    'categories' => Category::query()
                        ->withCount(['products' => function ($query) {
                            $query->published();
                        }])
                        ->get()
                        ->pluck('products_count', 'id')
                        ->toArray(),
                ];
            },
            1800, // 30 minutes
            CacheTagHelper::merge(
                CacheTagHelper::products(),
                CacheTagHelper::brands(),
                CacheTagHelper::collections(),
                CacheTagHelper::categories(),
                CacheTagHelper::locale($locale)
            )
        );
    }

    /**
     * Check if critical caches are warm.
     */
    public function areCachesWarm(string $locale): bool
    {
        $criticalKeys = [
            "collection:featured_products.{$locale}",
            "collection:featured_collections.{$locale}",
            "collection:featured_brands.{$locale}",
            "collection:navigation_categories.{$locale}",
            "category_facets.{$locale}",
        ];

        foreach ($criticalKeys as $key) {
            if (! Cache::has($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Prevent redundant cache writes by checking if content has changed.
     */
    public function shouldUpdateCache(string $key, mixed $newData): bool
    {
        $existing = Cache::get($key);

        if ($existing === null) {
            return true;
        }

        // For arrays, do a simple comparison
        if (is_array($existing) && is_array($newData)) {
            return serialize($existing) !== serialize($newData);
        }

        return $existing !== $newData;
    }
}
