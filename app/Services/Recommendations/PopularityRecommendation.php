<?php

declare(strict_types=1);

namespace App\Services\Recommendations;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * PopularityRecommendation
 *
 * Service class containing PopularityRecommendation business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class PopularityRecommendation extends BaseRecommendation
{
    /**
     * Handle getDefaultConfig functionality with proper error handling.
     */
    protected function getDefaultConfig(): array
    {
        return [
            'max_results' => 10,
            'min_score' => 0.1,
            'time_window' => 90,
            // days
            'popularity_weights' => ['sales' => 0.5, 'views' => 0.2, 'reviews' => 0.2, 'wishlist' => 0.1],
            'min_sales' => 1,
            'min_reviews' => 0,
        ];
    }

    /**
     * Handle getRecommendations functionality with proper error handling.
     */
    public function getRecommendations(?User $user = null, ?Product $product = null, array $context = []): Collection
    {
        $startTime = microtime(true);
        $cacheKey = $this->generateCacheKey('popularity', $user, $product, $context);
        $cached = $this->getCachedResult($cacheKey);
        if ($cached) {
            return $cached;
        }
        $recommendations = $this->calculatePopularityRecommendations($user, $product);
        $this->logPerformance('popularity', microtime(true) - $startTime, $recommendations->count());
        $this->trackRecommendation('popularity', $user, $product, $recommendations->toArray());

        return $this->cacheResult($cacheKey, $recommendations, $this->config['cache_ttl'] ?? 1800);
    }

    /**
     * Handle calculatePopularityRecommendations functionality with proper error handling.
     */
    private function calculatePopularityRecommendations(?User $user = null, ?Product $product = null): Collection
    {
        $timeWindow = $this->config['time_window'];
        $weights = $this->config['popularity_weights'];
        $query = Product::query()->with(['media', 'brand', 'categories'])->where('is_visible', true);
        // Exclude current product if provided
        if ($product) {
            $query->where('id', '!=', $product->id);
        }
        // Apply filters
        $query = $this->applyFilters($query);
        // Calculate popularity scores
        $query->selectRaw('
            products.*,
            COALESCE(sales_count, 0) as sales_count,
            COALESCE(view_count, 0) as view_count,
            COALESCE(review_count, 0) as review_count,
            COALESCE(wishlist_count, 0) as wishlist_count,
            (
                (COALESCE(sales_count, 0) * ?) +
                (COALESCE(view_count, 0) * ?) +
                (COALESCE(review_count, 0) * ?) +
                (COALESCE(wishlist_count, 0) * ?)
            ) as popularity_score
        ', [$weights['sales'], $weights['views'], $weights['reviews'], $weights['wishlist']]);
        // Add subqueries for different metrics
        $query->leftJoinSub($this->getSalesSubquery($timeWindow), 'sales_stats', 'products.id', '=', 'sales_stats.product_id');
        $query->leftJoinSub($this->getViewsSubquery($timeWindow), 'view_stats', 'products.id', '=', 'view_stats.product_id');
        $query->leftJoinSub($this->getReviewsSubquery($timeWindow), 'review_stats', 'products.id', '=', 'review_stats.product_id');
        $query->leftJoinSub($this->getWishlistSubquery($timeWindow), 'wishlist_stats', 'products.id', '=', 'wishlist_stats.product_id');
        // Add GROUP BY clause for SQLite compatibility
        $query->groupBy('products.id');
        // Apply minimum thresholds
        if ($this->config['min_sales'] > 0) {
            $query->havingRaw('COALESCE(sales_count, 0) >= ?', [$this->config['min_sales']]);
        }
        if ($this->config['min_reviews'] > 0) {
            $query->havingRaw('COALESCE(review_count, 0) >= ?', [$this->config['min_reviews']]);
        }

        // Order by popularity score and limit results
        return $query->orderByDesc('popularity_score')->limit($this->maxResults)->get();
    }

    /**
     * Handle getSalesSubquery functionality with proper error handling.
     *
     * @return Illuminate\Database\Eloquent\Builder
     */
    private function getSalesSubquery(int $timeWindow): \Illuminate\Database\Eloquent\Builder
    {
        return \App\Models\OrderItem::query()->selectRaw('product_id, COUNT(*) as sales_count')->whereHas('order', function ($query) use ($timeWindow) {
            $query->whereIn('status', ['completed', 'delivered'])->where('created_at', '>=', now()->subDays($timeWindow));
        })->groupBy('product_id');
    }

    /**
     * Handle getViewsSubquery functionality with proper error handling.
     *
     * @return Illuminate\Database\Eloquent\Builder
     */
    private function getViewsSubquery(int $timeWindow): \Illuminate\Database\Eloquent\Builder
    {
        if (class_exists(\App\Models\UserBehavior::class)) {
            return \App\Models\UserBehavior::query()->selectRaw('product_id, COUNT(*) as view_count')->where('behavior_type', 'view')->where('created_at', '>=', now()->subDays($timeWindow))->groupBy('product_id');
        }

        // Fallback to product view_count field if available
        return Product::query()->selectRaw('id as product_id, COALESCE(view_count, 0) as view_count')->where('created_at', '>=', now()->subDays($timeWindow));
    }

    /**
     * Handle getReviewsSubquery functionality with proper error handling.
     *
     * @return Illuminate\Database\Eloquent\Builder
     */
    private function getReviewsSubquery(int $timeWindow): \Illuminate\Database\Eloquent\Builder
    {
        return \App\Models\Review::query()->selectRaw('product_id, COUNT(*) as review_count')->where('created_at', '>=', now()->subDays($timeWindow))->groupBy('product_id');
    }

    /**
     * Handle getWishlistSubquery functionality with proper error handling.
     *
     * @return Illuminate\Database\Eloquent\Builder
     */
    private function getWishlistSubquery(int $timeWindow): \Illuminate\Database\Eloquent\Builder
    {
        if (class_exists(\App\Models\UserBehavior::class)) {
            return \App\Models\UserBehavior::query()->selectRaw('product_id, COUNT(*) as wishlist_count')->where('behavior_type', 'wishlist')->where('created_at', '>=', now()->subDays($timeWindow))->groupBy('product_id');
        }

        // Fallback if wishlist behavior tracking is not available
        return Product::query()->selectRaw('id as product_id, 0 as wishlist_count')->whereRaw('1 = 0');
        // Empty result
    }

    /**
     * Handle getTrendingProducts functionality with proper error handling.
     */
    public function getTrendingProducts(int $days = 7): Collection
    {
        $this->config['time_window'] = $days;

        return $this->getRecommendations();
    }

    /**
     * Handle getCategoryPopularity functionality with proper error handling.
     */
    public function getCategoryPopularity(int $categoryId): Collection
    {
        $this->filters = [['type' => 'whereHas', 'field' => 'categories', 'value' => ['field' => 'id', 'operator' => '=', 'value' => $categoryId]]];

        return $this->getRecommendations();
    }

    /**
     * Handle getBrandPopularity functionality with proper error handling.
     */
    public function getBrandPopularity(int $brandId): Collection
    {
        $this->filters = [['type' => 'where', 'field' => 'brand_id', 'operator' => '=', 'value' => $brandId]];

        return $this->getRecommendations();
    }

    /**
     * Handle updatePopularityWeights functionality with proper error handling.
     */
    public function updatePopularityWeights(array $performanceData): void
    {
        // Adjust weights based on performance metrics
        $totalPerformance = array_sum(array_column($performanceData, 'conversion_rate'));
        if ($totalPerformance > 0) {
            foreach ($performanceData as $metric => $data) {
                if (isset($this->config['popularity_weights'][$metric])) {
                    $this->config['popularity_weights'][$metric] = $data['conversion_rate'] / $totalPerformance;
                }
            }
        }
    }
}
