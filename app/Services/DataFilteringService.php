<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;

final class DataFilteringService
{
    /**
     * Filter products by skipping items that don't meet quality criteria
     */
    public function filterQualityProducts(Collection $products): Collection
    {
        return $products->skipWhile(function ($product) {
            // Skip products that don't meet quality standards
            return empty($product->name) || 
                   !$product->is_visible ||
                   $product->price <= 0 ||
                   empty($product->slug) ||
                   $product->stock_quantity <= 0 ||
                   !$product->is_published;
        });
    }

    /**
     * Filter collections by skipping empty or invalid collections
     */
    public function filterValidCollections(Collection $collections): Collection
    {
        return $collections->skipWhile(function ($collection) {
            // Skip collections that are not properly configured
            return empty($collection->name) || 
                   !$collection->is_visible ||
                   empty($collection->slug) ||
                   $collection->products_count <= 0;
        });
    }

    /**
     * Filter search results by skipping low-relevance items
     */
    public function filterRelevantResults(Collection $results, float $minRelevanceScore = 0.5): Collection
    {
        return $results->skipWhile(function ($result) use ($minRelevanceScore) {
            // Skip results with low relevance scores
            $relevanceScore = $result['relevance_score'] ?? 0;
            return $relevanceScore < $minRelevanceScore;
        });
    }

    /**
     * Filter user recommendations by skipping items user has already interacted with
     */
    public function filterNewRecommendations(Collection $recommendations, array $userInteractions = []): Collection
    {
        return $recommendations->skipWhile(function ($recommendation) use ($userInteractions) {
            // Skip recommendations for products user has already viewed/purchased
            $productId = $recommendation->id ?? $recommendation['id'] ?? null;
            return in_array($productId, $userInteractions);
        });
    }

    /**
     * Filter categories by skipping those without products
     */
    public function filterActiveCategories(Collection $categories): Collection
    {
        return $categories->skipWhile(function ($category) {
            // Skip categories that have no products or are not visible
            return !$category->is_visible ||
                   empty($category->name) ||
                   empty($category->slug) ||
                   $category->products_count <= 0;
        });
    }

    /**
     * Filter brands by skipping those without products
     */
    public function filterActiveBrands(Collection $brands): Collection
    {
        return $brands->skipWhile(function ($brand) {
            // Skip brands that have no products or are not visible
            return !$brand->is_visible ||
                   empty($brand->name) ||
                   empty($brand->slug) ||
                   $brand->products_count <= 0;
        });
    }

    /**
     * Filter attributes by skipping those without values
     */
    public function filterActiveAttributes(Collection $attributes): Collection
    {
        return $attributes->skipWhile(function ($attribute) {
            // Skip attributes that have no values or are not visible
            return !$attribute->is_visible ||
                   empty($attribute->name) ||
                   empty($attribute->slug) ||
                   $attribute->values_count <= 0;
        });
    }

    /**
     * Filter products by price range, skipping those outside the range
     */
    public function filterProductsByPriceRange(Collection $products, float $minPrice = 0, float $maxPrice = null): Collection
    {
        return $products->filter(function ($product) use ($minPrice, $maxPrice) {
            $price = $product->price ?? $product['price'] ?? 0;
            
            // Include products within price range
            if ($price < $minPrice) {
                return false;
            }
            
            if ($maxPrice !== null && $price > $maxPrice) {
                return false;
            }
            
            return true;
        });
    }

    /**
     * Filter products by stock availability, skipping out-of-stock items
     */
    public function filterInStockProducts(Collection $products): Collection
    {
        return $products->skipWhile(function ($product) {
            // Skip products that are out of stock
            $stockQuantity = $product->stock_quantity ?? $product['stock_quantity'] ?? 0;
            return $stockQuantity <= 0;
        });
    }

    /**
     * Filter products by publication status, skipping unpublished items
     */
    public function filterPublishedProducts(Collection $products): Collection
    {
        return $products->skipWhile(function ($product) {
            // Skip products that are not published
            return !($product->is_published ?? $product['is_published'] ?? false) ||
                   empty($product->published_at ?? $product['published_at'] ?? null);
        });
    }

    /**
     * Advanced filtering with multiple criteria using skipWhile
     */
    public function filterWithMultipleCriteria(Collection $items, array $criteria = []): Collection
    {
        return $items->skipWhile(function ($item) use ($criteria) {
            // Apply multiple filtering criteria
            foreach ($criteria as $field => $condition) {
                $value = $item->{$field} ?? $item[$field] ?? null;
                
                if (is_array($condition)) {
                    // Array condition: ['min' => 0, 'max' => 100]
                    if (isset($condition['min']) && $value < $condition['min']) {
                        return true;
                    }
                    if (isset($condition['max']) && $value > $condition['max']) {
                        return true;
                    }
                    if (isset($condition['in']) && !in_array($value, $condition['in'])) {
                        return true;
                    }
                    if (isset($condition['not_in']) && in_array($value, $condition['not_in'])) {
                        return true;
                    }
                } else {
                    // Simple condition: exact match or boolean check
                    if ($value !== $condition) {
                        return true;
                    }
                }
            }
            
            return false;
        });
    }
}
