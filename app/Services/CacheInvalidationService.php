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
 * Centralized cache tag invalidation service responsible for flushing storefront and dashboard caches.
 */
final class CacheInvalidationService
{
    use ResolvesSupportedLocales;

    /**
     * Map of model classes to their associated cache tags.
     *
     * @var array<class-string, array<int, string>>
     */
    private const MODEL_TAGS = [
        Product::class => [
            CacheTagHelper::PRODUCTS,
            CacheTagHelper::CATEGORIES,
            CacheTagHelper::BRANDS,
            CacheTagHelper::COLLECTIONS,
            CacheTagHelper::DASHBOARDS,
        ],
        Category::class => [
            CacheTagHelper::CATEGORIES,
            CacheTagHelper::PRODUCTS,
            CacheTagHelper::DASHBOARDS,
        ],
        Brand::class => [
            CacheTagHelper::BRANDS,
            CacheTagHelper::PRODUCTS,
            CacheTagHelper::DASHBOARDS,
        ],
        Collection::class => [
            CacheTagHelper::COLLECTIONS,
            CacheTagHelper::PRODUCTS,
            CacheTagHelper::DASHBOARDS,
        ],
    ];

    /**
     * Default limits we cache for product driven shelves.
     *
     * @var array<int, int>
     */
    private const PRODUCT_LIST_LIMITS = [4, 6, 8, 10, 12];

    /**
     * Default limits for category popularity calculations.
     *
     * @var array<int, int>
     */
    private const CATEGORY_LIST_LIMITS = [6, 8, 10, 12];

    /**
     * Default limits for brand leaderboards.
     *
     * @var array<int, int>
     */
    private const BRAND_LIST_LIMITS = [6, 8, 10, 12];

    /**
     * Shelf presets rendered on the home page.
     *
     * @var array<int, string>
     */
    private const SHELF_PRESETS = ['featured', 'latest', 'sale', 'trending'];

    /**
     * Shelf sizes we warm for the storefront carousel widgets.
     *
     * @var array<int, int>
     */
    private const SHELF_LIMITS = [4, 6, 8, 10, 12];

    /**
     * Flush cache tags for a specific model instance.
     */
    public function flushForModel(Model $model): void
    {
        foreach (self::MODEL_TAGS as $class => $tags) {
            if (! ($model instanceof $class)) {
                continue;
            }

            // Pass the relevant identifier into the fallback context so per-model caches can be cleared precisely.
            $this->flushTags($tags, $this->contextForModel($model));

            return;
        }
    }

    /**
     * Flush product related caches.
     */
    public function flushProducts(?int $productId = null): void
    {
        $this->flushTags(CacheTagHelper::products(), ['product_id' => $productId]);
    }

    /**
     * Flush category related caches.
     */
    public function flushCategories(?int $categoryId = null): void
    {
        $this->flushTags(CacheTagHelper::categories(), ['category_id' => $categoryId]);
    }

    /**
     * Flush brand related caches.
     */
    public function flushBrands(?int $brandId = null): void
    {
        $this->flushTags(CacheTagHelper::brands(), ['brand_id' => $brandId]);
    }

    /**
     * Flush collection related caches.
     */
    public function flushCollections(?int $collectionId = null): void
    {
        $this->flushTags(CacheTagHelper::collections(), ['collection_id' => $collectionId]);
    }

    /**
     * Flush dashboard related caches.
     */
    public function flushDashboards(): void
    {
        $this->flushTags(CacheTagHelper::dashboards());
    }

    /**
     * Attempt to flush the given tags and gracefully handle unsupported stores.
     *
     * @param array<int, string> $tags
     * @param array<string, int|null> $context
     */
    private function flushTags(array $tags, array $context = []): void
    {
        if ($tags === []) {
            return;
        }

        $store = Cache::getStore();

        if ($store instanceof TaggableStore) {
            try {
                Cache::tags($tags)->flush();

                return;
            } catch (Throwable $exception) {
                Log::warning('Failed to flush cache tags', [
                    'tags'    => $tags,
                    'error'   => $exception->getMessage(),
                    'context' => $context,
                ]);
            }
        }

        $this->flushFallback($tags, $context);
    }

    /**
     * Provide manual cache invalidation when cache tags are unavailable.
     *
     * @param array<int, string>        $tags
     * @param array<string, int|null> $context
     */
    private function flushFallback(array $tags, array $context): void
    {
        foreach ($tags as $tag) {
            match ($tag) {
                CacheTagHelper::PRODUCTS => $this->flushProductFallback($context['product_id'] ?? null),
                CacheTagHelper::CATEGORIES => $this->flushCategoryFallback($context['category_id'] ?? null),
                CacheTagHelper::BRANDS => $this->flushBrandFallback($context['brand_id'] ?? null),
                CacheTagHelper::COLLECTIONS => $this->flushCollectionFallback($context['collection_id'] ?? null),
                CacheTagHelper::DASHBOARDS => $this->flushDashboardFallback(),
                default => null,
            };
        }
    }

    /**
     * Determine the contextual identifiers for the provided model.
     *
     * @return array<string, int>
     */
    private function contextForModel(Model $model): array
    {
        return match (true) {
            $model instanceof Product => ['product_id' => (int) $model->getKey()],
            $model instanceof Category => ['category_id' => (int) $model->getKey()],
            $model instanceof Brand => ['brand_id' => (int) $model->getKey()],
            $model instanceof Collection => ['collection_id' => (int) $model->getKey()],
            default => [],
        };
    }

    private function flushProductFallback(?int $productId): void
    {
        // Aggregate counts shared across dashboards and storefront widgets.
        Cache::forget(CacheKeys::productTotalCount());
        Cache::forget(CacheKeys::productVisibleCount());

        foreach (self::PRODUCT_LIST_LIMITS as $limit) {
            Cache::forget(CacheKeys::productFeaturedList($limit));
            Cache::forget(CacheKeys::productLatestList($limit));
        }

        $locales = $this->supportedLocales();
        $currencies = $this->supportedCurrencies();

        foreach ($locales as $locale) {
            Cache::forget(CacheKeys::homeFeaturedProducts($locale));
            Cache::forget(CacheKeys::homeLatestProducts($locale));
            Cache::forget(CacheKeys::homeStats($locale));

            foreach (self::SHELF_PRESETS as $preset) {
                foreach (self::SHELF_LIMITS as $limit) {
                    Cache::forget(CacheKeys::homeShelf($preset, $limit, $locale));
                }
            }
        }

        foreach ($locales as $locale) {
            foreach ($currencies as $currency) {
                Cache::forget("featured_products.{$locale}.{$currency}");
                Cache::forget("new_arrivals.{$locale}.{$currency}");

                if ($productId !== null) {
                    Cache::forget("related_products.{$productId}.{$locale}.{$currency}");
                }
            }
        }

        if ($productId !== null) {
            Cache::forget(CacheKeys::productTag($productId));
        }
    }

    private function flushCategoryFallback(?int $categoryId): void
    {
        $locales = $this->supportedLocales();

        foreach (self::CATEGORY_LIST_LIMITS as $limit) {
            Cache::forget(CacheKeys::categoryPopularList($limit));
        }

        foreach ($locales as $locale) {
            Cache::forget(CacheKeys::homeCategoryTree($locale));
            Cache::forget(CacheKeys::homeCatalogueCategories($locale));
            Cache::forget(CacheKeys::navigationCategories(8, $locale));
            Cache::forget(CacheKeys::navigationCategories(12, $locale));
            Cache::forget(CacheKeys::homeStats($locale));
        }

        if ($categoryId !== null) {
            Cache::forget(CacheKeys::categoryTag($categoryId));
        }
    }

    private function flushBrandFallback(?int $brandId): void
    {
        foreach (self::BRAND_LIST_LIMITS as $limit) {
            Cache::forget(CacheKeys::brandTopList($limit));
        }

        foreach ($this->supportedLocales() as $locale) {
            Cache::forget(CacheKeys::homeStats($locale));
        }

        if ($brandId !== null) {
            Cache::forget(CacheKeys::brandTag($brandId));
        }
    }

    private function flushCollectionFallback(?int $collectionId): void
    {
        foreach ($this->supportedLocales() as $locale) {
            Cache::forget(CacheKeys::homeCollections($locale));

            if ($collectionId !== null) {
                Cache::forget("collection.{$collectionId}.{$locale}");
            }
        }
    }

    private function flushDashboardFallback(): void
    {
        Cache::forget(CacheKeys::dashboardSummary());

        $ranges = ['1h', '24h', '7d', '30d'];

        foreach ($ranges as $range) {
            Cache::forget(CacheKeys::dashboardStats($range));
            Cache::forget(CacheKeys::dashboardActivity($range));
            Cache::forget(CacheKeys::dashboardPerformance($range));
        }

        $now = now();
        $start = $now->copy()->subDays(6)->toDateString();
        $end = $now->copy()->toDateString();

        Cache::forget("dashboard.simplified-stats.chart.{$start}.{$end}");
    }

    /**
     * @return array<int, string>
     */
    private function supportedCurrencies(): array
    {
        $mapping = config('shared.localization.locale_currency_mapping', []);

        if (! is_array($mapping)) {
            $mapping = [];
        }

        $currencies = array_filter(array_map(
            static fn ($currency) => is_string($currency) ? trim($currency) : null,
            array_values($mapping)
        ));

        $defaults = [
            config('shared.localization.default_currency'),
            config('app.currency'),
        ];

        foreach ($defaults as $default) {
            if (is_string($default)) {
                $currencies[] = trim($default);
            }
        }

        $normalized = [];

        foreach ($currencies as $currency) {
            if ($currency === '') {
                continue;
            }

            $normalized[$currency] = $currency;
        }

        if ($normalized === []) {
            $normalized['EUR'] = 'EUR';
        }

        return array_values($normalized);
    }
}
