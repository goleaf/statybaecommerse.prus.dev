<?php

declare(strict_types=1);

namespace App\Services\Frontend;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Services\Shared\CacheService;
use App\Services\Shared\ProductService;
use App\Support\Cache\CacheKeys;
use Illuminate\Support\Collection;

final class HomepageDataProvider
{
    public function __construct(
        private readonly CacheService $cacheService,
        private readonly ProductService $productService,
    ) {}

    public function stats(): array
    {
        $locale = app()->getLocale();

        return $this->cacheService->rememberShort(
            CacheKeys::homeStats($locale),
            static function (): array {
                return [
                    'products' => Product::query()->where('is_visible', true)->count(),
                    'categories' => Category::query()->where('is_visible', true)->count(),
                    'brands' => Brand::query()->where('is_enabled', true)->count(),
                    'reviews' => Review::query()->where('is_approved', true)->count(),
                    'average_rating' => (float) (Review::query()->where('is_approved', true)->avg('rating') ?? 0.0),
                ];
            }
        );
    }

    public function featuredProducts(int $limit = 8): Collection
    {
        return $this->productService->getFeaturedProducts($limit);
    }

    public function newArrivals(int $limit = 8): Collection
    {
        return $this->productService->getNewArrivals($limit);
    }

    public function trendingProducts(int $limit = 8): Collection
    {
        $locale = app()->getLocale();
        $currency = current_currency();

        return $this->cacheService->rememberShort(
            $this->cacheService->generateHomeKey('trending_products', $locale, $currency),
            static function () use ($limit, $locale): Collection {
                return Product::query()
                    ->with([
                        'brand',
                        'media',
                        'categories',
                        'translations' => static fn ($query) => $query->where('locale', $locale),
                        'prices.currency',
                    ])
                    ->withSum('orderItems as orders_quantity', 'quantity')
                    ->withAvg('reviews', 'rating')
                    ->where('is_visible', true)
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->orderByDesc('orders_quantity')
                    ->orderByDesc('reviews_avg_rating')
                    ->orderByDesc('published_at')
                    ->limit($limit)
                    ->get();
            }
        );
    }

    public function topCategories(int $limit = 6): Collection
    {
        $locale = app()->getLocale();

        return $this->cacheService->rememberLong(
            $this->cacheService->generateHomeKey('top_categories', $locale),
            static function () use ($limit, $locale): Collection {
                return Category::query()
                    ->with(['translations' => static fn ($query) => $query->where('locale', $locale), 'media'])
                    ->withCount('products')
                    ->where('is_visible', true)
                    ->orderByDesc('products_count')
                    ->orderBy('name')
                    ->limit($limit)
                    ->get();
            }
        );
    }

    public function topBrands(int $limit = 6): Collection
    {
        $locale = app()->getLocale();

        return $this->cacheService->rememberLong(
            $this->cacheService->generateHomeKey('top_brands', $locale),
            static function () use ($limit, $locale): Collection {
                return Brand::query()
                    ->with(['translations' => static fn ($query) => $query->where('locale', $locale)])
                    ->withCount('products')
                    ->where('is_enabled', true)
                    ->where('is_visible', true)
                    ->orderByDesc('products_count')
                    ->orderBy('name')
                    ->limit($limit)
                    ->get();
            }
        );
    }
}
