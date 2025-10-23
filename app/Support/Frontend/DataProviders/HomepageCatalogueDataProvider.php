<?php

declare(strict_types=1);

namespace App\Support\Frontend\DataProviders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\TagAwareCache;
use App\Support\Frontend\DataProviders\Concerns\BuildsProductCatalogueQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class HomepageCatalogueDataProvider
{
    use BuildsProductCatalogueQuery;

    public function get(): array
    {
        $locale = app()->getLocale();

        $stats = TagAwareCache::remember(
            CacheKeys::homeStats($locale),
            CacheKeys::TTL_ONE_HOUR,
            static function (): array {
                return [
                    'products_count' => Product::query()->count(),
                    'categories_count' => Category::query()->count(),
                    'brands_count' => Brand::query()->count(),
                    'reviews_count' => Review::query()->where('is_approved', true)->count(),
                    'avg_rating' => (float) (Review::query()->where('is_approved', true)->avg('rating') ?? 0.0),
                ];
            },
            [CacheKeys::homeTag(), CacheKeys::productAggregateTag()]
        );

        $featuredProducts = TagAwareCache::remember(
            CacheKeys::homeFeaturedProducts($locale),
            CacheKeys::TTL_FIVE_MINUTES,
            function (): Collection {
                return $this->baseProductQuery()
                    ->where('is_featured', true)
                    ->limit(8)
                    ->get();
            },
            [CacheKeys::homeTag()]
        );

        $latestProducts = TagAwareCache::remember(
            CacheKeys::homeLatestProducts($locale),
            CacheKeys::TTL_FIVE_MINUTES,
            function (): Collection {
                return $this->baseProductQuery()
                    ->orderByDesc('published_at')
                    ->limit(8)
                    ->get();
            },
            [CacheKeys::homeTag()]
        );

        $popularCategories = TagAwareCache::remember(
            CacheKeys::categoryPopularList(6),
            CacheKeys::TTL_ONE_HOUR,
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
            [CacheKeys::homeTag()]
        );

        $topBrands = TagAwareCache::remember(
            CacheKeys::brandTopList(6),
            CacheKeys::TTL_ONE_HOUR,
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
            [CacheKeys::homeTag()]
        );

        return [
            'stats' => $stats,
            'featuredProducts' => $featuredProducts,
            'latestProducts' => $latestProducts,
            'popularCategories' => $popularCategories,
            'topBrands' => $topBrands,
        ];
    }
}
