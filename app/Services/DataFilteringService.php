<?php

declare (strict_types=1);
namespace App\Services;

use Illuminate\Support\Collection;
/**
 * DataFilteringService
 * 
 * Service class containing DataFilteringService business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 */
final class DataFilteringService
{
    /**
     * Handle filterQualityProducts functionality with proper error handling.
     * @param Collection $products
     * @return Collection
     */
    public function filterQualityProducts(Collection $products): Collection
    {
        return $products->skipWhile(function ($product) {
            // Skip products that don't meet quality standards
            return empty($product->name) || !$product->is_visible || $product->price <= 0 || empty($product->slug) || $product->stock_quantity <= 0 || !$product->is_published;
        });
    }
    /**
     * Handle filterValidCollections functionality with proper error handling.
     * @param Collection $collections
     * @return Collection
     */
    public function filterValidCollections(Collection $collections): Collection
    {
        return $collections->skipWhile(function ($collection) {
            // Skip collections that are not properly configured
            return empty($collection->name) || !$collection->is_visible || empty($collection->slug) || $collection->products_count <= 0;
        });
    }
    /**
     * Handle filterRelevantResults functionality with proper error handling.
     * @param Collection $results
     * @param float $minRelevanceScore
     * @return Collection
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
     * Handle filterNewRecommendations functionality with proper error handling.
     * @param Collection $recommendations
     * @param array $userInteractions
     * @return Collection
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
     * Handle filterActiveCategories functionality with proper error handling.
     * @param Collection $categories
     * @return Collection
     */
    public function filterActiveCategories(Collection $categories): Collection
    {
        return $categories->skipWhile(function ($category) {
            // Skip categories that have no products or are not visible
            return !$category->is_visible || empty($category->name) || empty($category->slug) || $category->products_count <= 0;
        });
    }
    /**
     * Handle filterActiveBrands functionality with proper error handling.
     * @param Collection $brands
     * @return Collection
     */
    public function filterActiveBrands(Collection $brands): Collection
    {
        return $brands->skipWhile(function ($brand) {
            // Skip brands that have no products or are not visible
            return !$brand->is_visible || empty($brand->name) || empty($brand->slug) || $brand->products_count <= 0;
        });
    }
    /**
     * Handle filterActiveAttributes functionality with proper error handling.
     * @param Collection $attributes
     * @return Collection
     */
    public function filterActiveAttributes(Collection $attributes): Collection
    {
        return $attributes->skipWhile(function ($attribute) {
            // Skip attributes that have no values or are not visible
            return !$attribute->is_visible || empty($attribute->name) || empty($attribute->slug) || $attribute->values_count <= 0;
        });
    }
    /**
     * Handle filterProductsByPriceRange functionality with proper error handling.
     * @param Collection $products
     * @param float $minPrice
     * @param float $maxPrice
     * @return Collection
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
     * Handle filterInStockProducts functionality with proper error handling.
     * @param Collection $products
     * @return Collection
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
     * Handle filterPublishedProducts functionality with proper error handling.
     * @param Collection $products
     * @return Collection
     */
    public function filterPublishedProducts(Collection $products): Collection
    {
        return $products->skipWhile(function ($product) {
            // Skip products that are not published
            return !($product->is_published ?? $product['is_published'] ?? false) || empty($product->published_at ?? $product['published_at'] ?? null);
        });
    }
    /**
     * Handle filterWithMultipleCriteria functionality with proper error handling.
     * @param Collection $items
     * @param array $criteria
     * @return Collection
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
                } else if ($value !== $condition) {
                    return true;
                }
            }
            return false;
        });
    }
}