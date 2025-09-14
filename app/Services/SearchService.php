<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\LazyCollection;

final /**
 * SearchService
 * 
 * Service class containing business logic and external integrations.
 */
class SearchService
{
    public function search(string $query, int $limit = 10): array
    {
        $cacheKey = "search_results_{$query}_{$limit}_" . app()->getLocale();
        
        return Cache::remember($cacheKey, 300, function () use ($query, $limit) {
            $results = [];
            
            // Search products
            $products = $this->searchProducts($query, (int) ceil($limit * 0.6));
            $results = array_merge($results, $products);
            
            // Search categories
            $categories = $this->searchCategories($query, (int) ceil($limit * 0.2));
            $results = array_merge($results, $categories);
            
            // Search brands
            $brands = $this->searchBrands($query, (int) ceil($limit * 0.2));
            $results = array_merge($results, $brands);
            
            // Sort by relevance and limit results
            return array_slice($results, 0, $limit);
        });
    }

    private function searchProducts(string $query, int $limit): array
    {
        $locale = app()->getLocale();
        $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], $query) . '%';

        // Use LazyCollection with timeout to prevent long-running search operations
        $timeout = now()->addSeconds(10); // 10 second timeout for product search

        return Product::query()
            ->with(['media', 'brand', 'categories'])
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function ($q) use ($searchTerm, $locale) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    ->orWhere('sku', 'like', $searchTerm)
                    ->orWhereExists(function ($sq) use ($searchTerm, $locale) {
                        $sq->selectRaw('1')
                            ->from('product_translations as t')
                            ->whereColumn('t.product_id', 'products.id')
                            ->where('t.locale', $locale)
                            ->where(function ($tw) use ($searchTerm) {
                                $tw->where('t.name', 'like', $searchTerm)
                                    ->orWhere('t.description', 'like', $searchTerm);
                            });
                    });
            })
            ->orderByRaw("
                CASE 
                    WHEN name LIKE ? THEN 1
                    WHEN sku LIKE ? THEN 2
                    WHEN description LIKE ? THEN 3
                    ELSE 4
                END
            ", ["%{$query}%", "%{$query}%", "%{$query}%"])
            ->cursor()
            ->takeUntilTimeout($timeout)
            ->take($limit)
            ->map(function (Product $product) use ($query) {
                return [
                    'id' => $product->id,
                    'type' => 'product',
                    'title' => $product->name,
                    'subtitle' => $product->brand?->name,
                    'description' => $product->short_description ?: $product->description,
                    'price' => $product->price,
                    'formatted_price' => number_format((float) $product->price, 2) . ' €',
                    'image' => $product->getFirstMediaUrl('images', 'thumb'),
                    'url' => route('products.show', $product->slug),
                    'relevance_score' => $this->calculateProductRelevance($product, $query),
                ];
            })
            ->sortByDesc('relevance_score')
            ->values()
            ->toArray();
    }

    private function searchCategories(string $query, int $limit): array
    {
        $locale = app()->getLocale();
        $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], $query) . '%';

        return Category::query()
            ->with(['media'])
            ->where('is_visible', true)
            ->where(function ($q) use ($searchTerm, $locale) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    ->orWhereExists(function ($sq) use ($searchTerm, $locale) {
                        $sq->selectRaw('1')
                            ->from('category_translations as t')
                            ->whereColumn('t.category_id', 'categories.id')
                            ->where('t.locale', $locale)
                            ->where(function ($tw) use ($searchTerm) {
                                $tw->where('t.name', 'like', $searchTerm)
                                    ->orWhere('t.description', 'like', $searchTerm);
                            });
                    });
            })
            ->withCount('products')
            ->groupBy('categories.id')
            ->having('products_count', '>', 0)
            ->limit($limit)
            ->get()
            ->map(function (Category $category) use ($query) {
                return [
                    'id' => $category->id,
                    'type' => 'category',
                    'title' => $category->name,
                    'subtitle' => __('frontend.search.category_with_products', ['count' => $category->products_count]),
                    'description' => $category->description,
                    'image' => $category->getFirstMediaUrl('images', 'thumb'),
                    'url' => route('categories.show', $category->slug),
                    'relevance_score' => $this->calculateCategoryRelevance($category, $query),
                ];
            })
            ->sortByDesc('relevance_score')
            ->values()
            ->toArray();
    }

    private function searchBrands(string $query, int $limit): array
    {
        $locale = app()->getLocale();
        $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], $query) . '%';

        return Brand::query()
            ->with(['media'])
            ->where('is_enabled', true)
            ->where(function ($q) use ($searchTerm, $locale) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    ->orWhereExists(function ($sq) use ($searchTerm, $locale) {
                        $sq->selectRaw('1')
                            ->from('brand_translations as t')
                            ->whereColumn('t.brand_id', 'brands.id')
                            ->where('t.locale', $locale)
                            ->where(function ($tw) use ($searchTerm) {
                                $tw->where('t.name', 'like', $searchTerm)
                                    ->orWhere('t.description', 'like', $searchTerm);
                            });
                    });
            })
            ->withCount('products')
            ->groupBy('brands.id')
            ->having('products_count', '>', 0)
            ->limit($limit)
            ->get()
            ->map(function (Brand $brand) use ($query) {
                return [
                    'id' => $brand->id,
                    'type' => 'brand',
                    'title' => $brand->name,
                    'subtitle' => __('frontend.search.brand_with_products', ['count' => $brand->products_count]),
                    'description' => $brand->description,
                    'image' => $brand->getFirstMediaUrl('logo', 'thumb'),
                    'url' => route('brands.show', $brand->slug),
                    'relevance_score' => $this->calculateBrandRelevance($brand, $query),
                ];
            })
            ->sortByDesc('relevance_score')
            ->values()
            ->toArray();
    }

    private function calculateProductRelevance(Product $product, string $query): int
    {
        $score = 0;
        $query = strtolower($query);
        
        // Exact name match gets highest score
        if (strtolower($product->name) === $query) {
            $score += 100;
        } elseif (str_contains(strtolower($product->name), $query)) {
            $score += 50;
        }
        
        // SKU match gets high score
        if (str_contains(strtolower($product->sku), $query)) {
            $score += 40;
        }
        
        // Description match gets medium score
        if (str_contains(strtolower($product->description), $query)) {
            $score += 20;
        }
        
        // Featured products get bonus
        if ($product->is_featured) {
            $score += 10;
        }
        
        // Products with images get bonus
        if ($product->hasMedia('images')) {
            $score += 5;
        }
        
        return $score;
    }

    private function calculateCategoryRelevance(Category $category, string $query): int
    {
        $score = 0;
        $query = strtolower($query);
        
        // Exact name match gets highest score
        if (strtolower($category->name) === $query) {
            $score += 100;
        } elseif (str_contains(strtolower($category->name), $query)) {
            $score += 50;
        }
        
        // Description match gets medium score
        if (str_contains(strtolower($category->description), $query)) {
            $score += 20;
        }
        
        // Categories with more products get bonus
        $score += min($category->products_count, 20);
        
        return $score;
    }

    private function calculateBrandRelevance(Brand $brand, string $query): int
    {
        $score = 0;
        $query = strtolower($query);
        
        // Exact name match gets highest score
        if (strtolower($brand->name) === $query) {
            $score += 100;
        } elseif (str_contains(strtolower($brand->name), $query)) {
            $score += 50;
        }
        
        // Description match gets medium score
        if (str_contains(strtolower($brand->description), $query)) {
            $score += 20;
        }
        
        // Brands with more products get bonus
        $score += min($brand->products_count, 20);
        
        return $score;
    }

    public function clearCache(): void
    {
        Cache::flush();
    }

    public function clearSearchCache(string $query): void
    {
        $pattern = "search_results_{$query}_*";
        // Note: This is a simplified cache clearing. In production, you might want to use Redis with pattern matching
        Cache::forget("search_results_{$query}_10_" . app()->getLocale());
    }
}
