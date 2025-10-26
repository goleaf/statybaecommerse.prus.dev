<?php

declare(strict_types=1);

namespace App\Services\Recommendations;

use App\Models\Product;
use App\Models\ProductSimilarity;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use JsonException;

/**
 * ContentBasedRecommendation
 *
 * Service class containing ContentBasedRecommendation business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class ContentBasedRecommendation extends BaseRecommendation
{
    /**
     * Handle getDefaultConfig functionality with proper error handling.
     */
    protected function getDefaultConfig(): array
    {
        return ['max_results' => 10, 'min_score' => 0.3, 'feature_weights' => ['category' => 0.4, 'brand' => 0.3, 'price_range' => 0.2, 'attributes' => 0.1], 'use_cached_similarities' => true, 'recalculate_threshold' => 7];
    }

    /**
     * Handle getRecommendations functionality with proper error handling.
     */
    public function getRecommendations(?User $user = null, ?Product $product = null, array $context = []): EloquentCollection
    {
        $startTime = microtime(true);
        if (! $product) {
            return collect();
        }
        $cacheKey = $this->generateCacheKey('content_based', $user, $product, $context);
        $cached = $this->getCachedResult($cacheKey);
        if ($cached) {
            $collection = (new Product)->newCollection(
                $cached
                    ->pluck('similarProduct')
                    ->filter(fn ($p) => $p instanceof Product && $p->is_visible)
                    ->all()
            );

            $collection->each(static fn (Product $item) => $item->unsetRelation('brand'));

            return $collection;
        }
        $recommendations = $this->calculateContentBasedRecommendations($product, $user);
        $recommendations->each(static fn (Product $item) => $item->unsetRelation('brand'));
        $this->logPerformance('content_based', microtime(true) - $startTime, $recommendations->count());
        $this->trackRecommendation(
            'content_based',
            $user,
            $product,
            $recommendations
                ->map(static fn (Product $item) => ['id' => $item->getKey()])
                ->all()
        );

        return $this->cacheResult($cacheKey, $recommendations, $this->config['cache_ttl'] ?? 3600);
    }

    /**
     * Handle calculateContentBasedRecommendations functionality with proper error handling.
     */
    private function calculateContentBasedRecommendations(Product $product, ?User $user = null): EloquentCollection
    {
        // Check if we have recent cached similarities
        if ($this->config['use_cached_similarities']) {
            $cachedSimilarities = ProductSimilarity::where('product_id', $product->id)->byAlgorithm('content_based')->recent($this->config['recalculate_threshold'])->withMinScore($this->minScore)->orderedBySimilarity()->with('similarProduct')->limit($this->maxResults)->get();
            if ($cachedSimilarities->isNotEmpty()) {
                $collection = (new Product)->newCollection(
                    $cachedSimilarities
                        ->pluck('similarProduct')
                        ->filter(fn ($p) => $p instanceof Product && $p->is_visible)
                        ->all()
                );

                $collection->each(static fn (Product $item) => $item->unsetRelation('brand'));

                return $collection;
            }
        }
        // Calculate similarities on the fly
        $productFeatures = $this->getProductFeatures($product);
        $similarities = $this->calculateProductSimilarities($product, $productFeatures);
        // Persist the scored pairs before we strip the similarity metadata for
        // the response so the recommendation history always reflects the real
        // calculated score instead of a synthetic placeholder.
        $this->cacheSimilarities($product->id, $similarities);

        $products = $similarities
            ->take($this->maxResults)
            ->pluck('product')
            ->filter(fn ($candidate) => $candidate instanceof Product);

        $collection = (new Product)->newCollection($products->all());
        $collection->each(static fn (Product $item) => $item->unsetRelation('brand'));

        return $collection;
    }

    /**
     * Handle calculateProductSimilarities functionality with proper error handling.
     */
    private function calculateProductSimilarities(Product $product, array $productFeatures): Collection
    {
        $categoryIds = $product->categories->pluck('id')->toArray();
        $brandId = $product->brand_id;
        // Prices are stored as decimal strings, so normalise them to float
        // before deriving the price-range bucket used in scoring.
        $priceRange = $this->getPriceRange((float) $product->price);
        $query = Product::query()->with(['media', 'categories'])->where('is_visible', true)->where('id', '!=', $product->id);
        // Apply filters
        $query = $this->applyFilters($query);
        $products = $query->get();
        $similarities = collect();
        foreach ($products as $candidateProduct) {
            $similarity = $this->calculateSimilarityScore($productFeatures, $candidateProduct);
            if ($similarity >= $this->minScore) {
                $similarities->push(['product' => $candidateProduct, 'similarity' => $similarity]);
            }
        }

        return $similarities
            // Sort by score so the highest matches are cached and returned first.
            ->sortByDesc('similarity')
            ->values();
    }

    /**
     * Handle calculateSimilarityScore functionality with proper error handling.
     */
    private function calculateSimilarityScore(array $sourceFeatures, Product $candidateProduct): float
    {
        $candidateFeatures = $this->getProductFeatures($candidateProduct);
        $weights = $this->config['feature_weights'];
        $totalScore = 0;
        $totalWeight = 0;
        // Category similarity
        if (isset($weights['category'])) {
            $categoryScore = $this->calculateCategorySimilarity($sourceFeatures, $candidateFeatures);
            $totalScore += $categoryScore * $weights['category'];
            $totalWeight += $weights['category'];
        }
        // Brand similarity
        if (isset($weights['brand'])) {
            $brandScore = $this->calculateBrandSimilarity($sourceFeatures, $candidateFeatures);
            $totalScore += $brandScore * $weights['brand'];
            $totalWeight += $weights['brand'];
        }
        // Price range similarity
        if (isset($weights['price_range'])) {
            $priceScore = $this->calculatePriceSimilarity($sourceFeatures, $candidateFeatures);
            $totalScore += $priceScore * $weights['price_range'];
            $totalWeight += $weights['price_range'];
        }
        // Attribute similarity
        if (isset($weights['attributes'])) {
            $attributeScore = $this->calculateAttributeSimilarity($sourceFeatures, $candidateFeatures);
            $totalScore += $attributeScore * $weights['attributes'];
            $totalWeight += $weights['attributes'];
        }

        return $totalWeight > 0 ? $totalScore / $totalWeight : 0;
    }

    /**
     * Handle calculateCategorySimilarity functionality with proper error handling.
     */
    private function calculateCategorySimilarity(array $sourceFeatures, array $candidateFeatures): float
    {
        $sourceCategories = array_filter($sourceFeatures, fn ($k) => str_starts_with($k, 'category_'), ARRAY_FILTER_USE_KEY);
        $candidateCategories = array_filter($candidateFeatures, fn ($k) => str_starts_with($k, 'category_'), ARRAY_FILTER_USE_KEY);
        if (empty($sourceCategories) || empty($candidateCategories)) {
            return 0;
        }
        $intersection = array_intersect_key($sourceCategories, $candidateCategories);
        $union = array_merge($sourceCategories, $candidateCategories);

        return count($intersection) / count($union);
    }

    /**
     * Handle calculateBrandSimilarity functionality with proper error handling.
     */
    private function calculateBrandSimilarity(array $sourceFeatures, array $candidateFeatures): float
    {
        $sourceBrand = array_filter($sourceFeatures, fn ($k) => str_starts_with($k, 'brand_'), ARRAY_FILTER_USE_KEY);
        $candidateBrand = array_filter($candidateFeatures, fn ($k) => str_starts_with($k, 'brand_'), ARRAY_FILTER_USE_KEY);
        if (empty($sourceBrand) || empty($candidateBrand)) {
            return 0;
        }

        return array_intersect_key($sourceBrand, $candidateBrand) ? 1.0 : 0.0;
    }

    /**
     * Handle calculatePriceSimilarity functionality with proper error handling.
     */
    private function calculatePriceSimilarity(array $sourceFeatures, array $candidateFeatures): float
    {
        $sourcePrice = array_filter($sourceFeatures, fn ($k) => str_starts_with($k, 'price_range_'), ARRAY_FILTER_USE_KEY);
        $candidatePrice = array_filter($candidateFeatures, fn ($k) => str_starts_with($k, 'price_range_'), ARRAY_FILTER_USE_KEY);
        if (empty($sourcePrice) || empty($candidatePrice)) {
            return 0;
        }

        return array_intersect_key($sourcePrice, $candidatePrice) ? 1.0 : 0.0;
    }

    /**
     * Handle calculateAttributeSimilarity functionality with proper error handling.
     */
    private function calculateAttributeSimilarity(array $sourceFeatures, array $candidateFeatures): float
    {
        $sourceAttrs = array_filter($sourceFeatures, fn ($k) => str_starts_with($k, 'attr_'), ARRAY_FILTER_USE_KEY);
        $candidateAttrs = array_filter($candidateFeatures, fn ($k) => str_starts_with($k, 'attr_'), ARRAY_FILTER_USE_KEY);
        if (empty($sourceAttrs) || empty($candidateAttrs)) {
            return 0;
        }
        $intersection = array_intersect_key($sourceAttrs, $candidateAttrs);
        $union = array_merge($sourceAttrs, $candidateAttrs);

        return count($intersection) / count($union);
    }

    /**
     * Handle cacheSimilarities functionality with proper error handling.
     */
    private function cacheSimilarities(int $productId, Collection $similarities): void
    {
        $similarityData = [];
        foreach ($similarities->take($this->maxResults) as $entry) {
            // Each entry includes both the product instance and its computed
            // similarity value so cached records can surface the exact score
            // that powered the recommendation carousel.
            $similarProduct = $entry['product'] ?? null;
            $score = $entry['similarity'] ?? null;

            if (! $similarProduct instanceof Product || $score === null) {
                continue;
            }

            try {
                $encodedMetadata = json_encode([
                    'calculated_at'   => now(),
                    'feature_weights' => $this->config['feature_weights'],
                ], JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                // Skip invalid payloads rather than crashing the recommendation
                // pipeline so other similarity rows can still be cached.
                continue;
            }

            $similarityData[] = [
                'product_id'         => $productId,
                'similar_product_id' => $similarProduct->id,
                'algorithm_type'     => 'content_based',
                'similarity_score'   => $score,
                'calculation_data'   => $encodedMetadata,
                'calculated_at'      => now(),
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        }
        if (! empty($similarityData)) {
            DB::table('product_similarities')->upsert($similarityData, ['product_id', 'similar_product_id', 'algorithm_type'], ['similarity_score', 'calculation_data', 'calculated_at', 'updated_at']);
        }
    }
}
