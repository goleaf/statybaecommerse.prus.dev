<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * ProductGalleryService
 *
 * Service class containing ProductGalleryService business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class ProductGalleryService
{
    /**
     * Handle arrangeForGallery functionality with proper error handling.
     */
    public function arrangeForGallery(Collection $products, int $columnCount = 4): Collection
    {
        // Use skipWhile to filter out invalid products from the beginning
        $validProducts = $products->skipWhile(function ($product) {
            return empty($product->name) || property_exists($product, 'is_visible') && ! $product->is_visible || property_exists($product, 'price') && $product->price <= 0 || property_exists($product, 'slug') && empty($product->slug);
        });
        $productColumns = $validProducts->splitIn($columnCount);

        return $productColumns->map(function ($columnProducts, $columnIndex) {
            return ['column_id' => $columnIndex + 1, 'item_count' => $columnProducts->count(), 'products' => $columnProducts->map(fn ($product) => ['id' => $product->id ?? null, 'name' => $product->name ?? 'Unknown', 'slug' => $product->slug ?? 'unknown', 'price' => $product->formatted_price ?? $product->price ?? 0, 'image_url' => method_exists($product, 'getFirstMediaUrl') ? $product->getFirstMediaUrl('images') : null, 'category' => $product->category?->name ?? null, 'is_featured' => $product->is_featured ?? false])];
        });
    }

    /**
     * Handle arrangeForMasonry functionality with proper error handling.
     */
    public function arrangeForMasonry(Collection $products, int $columns = 3): Collection
    {
        // Use skipWhile to filter out invalid products from the beginning
        $validProducts = $products->skipWhile(function ($product) {
            return empty($product->name) || property_exists($product, 'is_visible') && ! $product->is_visible || property_exists($product, 'price') && $product->price <= 0 || property_exists($product, 'slug') && empty($product->slug);
        });

        return $validProducts->splitIn($columns);
    }

    /**
     * Handle arrangeForResponsiveGrid functionality with proper error handling.
     */
    public function arrangeForResponsiveGrid(Collection $products, string $breakpoint = 'lg'): Collection
    {
        $columnCount = match ($breakpoint) {
            'sm' => 2,
            'md' => 3,
            'lg' => 4,
            'xl' => 5,
            '2xl' => 6,
            default => 4,
        };

        return $this->arrangeForGallery($products, $columnCount);
    }

    /**
     * Handle arrangeForCategoryShowcase functionality with proper error handling.
     */
    public function arrangeForCategoryShowcase(Collection $products, int $featuredCount = 8): array
    {
        // Use skipWhile to filter out invalid products from the beginning
        $validProducts = $products->skipWhile(function ($product) {
            return empty($product->name) || property_exists($product, 'is_visible') && ! $product->is_visible || property_exists($product, 'price') && $product->price <= 0 || property_exists($product, 'slug') && empty($product->slug);
        });
        $featuredProducts = $validProducts->take($featuredCount);
        $remainingProducts = $validProducts->skip($featuredCount);

        return ['featured' => $this->arrangeForGallery($featuredProducts, 4), 'remaining' => $remainingProducts->count() > 0 ? $this->arrangeForGallery($remainingProducts, 3) : collect(), 'total_count' => $validProducts->count(), 'featured_count' => $featuredProducts->count(), 'remaining_count' => $remainingProducts->count()];
    }

    /**
     * Handle arrangeForCollection functionality with proper error handling.
     */
    public function arrangeForCollection(Collection $products, int $displayType = 1): Collection
    {
        $columnCount = match ($displayType) {
            1 => 4,
            // Grid
            2 => 3,
            // Masonry
            3 => 2,
            // List
            4 => 5,
            // Carousel
            default => 4,
        };

        return $this->arrangeForGallery($products, $columnCount);
    }

    /**
     * Handle arrangeForSearchResults functionality with proper error handling.
     */
    public function arrangeForSearchResults(Collection $products, int $resultsPerPage = 20): Collection
    {
        $columnCount = match (true) {
            $resultsPerPage <= 12 => 3,
            $resultsPerPage <= 20 => 4,
            $resultsPerPage <= 30 => 5,
            default => 4,
        };

        return $this->arrangeForGallery($products, $columnCount);
    }

    /**
     * Handle arrangeForRelatedProducts functionality with proper error handling.
     */
    public function arrangeForRelatedProducts(Collection $products, int $maxProducts = 8): Collection
    {
        $limitedProducts = $products->take($maxProducts);

        return $this->arrangeForGallery($limitedProducts, 4);
    }

    /**
     * Handle arrangeForHomepageFeatured functionality with proper error handling.
     */
    public function arrangeForHomepageFeatured(Collection $products): array
    {
        // Use skipWhile to filter out invalid products from the beginning
        $validProducts = $products->skipWhile(function ($product) {
            return empty($product->name) || property_exists($product, 'is_visible') && ! $product->is_visible || property_exists($product, 'price') && $product->price <= 0 || property_exists($product, 'slug') && empty($product->slug);
        });
        $heroProducts = $validProducts->take(1);
        $featuredProducts = $validProducts->skip(1)->take(6);
        $additionalProducts = $validProducts->skip(7)->take(8);

        return ['hero' => $heroProducts->first(), 'featured' => $this->arrangeForGallery($featuredProducts, 3), 'additional' => $this->arrangeForGallery($additionalProducts, 4), 'total_count' => $validProducts->count()];
    }

    /**
     * Handle arrangeForMobile functionality with proper error handling.
     */
    public function arrangeForMobile(Collection $products): Collection
    {
        return $this->arrangeForGallery($products, 2);
    }

    /**
     * Handle arrangeForTablet functionality with proper error handling.
     */
    public function arrangeForTablet(Collection $products): Collection
    {
        return $this->arrangeForGallery($products, 3);
    }

    /**
     * Handle arrangeForDesktop functionality with proper error handling.
     */
    public function arrangeForDesktop(Collection $products): Collection
    {
        return $this->arrangeForGallery($products, 4);
    }

    /**
     * Handle arrangeWithAdvancedFiltering functionality with proper error handling.
     */
    public function arrangeWithAdvancedFiltering(Collection $products, array $filters = []): Collection
    {
        $minPrice = $filters['min_price'] ?? 0;
        $maxPrice = $filters['max_price'] ?? null;
        $minRating = $filters['min_rating'] ?? 0;
        $hasImages = $filters['has_images'] ?? true;
        $isFeatured = $filters['is_featured'] ?? null;

        return $products->skipWhile(function ($product) use ($minPrice, $maxPrice, $minRating, $hasImages, $isFeatured) {
            // Skip products that don't meet basic requirements
            if (empty($product->name) || ! $product->is_visible || empty($product->slug)) {
                return true;
            }
            // Skip products with invalid prices
            if ($product->price <= 0 || $product->price < $minPrice) {
                return true;
            }
            // Skip products above maximum price
            if ($maxPrice !== null && $product->price > $maxPrice) {
                return true;
            }
            // Skip products without images if required
            if ($hasImages && ! $product->getFirstMediaUrl('images')) {
                return true;
            }
            // Skip products based on featured status
            if ($isFeatured !== null && $product->is_featured !== $isFeatured) {
                return true;
            }
            // Skip products with low ratings
            if ($minRating > 0 && ($product->average_rating ?? 0) < $minRating) {
                return true;
            }

            return false;
        });
    }

    /**
     * Handle arrangeWithQualityFiltering functionality with proper error handling.
     */
    public function arrangeWithQualityFiltering(Collection $products, int $columnCount = 4, float $qualityThreshold = 0.7): Collection
    {
        $qualityProducts = $products->skipWhile(function ($product) use ($qualityThreshold) {
            $qualityScore = $this->calculateProductQualityScore($product);

            return $qualityScore < $qualityThreshold;
        });

        return $this->arrangeForGallery($qualityProducts, $columnCount);
    }

    /**
     * Handle calculateProductQualityScore functionality with proper error handling.
     *
     * @param  mixed  $product
     */
    private function calculateProductQualityScore($product): float
    {
        $score = 0.0;
        // Basic requirements (40% of score)
        if (! empty($product->name)) {
            $score += 0.1;
        }
        if (! empty($product->slug)) {
            $score += 0.1;
        }
        if ($product->is_visible) {
            $score += 0.1;
        }
        if ($product->price > 0) {
            $score += 0.1;
        }
        // Media quality (30% of score)
        if (method_exists($product, 'getFirstMediaUrl') && $product->getFirstMediaUrl('images')) {
            $score += 0.2;
        }
        if (method_exists($product, 'getFirstMediaUrl') && $product->getFirstMediaUrl('images', 'large')) {
            $score += 0.1;
        }
        // Content quality (20% of score)
        if (! empty($product->description)) {
            $score += 0.1;
        }
        if ($product->is_featured) {
            $score += 0.1;
        }
        // Engagement metrics (10% of score)
        if (($product->views_count ?? 0) > 0) {
            $score += 0.05;
        }
        if (($product->average_rating ?? 0) > 0) {
            $score += 0.05;
        }

        return min($score, 1.0);
    }

    /**
     * Handle arrangeWithAdvancedSkipWhile functionality with proper error handling.
     */
    public function arrangeWithAdvancedSkipWhile(Collection $products, array $filters = []): Collection
    {
        $minPrice = $filters['min_price'] ?? 0;
        $maxPrice = $filters['max_price'] ?? null;
        $minRating = $filters['min_rating'] ?? 0;
        $hasImages = $filters['has_images'] ?? true;
        $isFeatured = $filters['is_featured'] ?? null;
        $categoryId = $filters['category_id'] ?? null;

        return $products->skipWhile(function ($product) use ($minPrice, $maxPrice, $minRating, $hasImages, $isFeatured, $categoryId) {
            // Skip products that don't meet basic requirements
            if (empty($product->name) || property_exists($product, 'is_visible') && ! $product->is_visible || property_exists($product, 'slug') && empty($product->slug)) {
                return true;
            }
            // Skip products with invalid prices
            if (property_exists($product, 'price') && $product->price <= 0 || property_exists($product, 'price') && $product->price < $minPrice) {
                return true;
            }
            // Skip products above maximum price
            if ($maxPrice !== null && property_exists($product, 'price') && $product->price > $maxPrice) {
                return true;
            }
            // Skip products without images if required
            if ($hasImages && method_exists($product, 'getFirstMediaUrl') && ! $product->getFirstMediaUrl('images')) {
                return true;
            }
            // Skip products based on featured status
            if ($isFeatured !== null && property_exists($product, 'is_featured') && $product->is_featured !== $isFeatured) {
                return true;
            }
            // Skip products with low ratings
            if ($minRating > 0 && property_exists($product, 'average_rating') && ($product->average_rating ?? 0) < $minRating) {
                return true;
            }
            // Skip products not in specified category
            if ($categoryId !== null && property_exists($product, 'category_id') && $product->category_id !== $categoryId) {
                return true;
            }

            return false;
        });
    }

    /**
     * Handle arrangeWithStockFiltering functionality with proper error handling.
     */
    public function arrangeWithStockFiltering(Collection $products, bool $inStockOnly = true, int $minStock = 1): Collection
    {
        return $products->skipWhile(function ($product) use ($inStockOnly, $minStock) {
            if ($inStockOnly) {
                // Skip products that are out of stock
                if (property_exists($product, 'stock_quantity') && $product->stock_quantity < $minStock) {
                    return true;
                }
                // Skip products that are not available
                if (property_exists($product, 'is_available') && ! $product->is_available) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Handle arrangeWithDateFiltering functionality with proper error handling.
     */
    public function arrangeWithDateFiltering(Collection $products, array $dateFilters = []): Collection
    {
        $newArrivalsDays = $dateFilters['new_arrivals_days'] ?? null;
        $seasonalStart = $dateFilters['seasonal_start'] ?? null;
        $seasonalEnd = $dateFilters['seasonal_end'] ?? null;
        $excludeOld = $dateFilters['exclude_old'] ?? false;

        return $products->skipWhile(function ($product) use ($newArrivalsDays, $seasonalStart, $seasonalEnd, $excludeOld) {
            $now = now();
            // Skip old products if requested
            if ($excludeOld && property_exists($product, 'created_at')) {
                $productDate = $product->created_at;
                if ($productDate && $productDate->diffInDays($now) > 365) {
                    return true;
                }
            }
            // Skip products not in new arrivals range
            if ($newArrivalsDays !== null && property_exists($product, 'created_at')) {
                $productDate = $product->created_at;
                if ($productDate && $productDate->diffInDays($now) > $newArrivalsDays) {
                    return true;
                }
            }
            // Skip products not in seasonal range
            if ($seasonalStart !== null && $seasonalEnd !== null && property_exists($product, 'created_at')) {
                $productDate = $product->created_at;
                if ($productDate && ($productDate->lt($seasonalStart) || $productDate->gt($seasonalEnd))) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Handle arrangeWithUserPreferences functionality with proper error handling.
     */
    public function arrangeWithUserPreferences(Collection $products, array $userPreferences = []): Collection
    {
        $preferredBrands = $userPreferences['preferred_brands'] ?? [];
        $preferredCategories = $userPreferences['preferred_categories'] ?? [];
        $excludedBrands = $userPreferences['excluded_brands'] ?? [];
        $excludedCategories = $userPreferences['excluded_categories'] ?? [];
        $priceRange = $userPreferences['price_range'] ?? null;

        return $products->skipWhile(function ($product) use ($preferredBrands, $preferredCategories, $excludedBrands, $excludedCategories, $priceRange) {
            // Skip products from excluded brands
            if (! empty($excludedBrands) && property_exists($product, 'brand_id') && in_array($product->brand_id, $excludedBrands)) {
                return true;
            }
            // Skip products from excluded categories
            if (! empty($excludedCategories) && property_exists($product, 'category_id') && in_array($product->category_id, $excludedCategories)) {
                return true;
            }
            // Skip products outside price range
            if ($priceRange !== null && property_exists($product, 'price')) {
                if ($product->price < $priceRange['min'] || $product->price > $priceRange['max']) {
                    return true;
                }
            }
            // If user has preferences, skip products that don't match
            if (! empty($preferredBrands) && property_exists($product, 'brand_id') && ! in_array($product->brand_id, $preferredBrands)) {
                return true;
            }
            if (! empty($preferredCategories) && property_exists($product, 'category_id') && ! in_array($product->category_id, $preferredCategories)) {
                return true;
            }

            return false;
        });
    }

    /**
     * Handle arrangeWithPerformanceFiltering functionality with proper error handling.
     */
    public function arrangeWithPerformanceFiltering(Collection $products, array $performanceFilters = []): Collection
    {
        $minViews = $performanceFilters['min_views'] ?? 0;
        $minSales = $performanceFilters['min_sales'] ?? 0;
        $minRating = $performanceFilters['min_rating'] ?? 0;
        $maxRating = $performanceFilters['max_rating'] ?? 5.0;
        $trendingOnly = $performanceFilters['trending_only'] ?? false;

        return $products->skipWhile(function ($product) use ($minViews, $minSales, $minRating, $maxRating, $trendingOnly) {
            // Skip products with low views
            if ($minViews > 0 && property_exists($product, 'views_count') && ($product->views_count ?? 0) < $minViews) {
                return true;
            }
            // Skip products with low sales
            if ($minSales > 0 && property_exists($product, 'sales_count') && ($product->sales_count ?? 0) < $minSales) {
                return true;
            }
            // Skip products with low ratings
            if ($minRating > 0 && property_exists($product, 'average_rating') && ($product->average_rating ?? 0) < $minRating) {
                return true;
            }
            // Skip products with high ratings (for testing low-rated products)
            if ($maxRating < 5.0 && property_exists($product, 'average_rating') && ($product->average_rating ?? 0) > $maxRating) {
                return true;
            }
            // Skip non-trending products
            if ($trendingOnly && property_exists($product, 'is_trending') && ! $product->is_trending) {
                return true;
            }

            return false;
        });
    }
}
