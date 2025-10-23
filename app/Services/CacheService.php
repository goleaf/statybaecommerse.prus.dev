<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTagHelper;
use Closure;
use DateInterval;
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
        return self::rememberWithTags(
            CacheTagHelper::products(),
            CacheKeys::productFeaturedList($limit),
            CacheKeys::TTL_ONE_HOUR,
            fn () => Product::where('is_featured', true)->where('is_visible', true)->with(['brand', 'categories', 'media'])->limit($limit)->get(),
        );
    }

    /**
     * Handle getPopularCategories functionality with proper error handling.
     */
    public static function getPopularCategories(int $limit = 6): Collection
    {
        return self::rememberWithTags(
            CacheTagHelper::categories(),
            CacheKeys::categoryPopularList($limit),
            CacheKeys::TTL_ONE_HOUR,
            fn () => Category::where('is_visible', true)->where('is_featured', true)->with(['media'])->withCount('products')->orderBy('products_count', 'desc')->limit($limit)->get(),
        );
    }

    /**
     * Handle getTopBrands functionality with proper error handling.
     */
    public static function getTopBrands(int $limit = 10): Collection
    {
        return self::rememberWithTags(
            CacheTagHelper::brands(),
            CacheKeys::brandTopList($limit),
            CacheKeys::TTL_ONE_HOUR,
            fn () => Brand::where('is_visible', true)->where('is_featured', true)->with(['media'])->withCount('products')->orderBy('products_count', 'desc')->limit($limit)->get(),
        );
    }

    /**
     * Handle getNavigationCategories functionality with proper error handling.
     */
    public static function getNavigationCategories(): Collection
    {
        return self::rememberWithTags(
            CacheTagHelper::categories(),
            CacheKeys::categoryNavigationTree(),
            CacheKeys::TTL_ONE_DAY,
            fn () => Category::where('is_visible', true)->whereNull('parent_id')->with(['children' => function ($query): void {
                $query->where('is_visible', true)->orderBy('sort_order')->orderBy('name');
            }])->orderBy('sort_order')->orderBy('name')->get(),
        );
    }

    /**
     * Handle clearProductCaches functionality with proper error handling.
     */
    public static function clearProductCaches(): void
    {
        app(CacheInvalidationService::class)->flushProducts();
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
     * @template TValue
     *
     * @param  array<int, string> $tags
     * @param  Closure(): TValue  $callback
     * @return TValue
     */
    private static function rememberWithTags(array $tags, string $key, int|DateInterval $ttl, Closure $callback)
    {
        if ($tags !== [] && Cache::supportsTags()) {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        }

        return Cache::remember($key, $ttl, $callback);
    }
}
