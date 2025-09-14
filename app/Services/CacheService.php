<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final /**
 * CacheService
 * 
 * Service class containing business logic and external integrations.
 */
class CacheService
{
    private const TTL = 3600;  // 1 hour

    public static function getFeaturedProducts(int $limit = 8): Collection
    {
        return Cache::remember(
            "featured_products_{$limit}",
            self::TTL,
            fn () => Product::where('is_featured', true)
                ->where('is_visible', true)
                ->with(['brand', 'categories', 'media'])
                ->limit($limit)
                ->get()
        );
    }

    public static function getPopularCategories(int $limit = 6): Collection
    {
        return Cache::remember(
            "popular_categories_{$limit}",
            self::TTL,
            fn () => Category::where('is_visible', true)
                ->where('is_featured', true)
                ->with(['media'])
                ->withCount('products')
                ->orderBy('products_count', 'desc')
                ->limit($limit)
                ->get()
        );
    }

    public static function getTopBrands(int $limit = 10): Collection
    {
        return Cache::remember(
            "top_brands_{$limit}",
            self::TTL,
            fn () => Brand::where('is_visible', true)
                ->where('is_featured', true)
                ->with(['media'])
                ->withCount('products')
                ->orderBy('products_count', 'desc')
                ->limit($limit)
                ->get()
        );
    }

    public static function getNavigationCategories(): Collection
    {
        return Cache::remember(
            'navigation_categories',
            self::TTL * 24,  // 24 hours
            fn () => Category::where('is_visible', true)
                ->whereNull('parent_id')
                ->with(['children' => function ($query) {
                    $query
                        ->where('is_visible', true)
                        ->orderBy('sort_order')
                        ->orderBy('name');
                }])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
        );
    }

    public static function clearProductCaches(): void
    {
        Cache::forget('featured_products_8');
        Cache::forget('popular_categories_6');
        Cache::forget('top_brands_10');
        Cache::forget('navigation_categories');
    }

    public static function warmupCaches(): void
    {
        self::getFeaturedProducts();
        self::getPopularCategories();
        self::getTopBrands();
        self::getNavigationCategories();
    }
}
