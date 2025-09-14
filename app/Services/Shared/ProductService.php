<?php

declare (strict_types=1);
namespace App\Services\Shared;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
/**
 * ProductService
 * 
 * Service class containing ProductService business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 */
final class ProductService
{
    /**
     * Initialize the class instance with required dependencies.
     * @param CacheService $cacheService
     */
    public function __construct(private CacheService $cacheService)
    {
    }
    /**
     * Handle getFeaturedProducts functionality with proper error handling.
     * @param int $limit
     * @return Collection
     */
    public function getFeaturedProducts(int $limit = 8): Collection
    {
        $locale = app()->getLocale();
        $currency = current_currency();
        return $this->cacheService->rememberDefault("featured_products.{$locale}.{$currency}", function () use ($limit) {
            return Product::query()->with($this->getProductRelations())->where('is_visible', true)->where('is_featured', true)->whereNotNull('published_at')->latest('published_at')->limit($limit)->get();
        });
    }
    /**
     * Handle getNewArrivals functionality with proper error handling.
     * @param int $limit
     * @param int $days
     * @return Collection
     */
    public function getNewArrivals(int $limit = 12, int $days = 30): Collection
    {
        $locale = app()->getLocale();
        $currency = current_currency();
        return $this->cacheService->rememberShort("new_arrivals.{$locale}.{$currency}", function () use ($limit, $days) {
            return Product::query()->with($this->getProductRelations())->where('is_visible', true)->whereNotNull('published_at')->where('published_at', '>=', now()->subDays($days))->latest('published_at')->limit($limit)->get();
        });
    }
    /**
     * Handle searchProducts functionality with proper error handling.
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchProducts(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Product::query()->with($this->getProductRelations())->where('is_visible', true)->whereNotNull('published_at')->where('published_at', '<=', now());
        $query = $this->applyFilters($query, $filters);
        $query = $this->applySorting($query, $filters['sort_by'] ?? 'created_at', $filters['sort_direction'] ?? 'desc');
        return $query->paginate($perPage);
    }
    /**
     * Handle getProductsByCategory functionality with proper error handling.
     * @param int $categoryId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getProductsByCategory(int $categoryId, array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Product::query()->with($this->getProductRelations())->whereHas('categories', function ($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        })->where('is_visible', true)->whereNotNull('published_at')->where('published_at', '<=', now());
        $query = $this->applyFilters($query, $filters);
        $query = $this->applySorting($query, $filters['sort_by'] ?? 'created_at', $filters['sort_direction'] ?? 'desc');
        return $query->paginate($perPage);
    }
    /**
     * Handle getProductsByBrand functionality with proper error handling.
     * @param int $brandId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getProductsByBrand(int $brandId, array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Product::query()->with($this->getProductRelations())->where('brand_id', $brandId)->where('is_visible', true)->whereNotNull('published_at')->where('published_at', '<=', now());
        $query = $this->applyFilters($query, $filters);
        $query = $this->applySorting($query, $filters['sort_by'] ?? 'created_at', $filters['sort_direction'] ?? 'desc');
        return $query->paginate($perPage);
    }
    /**
     * Handle getProductsByCollection functionality with proper error handling.
     * @param int $collectionId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getProductsByCollection(int $collectionId, array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Product::query()->with($this->getProductRelations())->whereHas('collections', function ($q) use ($collectionId) {
            $q->where('collections.id', $collectionId);
        })->where('is_visible', true)->whereNotNull('published_at')->where('published_at', '<=', now());
        $query = $this->applyFilters($query, $filters);
        $query = $this->applySorting($query, $filters['sort_by'] ?? 'created_at', $filters['sort_direction'] ?? 'desc');
        return $query->paginate($perPage);
    }
    /**
     * Handle getRelatedProducts functionality with proper error handling.
     * @param Product $product
     * @param int $limit
     * @return Collection
     */
    public function getRelatedProducts(Product $product, int $limit = 4): Collection
    {
        $locale = app()->getLocale();
        $currency = current_currency();
        return $this->cacheService->rememberDefault("related_products.{$product->id}.{$locale}.{$currency}", function () use ($product, $limit) {
            return Product::query()->with($this->getProductRelations())->where('id', '!=', $product->id)->where('is_visible', true)->where(function ($query) use ($product) {
                // Same category or brand
                $query->whereHas('categories', function ($q) use ($product) {
                    $q->whereIn('categories.id', $product->categories->pluck('id'));
                })->orWhere('brand_id', $product->brand_id);
            })->inRandomOrder()->limit($limit)->get();
        });
    }
    /**
     * Handle getProductRelations functionality with proper error handling.
     * @return array
     */
    private function getProductRelations(): array
    {
        $locale = app()->getLocale();
        $currency = current_currency();
        return ['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }, 'brand:id,slug,name', 'brand.translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }, 'media', 'prices' => function ($pq) use ($currency) {
            $pq->whereRelation('currency', 'code', $currency);
        }, 'prices.currency:id,code,symbol', 'categories:id,name,slug', 'reviews' => function ($q) {
            $q->where('is_approved', true);
        }];
    }
    /**
     * Handle applyFilters functionality with proper error handling.
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')->orWhere('summary', 'like', '%' . $filters['search'] . '%')->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }
        if (!empty($filters['categories'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->whereIn('categories.id', $filters['categories']);
            });
        }
        if (!empty($filters['brands'])) {
            $query->whereIn('brand_id', $filters['brands']);
        }
        if (isset($filters['min_price'])) {
            $query->whereHas('prices', function ($q) use ($filters) {
                $q->where('amount', '>=', $filters['min_price']);
            });
        }
        if (isset($filters['max_price'])) {
            $query->whereHas('prices', function ($q) use ($filters) {
                $q->where('amount', '<=', $filters['max_price']);
            });
        }
        if ($filters['in_stock'] ?? false) {
            $query->where(function ($q) {
                $q->whereNull('stock_quantity')->orWhere('stock_quantity', '>', 0);
            });
        }
        if ($filters['on_sale'] ?? false) {
            $query->whereNotNull('sale_price')->where('sale_price', '>', 0);
        }
        return $query;
    }
    /**
     * Handle applySorting functionality with proper error handling.
     * @param Builder $query
     * @param string $sortBy
     * @param string $direction
     * @return Builder
     */
    private function applySorting(Builder $query, string $sortBy, string $direction = 'desc'): Builder
    {
        match ($sortBy) {
            'name' => $query->orderBy('name', $direction),
            'price' => $query->orderBy('price', $direction),
            'created_at' => $query->orderBy('created_at', $direction),
            'updated_at' => $query->orderBy('updated_at', $direction),
            'popularity' => $query->withCount('orderItems')->orderBy('order_items_count', $direction),
            'rating' => $query->withAvg('reviews', 'rating')->orderBy('reviews_avg_rating', $direction),
            default => $query->orderBy('created_at', $direction),
        };
        return $query;
    }
}