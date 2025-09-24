<?php

declare(strict_types=1);

namespace App\Services\Recommendations;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * UpSellRecommendation
 *
 * Service class containing UpSellRecommendation business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class UpSellRecommendation extends BaseRecommendation
{
    /**
     * Handle getDefaultConfig functionality with proper error handling.
     */
    protected function getDefaultConfig(): array
    {
        return [
            'max_results' => 10,
            'min_score' => 0.1,
            'min_price_increase' => 1.1,
            // At least 10% more expensive
            'max_price_increase' => 2.0,
            // At most 2x more expensive
            'category_similarity_weight' => 0.5,
            'price_ratio_weight' => 0.3,
            'quality_indicators_weight' => 0.2,
            'quality_indicators' => ['review_rating' => 0.4, 'review_count' => 0.3, 'sales_count' => 0.3],
        ];
    }

    /**
     * Handle getRecommendations functionality with proper error handling.
     */
    public function getRecommendations(?User $user = null, ?Product $product = null, array $context = []): Collection
    {
        $startTime = microtime(true);
        if (! $product) {
            return collect();
        }
        $cacheKey = $this->generateCacheKey('up_sell', $user, $product, $context);
        $cached = $this->getCachedResult($cacheKey);
        if ($cached) {
            return $cached;
        }
        $recommendations = $this->calculateUpSellRecommendations($product, $user);
        $this->logPerformance('up_sell', microtime(true) - $startTime, $recommendations->count());
        $this->trackRecommendation('up_sell', $user, $product, $recommendations->toArray());

        return $this->cacheResult($cacheKey, $recommendations, $this->config['cache_ttl'] ?? 3600);
    }

    /**
     * Handle calculateUpSellRecommendations functionality with proper error handling.
     */
    private function calculateUpSellRecommendations(Product $product, ?User $user = null): Collection
    {
        $minPrice = $product->price * $this->config['min_price_increase'];
        $maxPrice = $product->price * $this->config['max_price_increase'];
        $query = Product::query()->with(['media', 'brand', 'categories', 'reviews'])->where('is_visible', true)->where('id', '!=', $product->id)->whereBetween('price', [$minPrice, $maxPrice]);
        // Filter by similar categories
        $categoryIds = $product->categories->pluck('id')->toArray();
        if (! empty($categoryIds)) {
            $query->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            });
        }
        // Apply additional filters
        $query = $this->applyFilters($query);
        $candidateProducts = $query->get();
        // Calculate up-sell scores
        $scoredProducts = $this->calculateUpSellScores($product, $candidateProducts);

        return $scoredProducts->sortByDesc('score')->take($this->maxResults)->pluck('product');
    }

    /**
     * Handle calculateUpSellScores functionality with proper error handling.
     */
    private function calculateUpSellScores(Product $product, Collection $candidateProducts): Collection
    {
        $scores = collect();
        foreach ($candidateProducts as $candidateProduct) {
            $categoryScore = $this->calculateCategorySimilarityScore($product, $candidateProduct);
            $priceScore = $this->calculatePriceRatioScore($product, $candidateProduct);
            $qualityScore = $this->calculateQualityIndicatorScore($candidateProduct);
            $totalScore = $categoryScore * $this->config['category_similarity_weight'] + $priceScore * $this->config['price_ratio_weight'] + $qualityScore * $this->config['quality_indicators_weight'];
            if ($totalScore >= $this->minScore) {
                $scores->push(['product' => $candidateProduct, 'score' => $totalScore, 'category_score' => $categoryScore, 'price_score' => $priceScore, 'quality_score' => $qualityScore, 'price_increase_ratio' => $candidateProduct->price / $product->price]);
            }
        }

        return $scores;
    }

    /**
     * Handle calculateCategorySimilarityScore functionality with proper error handling.
     */
    private function calculateCategorySimilarityScore(Product $product, Product $candidateProduct): float
    {
        $productCategories = $product->categories->pluck('id')->toArray();
        $candidateCategories = $candidateProduct->categories->pluck('id')->toArray();
        if (empty($productCategories) || empty($candidateCategories)) {
            return 0;
        }
        $intersection = array_intersect($productCategories, $candidateCategories);
        $union = array_unique(array_merge($productCategories, $candidateCategories));

        return count($intersection) / count($union);
    }

    /**
     * Handle calculatePriceRatioScore functionality with proper error handling.
     */
    private function calculatePriceRatioScore(Product $product, Product $candidateProduct): float
    {
        $priceRatio = $candidateProduct->price / $product->price;
        $minRatio = $this->config['min_price_increase'];
        $maxRatio = $this->config['max_price_increase'];
        // Optimal price ratio is around 1.5x (50% increase)
        $optimalRatio = ($minRatio + $maxRatio) / 2;
        $distance = abs($priceRatio - $optimalRatio);
        $maxDistance = max($optimalRatio - $minRatio, $maxRatio - $optimalRatio);

        return max(0, 1 - $distance / $maxDistance);
    }

    /**
     * Handle calculateQualityIndicatorScore functionality with proper error handling.
     */
    private function calculateQualityIndicatorScore(Product $product): float
    {
        $indicators = $this->config['quality_indicators'];
        $totalScore = 0;
        // Review rating score
        if (isset($indicators['review_rating'])) {
            $avgRating = $product->reviews()->avg('rating') ?? 0;
            $ratingScore = $avgRating / 5.0;
            // Normalize to 0-1
            $totalScore += $ratingScore * $indicators['review_rating'];
        }
        // Review count score
        if (isset($indicators['review_count'])) {
            $reviewCount = $product->reviews()->count();
            $reviewScore = min($reviewCount / 50.0, 1.0);
            // Cap at 1.0 for 50+ reviews
            $totalScore += $reviewScore * $indicators['review_count'];
        }
        // Sales count score
        if (isset($indicators['sales_count'])) {
            $salesCount = $product->orderItems()->whereHas('order', function ($query) {
                $query->whereIn('status', ['completed', 'delivered'])->where('created_at', '>=', now()->subDays(90));
            })->sum('quantity');
            $salesScore = min($salesCount / 100.0, 1.0);
            // Cap at 1.0 for 100+ sales
            $totalScore += $salesScore * $indicators['sales_count'];
        }

        return $totalScore;
    }

    /**
     * Handle getUpSellOpportunities functionality with proper error handling.
     */
    public function getUpSellOpportunities(User $user): Collection
    {
        // Get user's purchase history
        $purchasedProducts = $user->orders()->with('items.product')->where('status', 'completed')->get()->pluck('items')->flatten()->pluck('product')->unique('id');
        $opportunities = collect();
        foreach ($purchasedProducts as $purchasedProduct) {
            $upSellRecommendations = $this->getRecommendations($user, $purchasedProduct);
            if ($upSellRecommendations->isNotEmpty()) {
                $opportunities->push(['original_product' => $purchasedProduct, 'up_sell_products' => $upSellRecommendations, 'potential_revenue_increase' => $this->calculatePotentialRevenueIncrease($purchasedProduct, $upSellRecommendations)]);
            }
        }

        return $opportunities->sortByDesc('potential_revenue_increase');
    }

    /**
     * Handle calculatePotentialRevenueIncrease functionality with proper error handling.
     */
    private function calculatePotentialRevenueIncrease(Product $originalProduct, Collection $upSellProducts): float
    {
        $avgUpSellPrice = $upSellProducts->avg('price');
        $priceDifference = $avgUpSellPrice - $originalProduct->price;
        // Assume 10% conversion rate for up-sell
        $conversionRate = 0.1;

        return $priceDifference * $conversionRate;
    }

    /**
     * Handle getUpSellAnalytics functionality with proper error handling.
     */
    public function getUpSellAnalytics(Product $product): array
    {
        $minPrice = $product->price * $this->config['min_price_increase'];
        $maxPrice = $product->price * $this->config['max_price_increase'];
        $upSellCandidates = Product::where('is_visible', true)->where('id', '!=', $product->id)->whereBetween('price', [$minPrice, $maxPrice])->whereHas('categories', function ($query) use ($product) {
            $query->whereIn('categories.id', $product->categories->pluck('id'));
        })->get();

        return ['total_candidates' => $upSellCandidates->count(), 'price_range' => ['min' => $minPrice, 'max' => $maxPrice, 'avg' => $upSellCandidates->avg('price')], 'category_distribution' => $this->getUpSellCategoryDistribution($upSellCandidates), 'quality_distribution' => $this->getUpSellQualityDistribution($upSellCandidates), 'conversion_potential' => $this->calculateConversionPotential($product, $upSellCandidates)];
    }

    /**
     * Handle getUpSellCategoryDistribution functionality with proper error handling.
     */
    private function getUpSellCategoryDistribution(Collection $products): Collection
    {
        return $products->pluck('categories')->flatten()->groupBy('name')->map(function ($categories) {
            return ['category' => $categories->first(), 'count' => $categories->count()];
        })->sortByDesc('count');
    }

    /**
     * Handle getUpSellQualityDistribution functionality with proper error handling.
     */
    private function getUpSellQualityDistribution(Collection $products): array
    {
        return ['avg_rating' => $products->avg(function ($product) {
            return $product->reviews()->avg('rating') ?? 0;
        }), 'avg_review_count' => $products->avg(function ($product) {
            return $product->reviews()->count();
        }), 'high_quality_count' => $products->filter(function ($product) {
            $rating = $product->reviews()->avg('rating') ?? 0;
            $reviewCount = $product->reviews()->count();

            return $rating >= 4.0 && $reviewCount >= 10;
        })->count()];
    }

    /**
     * Handle calculateConversionPotential functionality with proper error handling.
     */
    private function calculateConversionPotential(Product $product, Collection $upSellCandidates): float
    {
        // Simple heuristic based on price ratio and quality
        $avgPriceRatio = $upSellCandidates->avg(function ($candidate) use ($product) {
            return $candidate->price / $product->price;
        });
        $avgQuality = $upSellCandidates->avg(function ($candidate) {
            return $candidate->reviews()->avg('rating') ?? 3.0;
        });
        // Optimal conversion when price ratio is around 1.5 and quality is high
        $priceScore = 1 - abs($avgPriceRatio - 1.5) / 1.5;
        $qualityScore = $avgQuality / 5.0;

        return $priceScore * 0.6 + $qualityScore * 0.4;
    }
}
