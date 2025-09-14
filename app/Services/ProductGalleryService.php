<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

final class ProductGalleryService
{
    /**
     * Arrange products for gallery layout using splitIn method
     * 
     * @param Collection $products
     * @param int $columnCount
     * @return Collection
     */
    public function arrangeForGallery(Collection $products, int $columnCount = 4): Collection
    {
        // Filter out invalid products - check if properties exist before accessing them
        $validProducts = $products->filter(function ($product) {
            return !empty($product->name) && 
                   (!property_exists($product, 'is_visible') || $product->is_visible) &&
                   (!property_exists($product, 'price') || $product->price > 0) &&
                   (!property_exists($product, 'slug') || !empty($product->slug));
        });

        $productColumns = $validProducts->splitIn($columnCount);
        
        return $productColumns->map(function ($columnProducts, $columnIndex) {
            return [
                'column_id' => $columnIndex + 1,
                'item_count' => $columnProducts->count(),
                'products' => $columnProducts->map(fn($product) => [
                    'id' => $product->id ?? null,
                    'name' => $product->name ?? 'Unknown',
                    'slug' => $product->slug ?? 'unknown',
                    'price' => $product->formatted_price ?? $product->price ?? 0,
                    'image_url' => method_exists($product, 'getFirstMediaUrl') ? $product->getFirstMediaUrl('images') : null,
                    'category' => $product->category?->name ?? null,
                    'is_featured' => $product->is_featured ?? false,
                ])
            ];
        });
    }

    /**
     * Split products into balanced columns for masonry layout
     * 
     * @param Collection $products
     * @param int $columns
     * @return Collection
     */
    public function arrangeForMasonry(Collection $products, int $columns = 3): Collection
    {
        // Filter out invalid products - check if properties exist before accessing them
        $validProducts = $products->filter(function ($product) {
            return !empty($product->name) && 
                   (!property_exists($product, 'is_visible') || $product->is_visible) &&
                   (!property_exists($product, 'price') || $product->price > 0) &&
                   (!property_exists($product, 'slug') || !empty($product->slug));
        });

        return $validProducts->splitIn($columns);
    }

    /**
     * Split products for responsive grid layout
     * 
     * @param Collection $products
     * @param string $breakpoint
     * @return Collection
     */
    public function arrangeForResponsiveGrid(Collection $products, string $breakpoint = 'lg'): Collection
    {
        $columnCount = match($breakpoint) {
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
     * Split products for category showcase
     * 
     * @param Collection $products
     * @param int $featuredCount
     * @return array
     */
    public function arrangeForCategoryShowcase(Collection $products, int $featuredCount = 8): array
    {
        // Filter out invalid products - check if properties exist before accessing them
        $validProducts = $products->filter(function ($product) {
            return !empty($product->name) && 
                   (!property_exists($product, 'is_visible') || $product->is_visible) &&
                   (!property_exists($product, 'price') || $product->price > 0) &&
                   (!property_exists($product, 'slug') || !empty($product->slug));
        });

        $featuredProducts = $validProducts->take($featuredCount);
        $remainingProducts = $validProducts->skip($featuredCount);
        
        return [
            'featured' => $this->arrangeForGallery($featuredProducts, 4),
            'remaining' => $remainingProducts->count() > 0 ? $this->arrangeForGallery($remainingProducts, 3) : collect(),
            'total_count' => $validProducts->count(),
            'featured_count' => $featuredProducts->count(),
            'remaining_count' => $remainingProducts->count(),
        ];
    }

    /**
     * Split products for collection display
     * 
     * @param Collection $products
     * @param int $displayType
     * @return Collection
     */
    public function arrangeForCollection(Collection $products, int $displayType = 1): Collection
    {
        $columnCount = match($displayType) {
            1 => 4, // Grid
            2 => 3, // Masonry
            3 => 2, // List
            4 => 5, // Carousel
            default => 4,
        };

        return $this->arrangeForGallery($products, $columnCount);
    }

    /**
     * Split products for search results
     * 
     * @param Collection $products
     * @param int $resultsPerPage
     * @return Collection
     */
    public function arrangeForSearchResults(Collection $products, int $resultsPerPage = 20): Collection
    {
        $columnCount = match(true) {
            $resultsPerPage <= 12 => 3,
            $resultsPerPage <= 20 => 4,
            $resultsPerPage <= 30 => 5,
            default => 4,
        };

        return $this->arrangeForGallery($products, $columnCount);
    }

    /**
     * Split products for related products section
     * 
     * @param Collection $products
     * @param int $maxProducts
     * @return Collection
     */
    public function arrangeForRelatedProducts(Collection $products, int $maxProducts = 8): Collection
    {
        $limitedProducts = $products->take($maxProducts);
        return $this->arrangeForGallery($limitedProducts, 4);
    }

    /**
     * Split products for homepage featured section
     * 
     * @param Collection $products
     * @return array
     */
    public function arrangeForHomepageFeatured(Collection $products): array
    {
        // Filter out invalid products - check if properties exist before accessing them
        $validProducts = $products->filter(function ($product) {
            return !empty($product->name) && 
                   (!property_exists($product, 'is_visible') || $product->is_visible) &&
                   (!property_exists($product, 'price') || $product->price > 0) &&
                   (!property_exists($product, 'slug') || !empty($product->slug));
        });

        $heroProducts = $validProducts->take(1);
        $featuredProducts = $validProducts->skip(1)->take(6);
        $additionalProducts = $validProducts->skip(7)->take(8);
        
        return [
            'hero' => $heroProducts->first(),
            'featured' => $this->arrangeForGallery($featuredProducts, 3),
            'additional' => $this->arrangeForGallery($additionalProducts, 4),
            'total_count' => $validProducts->count(),
        ];
    }

    /**
     * Split products for mobile view
     * 
     * @param Collection $products
     * @return Collection
     */
    public function arrangeForMobile(Collection $products): Collection
    {
        return $this->arrangeForGallery($products, 2);
    }

    /**
     * Split products for tablet view
     * 
     * @param Collection $products
     * @return Collection
     */
    public function arrangeForTablet(Collection $products): Collection
    {
        return $this->arrangeForGallery($products, 3);
    }

    /**
     * Split products for desktop view
     * 
     * @param Collection $products
     * @return Collection
     */
    public function arrangeForDesktop(Collection $products): Collection
    {
        return $this->arrangeForGallery($products, 4);
    }

    /**
     * Advanced filtering with multiple skipWhile conditions
     * 
     * @param Collection $products
     * @param array $filters
     * @return Collection
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
            if (empty($product->name) || !$product->is_visible || empty($product->slug)) {
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
            if ($hasImages && !$product->getFirstMediaUrl('images')) {
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
     * Split products with quality-based filtering using skipWhile
     * 
     * @param Collection $products
     * @param int $columnCount
     * @param float $qualityThreshold
     * @return Collection
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
     * Calculate product quality score for filtering
     * 
     * @param mixed $product
     * @return float
     */
    private function calculateProductQualityScore($product): float
    {
        $score = 0.0;

        // Basic requirements (40% of score)
        if (!empty($product->name)) $score += 0.1;
        if (!empty($product->slug)) $score += 0.1;
        if ($product->is_visible) $score += 0.1;
        if ($product->price > 0) $score += 0.1;

        // Media quality (30% of score)
        if ($product->getFirstMediaUrl('images')) $score += 0.2;
        if ($product->getFirstMediaUrl('images', 'large')) $score += 0.1;

        // Content quality (20% of score)
        if (!empty($product->description)) $score += 0.1;
        if ($product->is_featured) $score += 0.1;

        // Engagement metrics (10% of score)
        if (($product->views_count ?? 0) > 0) $score += 0.05;
        if (($product->average_rating ?? 0) > 0) $score += 0.05;

        return min($score, 1.0);
    }
}
