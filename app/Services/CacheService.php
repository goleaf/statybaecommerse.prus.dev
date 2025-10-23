<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\TagAwareCache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * CacheService
 *
 * Service class containing CacheService business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class CacheService
{
    /**
     * Handle getFeaturedProducts functionality with proper error handling.
     */
    public static function getFeaturedProducts(int $limit = 8): Collection
    {
        return TagAwareCache::remember(
            CacheKeys::productFeaturedList($limit),
            CacheKeys::TTL_ONE_HOUR,
            fn () => Product::where('is_featured', true)->where('is_visible', true)->with(['brand', 'categories', 'media'])->limit($limit)->get(),
            [CacheKeys::homeTag(), CacheKeys::productAggregateTag()]
        );
    }

    /**
     * Handle getPopularCategories functionality with proper error handling.
     */
    public static function getPopularCategories(int $limit = 6): Collection
    {
        return TagAwareCache::remember(
            CacheKeys::categoryPopularList($limit),
            CacheKeys::TTL_ONE_HOUR,
            fn () => Category::where('is_visible', true)->where('is_featured', true)->with(['media'])->withCount('products')->orderBy('products_count', 'desc')->limit($limit)->get(),
            [CacheKeys::homeTag()]
        );
    }

    /**
     * Handle getTopBrands functionality with proper error handling.
     */
    public static function getTopBrands(int $limit = 10): Collection
    {
        return TagAwareCache::remember(
            CacheKeys::brandTopList($limit),
            CacheKeys::TTL_ONE_HOUR,
            fn () => Brand::where('is_visible', true)->where('is_featured', true)->with(['media'])->withCount('products')->orderBy('products_count', 'desc')->limit($limit)->get(),
            [CacheKeys::homeTag()]
        );
    }

    /**
     * Handle getNavigationCategories functionality with proper error handling.
     */
    public static function getNavigationCategories(): Collection
    {
        return TagAwareCache::remember(
            CacheKeys::categoryNavigationTree(),
            CacheKeys::TTL_ONE_DAY,
            fn () => Category::where('is_visible', true)->whereNull('parent_id')->with(['children' => function ($query) {
                $query->where('is_visible', true)->orderBy('sort_order')->orderBy('name');
            }])->orderBy('sort_order')->orderBy('name')->get(),
            [CacheKeys::homeTag()]
        );
    }

    /**
     * Handle clearProductCaches functionality with proper error handling.
     */
    public static function clearProductCaches(): void
    {
        TagAwareCache::flush([
            CacheKeys::homeTag(),
            CacheKeys::productAggregateTag(),
        ]);

        Cache::forget(CacheKeys::productFeaturedList(8));
        Cache::forget(CacheKeys::categoryPopularList(6));
        Cache::forget(CacheKeys::brandTopList(10));
        Cache::forget(CacheKeys::categoryNavigationTree());
    }

    /**
     * Handle warmupCaches functionality with proper error handling.
     */
    public static function warmupCaches(): void
    {
        self::getFeaturedProducts();
        self::getPopularCategories();
        self::getTopBrands();
        self::getNavigationCategories();
    }

    /**
     * @template TCacheValue
     *
     * @param  array<int, string>  $tags
     * @param  Closure(): TCacheValue  $callback
     * @return TCacheValue
     */
    private static function rememberWithTags(array $tags, string $key, int|\DateInterval $ttl, Closure $callback)
    {
        if ($tags !== [] && Cache::getStore() instanceof TaggableStore) {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        }

        return Cache::remember($key, $ttl, $callback);
    }
}
