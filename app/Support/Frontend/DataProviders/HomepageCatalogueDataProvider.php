<?php

declare(strict_types=1);

namespace App\Support\Frontend\DataProviders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Services\Shared\CacheService as SharedCacheService;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTagHelper;
use App\Support\Frontend\DataProviders\Concerns\BuildsProductCatalogueQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class HomepageCatalogueDataProvider
{
    use BuildsProductCatalogueQuery;

    public function get(): array
    {
        $locale = app()->getLocale();

        /** @var SharedCacheService $sharedCache */
        $sharedCache = app(SharedCacheService::class);

        // Cache the headline statistics behind the merged product, category, and brand tag groups.
        $stats = $sharedCache->rememberDefault(
            CacheKeys::homeStats($locale),
            static function (): array {
                return [
                    'products_count'  => Product::query()->count(),
                    'categories_count' => Category::query()->count(),
                    'brands_count'    => Brand::query()->count(),
                    'reviews_count'   => Review::query()->where('is_approved', true)->count(),
                    'avg_rating'      => (float) (Review::query()->where('is_approved', true)->avg('rating') ?? 0.0),
                ];
            },
            CacheKeys::TTL_ONE_HOUR,
            CacheTagHelper::merge(
                CacheTagHelper::products(),
                CacheTagHelper::categories(),
                CacheTagHelper::brands(),
            ),
        );

        // Feature shelves warm the storefront experience and depend on product mutations exclusively.
        $featuredProducts = $sharedCache->rememberLong(
            CacheKeys::homeFeaturedProducts($locale),
            function (): Collection {
                return $this->baseProductQuery()
                    ->where('is_featured', true)
                    ->limit(8)
                    ->get();
            },
            CacheKeys::TTL_FIVE_MINUTES,
            CacheTagHelper::products(),
        );

        // Latest arrivals are also product-driven and reuse the same tag grouping.
        $latestProducts = $sharedCache->rememberLong(
            CacheKeys::homeLatestProducts($locale),
            function (): Collection {
                return $this->baseProductQuery()
                    ->orderByDesc('published_at')
                    ->limit(8)
                    ->get();
            },
            CacheKeys::TTL_FIVE_MINUTES,
            CacheTagHelper::products(),
        );

        // Popular categories use both category and product relationships to determine demand.
        $popularCategories = $sharedCache->rememberDefault(
            CacheKeys::categoryPopularList(6),
            static function (): Collection {
                return Category::query()
                    ->withCount([
                        'products as visible_products_count' => static function (Builder $query): void {
                            $query->where('is_visible', true)
                                ->whereNotNull('published_at')
                                ->where('published_at', '<=', now());
                        },
                    ])
                    ->orderByDesc('visible_products_count')
                    ->limit(6)
                    ->get();
            },
            CacheKeys::TTL_ONE_HOUR,
            CacheTagHelper::merge(
                CacheTagHelper::categories(),
                CacheTagHelper::products(),
            ),
        );

        // Top brands highlight catalogue depth per manufacturer and should clear when either brand or product data shifts.
        $topBrands = $sharedCache->rememberDefault(
            CacheKeys::brandTopList(6),
            static function (): Collection {
                return Brand::query()
                    ->withCount([
                        'products as visible_products_count' => static function (Builder $query): void {
                            $query->where('is_visible', true)
                                ->whereNotNull('published_at')
                                ->where('published_at', '<=', now());
                        },
                    ])
                    ->orderByDesc('visible_products_count')
                    ->limit(6)
                    ->get();
            },
            CacheKeys::TTL_ONE_HOUR,
            CacheTagHelper::merge(
                CacheTagHelper::brands(),
                CacheTagHelper::products(),
            ),
        );

        return [
            'stats' => $stats,
            'featuredProducts' => $featuredProducts,
            'latestProducts' => $latestProducts,
            'popularCategories' => $popularCategories,
            'topBrands' => $topBrands,
            'popularBrands' => $topBrands,
        ];
    }
}
