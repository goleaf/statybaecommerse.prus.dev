<?php

declare(strict_types=1);

namespace App\Services;

use ArrayAccess;
use Illuminate\Support\Collection;

/**
 * DataFilteringService
 *
 * Service class containing DataFilteringService business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class DataFilteringService
{
    /**
     * Handle filterQualityProducts functionality with proper error handling.
     */
    public function filterQualityProducts(Collection $products): Collection
    {
        return $products
            ->filter(function ($product) {
                // Keep only products that satisfy every quality requirement regardless of their position in the collection.
                $name = (string) ($this->extractValue($product, 'name') ?? '');
                $isVisible = (bool) ($this->extractValue($product, 'is_visible') ?? false);
                $price = (float) ($this->extractValue($product, 'price') ?? 0.0);
                $slug = (string) ($this->extractValue($product, 'slug') ?? '');
                $stock = (int) ($this->extractValue($product, 'stock_quantity') ?? 0);
                $isPublished = (bool) ($this->extractValue($product, 'is_published') ?? false);

                return $name !== '' && $isVisible && $price > 0 && $slug !== '' && $stock > 0 && $isPublished;
            })
            ->values(); // Reindex results so pagination helpers receive tidy sequential keys.
    }

    /**
     * Handle filterValidCollections functionality with proper error handling.
     */
    public function filterValidCollections(Collection $collections): Collection
    {
        return $collections
            ->filter(function ($collection) {
                // Retain only collections that have the required metadata and at least one product.
                $name = (string) ($this->extractValue($collection, 'name') ?? '');
                $isVisible = (bool) ($this->extractValue($collection, 'is_visible') ?? false);
                $slug = (string) ($this->extractValue($collection, 'slug') ?? '');
                $productsCount = (int) ($this->extractValue($collection, 'products_count') ?? 0);

                return $name !== '' && $isVisible && $slug !== '' && $productsCount > 0;
            })
            ->values();
    }

    /**
     * Handle filterRelevantResults functionality with proper error handling.
     */
    public function filterRelevantResults(Collection $results, float $minRelevanceScore = 0.5): Collection
    {
        return $results
            ->filter(function ($result) use ($minRelevanceScore) {
                // Keep results that meet or exceed the configured relevance threshold.
                $relevanceScore = (float) ($this->extractValue($result, 'relevance_score') ?? 0.0);

                return $relevanceScore >= $minRelevanceScore;
            })
            ->values();
    }

    /**
     * Handle filterNewRecommendations functionality with proper error handling.
     */
    public function filterNewRecommendations(Collection $recommendations, array $userInteractions = []): Collection
    {
        return $recommendations
            ->filter(function ($recommendation) use ($userInteractions) {
                // Keep recommendations for products the user has not interacted with.
                $productId = $this->extractValue($recommendation, 'id');

                if ($productId === null) {
                    return false;
                }

                return ! in_array($productId, $userInteractions, true);
            })
            ->values();
    }

    /**
     * Handle filterActiveCategories functionality with proper error handling.
     */
    public function filterActiveCategories(Collection $categories): Collection
    {
        return $categories
            ->filter(function ($category) {
                // Keep categories that are visible, named, and contain products.
                $isVisible = (bool) ($this->extractValue($category, 'is_visible') ?? false);
                $name = (string) ($this->extractValue($category, 'name') ?? '');
                $slug = (string) ($this->extractValue($category, 'slug') ?? '');
                $productsCount = (int) ($this->extractValue($category, 'products_count') ?? 0);

                return $isVisible && $name !== '' && $slug !== '' && $productsCount > 0;
            })
            ->values();
    }

    /**
     * Handle filterActiveBrands functionality with proper error handling.
     */
    public function filterActiveBrands(Collection $brands): Collection
    {
        return $brands
            ->filter(function ($brand) {
                // Keep brands that can be displayed on the storefront and contain products.
                $isVisible = (bool) ($this->extractValue($brand, 'is_visible') ?? false);
                $name = (string) ($this->extractValue($brand, 'name') ?? '');
                $slug = (string) ($this->extractValue($brand, 'slug') ?? '');
                $productsCount = (int) ($this->extractValue($brand, 'products_count') ?? 0);

                return $isVisible && $name !== '' && $slug !== '' && $productsCount > 0;
            })
            ->values();
    }

    /**
     * Handle filterActiveAttributes functionality with proper error handling.
     */
    public function filterActiveAttributes(Collection $attributes): Collection
    {
        return $attributes
            ->filter(function ($attribute) {
                // Keep attributes that are visible and have at least one configured value.
                $isVisible = (bool) ($this->extractValue($attribute, 'is_visible') ?? false);
                $name = (string) ($this->extractValue($attribute, 'name') ?? '');
                $slug = (string) ($this->extractValue($attribute, 'slug') ?? '');
                $valuesCount = (int) ($this->extractValue($attribute, 'values_count') ?? 0);

                return $isVisible && $name !== '' && $slug !== '' && $valuesCount > 0;
            })
            ->values();
    }

    /**
     * Handle filterProductsByPriceRange functionality with proper error handling.
     */
    public function filterProductsByPriceRange(Collection $products, float $minPrice = 0, ?float $maxPrice = null): Collection
    {
        return $products
            ->filter(function ($product) use ($minPrice, $maxPrice) {
                // Normalise the price regardless of whether we receive arrays, objects, or ArrayAccess instances.
                $price = (float) ($this->extractValue($product, 'price') ?? 0.0);

                // Include only products that fit within the defined price range.
                if ($price < $minPrice) {
                    return false;
                }

                if ($maxPrice !== null && $price > $maxPrice) {
                    return false;
                }

                return true;
            })
            ->values();
    }

    /**
     * Handle filterInStockProducts functionality with proper error handling.
     */
    public function filterInStockProducts(Collection $products): Collection
    {
        return $products
            ->filter(function ($product) {
                // Keep products with a positive stock quantity regardless of their placement in the dataset.
                $stockQuantity = (int) ($this->extractValue($product, 'stock_quantity') ?? 0);

                return $stockQuantity > 0;
            })
            ->values();
    }

    /**
     * Handle filterPublishedProducts functionality with proper error handling.
     */
    public function filterPublishedProducts(Collection $products): Collection
    {
        return $products
            ->filter(function ($product) {
                // Keep products that have been published and expose a publication timestamp.
                $isPublished = (bool) ($this->extractValue($product, 'is_published') ?? false);
                $publishedAt = $this->extractValue($product, 'published_at');

                return $isPublished && ! empty($publishedAt);
            })
            ->values();
    }

    /**
     * Handle filterWithMultipleCriteria functionality with proper error handling.
     */
    public function filterWithMultipleCriteria(Collection $items, array $criteria = []): Collection
    {
        return $items
            ->filter(function ($item) use ($criteria) {
                // Keep items that meet every provided criterion, even when invalid entries are interleaved.
                foreach ($criteria as $field => $condition) {
                    $value = $this->extractValue($item, $field);
                    if (is_array($condition)) {
                        if (array_key_exists('min', $condition) && $value < $condition['min']) {
                            return false;
                        }
                        if (array_key_exists('max', $condition) && $value > $condition['max']) {
                            return false;
                        }
                        if (array_key_exists('in', $condition) && ! in_array($value, $condition['in'])) {
                            return false;
                        }
                        if (array_key_exists('not_in', $condition) && in_array($value, $condition['not_in'])) {
                            return false;
                        }
                    } elseif ($value !== $condition) {
                        return false;
                    }
                }

                return true;
            })
            ->values();
    }

    private function extractValue(mixed $item, string $key): mixed
    {
        // Gracefully support array-like, ArrayAccess, and object payloads that surface throughout the service test suite.
        if (is_array($item)) {
            return $item[$key] ?? null;
        }

        if ($item instanceof ArrayAccess) {
            return $item->offsetExists($key) ? $item[$key] : null;
        }

        if (is_object($item)) {
            return $item->{$key} ?? null;
        }

        return null;
    }
}
