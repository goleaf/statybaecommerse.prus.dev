<?php

declare (strict_types=1);
namespace App\Services\Recommendations;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
/**
 * TrendingRecommendation
 * 
 * Service class containing TrendingRecommendation business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 */
final class TrendingRecommendation extends BaseRecommendation
{
    /**
     * Handle getDefaultConfig functionality with proper error handling.
     * @return array
     */
    protected function getDefaultConfig(): array
    {
        return [
            'max_results' => 10,
            'min_score' => 0.1,
            'time_window' => 7,
            // days
            'trend_weights' => ['recent_sales' => 0.4, 'recent_views' => 0.3, 'recent_reviews' => 0.2, 'recent_wishlist' => 0.1],
            'min_recent_activity' => 1,
            'boost_new_products' => true,
            'new_product_threshold' => 30,
        ];
    }
    /**
     * Handle getRecommendations functionality with proper error handling.
     * @param User|null $user
     * @param Product|null $product
     * @param array $context
     * @return Collection
     */
    public function getRecommendations(?User $user = null, ?Product $product = null, array $context = []): Collection
    {
        $startTime = microtime(true);
        $cacheKey = $this->generateCacheKey('trending', $user, $product, $context);
        $cached = $this->getCachedResult($cacheKey);
        if ($cached) {
            return $cached;
        }
        $recommendations = $this->calculateTrendingRecommendations($user, $product);
        $this->logPerformance('trending', microtime(true) - $startTime, $recommendations->count());
        $this->trackRecommendation('trending', $user, $product, $recommendations->toArray());
        return $this->cacheResult($cacheKey, $recommendations, $this->config['cache_ttl'] ?? 900);
        // 15 minutes
    }
    /**
     * Handle calculateTrendingRecommendations functionality with proper error handling.
     * @param User|null $user
     * @param Product|null $product
     * @return Collection
     */
    private function calculateTrendingRecommendations(?User $user = null, ?Product $product = null): Collection
    {
        $timeWindow = $this->config['time_window'];
        $weights = $this->config['trend_weights'];
        $query = Product::query()->with(['media', 'brand', 'categories'])->where('is_visible', true);
        // Exclude current product if provided
        if ($product) {
            $query->where('id', '!=', $product->id);
        }
        // Apply filters
        $query = $this->applyFilters($query);
        // Calculate trending scores with velocity (growth rate)
        $query->selectRaw('
            products.*,
            COALESCE(recent_sales, 0) as recent_sales,
            COALESCE(recent_views, 0) as recent_views,
            COALESCE(recent_reviews, 0) as recent_reviews,
            COALESCE(recent_wishlist, 0) as recent_wishlist,
            COALESCE(previous_sales, 0) as previous_sales,
            COALESCE(previous_views, 0) as previous_views,
            COALESCE(previous_reviews, 0) as previous_reviews,
            COALESCE(previous_wishlist, 0) as previous_wishlist,
            CASE 
                WHEN products.created_at >= ? THEN 1.5 
                ELSE 1.0 
            END as new_product_boost,
            (
                (COALESCE(recent_sales, 0) * ?) +
                (COALESCE(recent_views, 0) * ?) +
                (COALESCE(recent_reviews, 0) * ?) +
                (COALESCE(recent_wishlist, 0) * ?)
            ) * CASE 
                WHEN products.created_at >= ? THEN 1.5 
                ELSE 1.0 
            END as trending_score
        ', [now()->subDays($this->config['new_product_threshold']), $weights['recent_sales'], $weights['recent_views'], $weights['recent_reviews'], $weights['recent_wishlist'], now()->subDays($this->config['new_product_threshold'])]);
        // Add subqueries for recent activity
        $query->leftJoinSub($this->getRecentSalesSubquery($timeWindow), 'recent_sales_stats', 'products.id', '=', 'recent_sales_stats.product_id');
        $query->leftJoinSub($this->getRecentViewsSubquery($timeWindow), 'recent_view_stats', 'products.id', '=', 'recent_view_stats.product_id');
        $query->leftJoinSub($this->getRecentReviewsSubquery($timeWindow), 'recent_review_stats', 'products.id', '=', 'recent_review_stats.product_id');
        $query->leftJoinSub($this->getRecentWishlistSubquery($timeWindow), 'recent_wishlist_stats', 'products.id', '=', 'recent_wishlist_stats.product_id');
        // Add subqueries for previous period activity (for velocity calculation)
        $query->leftJoinSub($this->getPreviousSalesSubquery($timeWindow), 'previous_sales_stats', 'products.id', '=', 'previous_sales_stats.product_id');
        $query->leftJoinSub($this->getPreviousViewsSubquery($timeWindow), 'previous_view_stats', 'products.id', '=', 'previous_view_stats.product_id');
        $query->leftJoinSub($this->getPreviousReviewsSubquery($timeWindow), 'previous_review_stats', 'products.id', '=', 'previous_review_stats.product_id');
        $query->leftJoinSub($this->getPreviousWishlistSubquery($timeWindow), 'previous_wishlist_stats', 'products.id', '=', 'previous_wishlist_stats.product_id');
        // Apply minimum recent activity threshold
        if ($this->config['min_recent_activity'] > 0) {
            $query->havingRaw('
                (COALESCE(recent_sales, 0) + 
                 COALESCE(recent_views, 0) + 
                 COALESCE(recent_reviews, 0) + 
                 COALESCE(recent_wishlist, 0)) >= ?
            ', [$this->config['min_recent_activity']]);
        }
        // Order by trending score and limit results
        return $query->orderByDesc('trending_score')->limit($this->maxResults)->get();
    }
    /**
     * Handle getRecentSalesSubquery functionality with proper error handling.
     * @param int $timeWindow
     * @return Illuminate\Database\Eloquent\Builder
     */
    private function getRecentSalesSubquery(int $timeWindow): \Illuminate\Database\Eloquent\Builder
    {
        return \App\Models\OrderItem::query()->selectRaw('product_id, COUNT(*) as recent_sales')->whereHas('order', function ($query) use ($timeWindow) {
            $query->whereIn('status', ['completed', 'delivered'])->where('created_at', '>=', now()->subDays($timeWindow));
        })->groupBy('product_id');
    }
    /**
     * Handle getRecentViewsSubquery functionality with proper error handling.
     * @param int $timeWindow
     * @return Illuminate\Database\Eloquent\Builder
     */
    private function getRecentViewsSubquery(int $timeWindow): \Illuminate\Database\Eloquent\Builder
    {
        if (class_exists(\App\Models\UserBehavior::class)) {
            return \App\Models\UserBehavior::query()->selectRaw('product_id, COUNT(*) as recent_views')->where('behavior_type', 'view')->where('created_at', '>=', now()->subDays($timeWindow))->groupBy('product_id');
        }
        return Product::query()->selectRaw('id as product_id, 0 as recent_views')->whereRaw('1 = 0');
    }
    /**
     * Handle getRecentReviewsSubquery functionality with proper error handling.
     * @param int $timeWindow
     * @return Illuminate\Database\Eloquent\Builder
     */
    private function getRecentReviewsSubquery(int $timeWindow): \Illuminate\Database\Eloquent\Builder
    {
        return \App\Models\Review::query()->selectRaw('product_id, COUNT(*) as recent_reviews')->where('created_at', '>=', now()->subDays($timeWindow))->groupBy('product_id');
    }
    /**
     * Handle getRecentWishlistSubquery functionality with proper error handling.
     * @param int $timeWindow
     * @return Illuminate\Database\Eloquent\Builder
     */
    private function getRecentWishlistSubquery(int $timeWindow): \Illuminate\Database\Eloquent\Builder
    {
        if (class_exists(\App\Models\UserBehavior::class)) {
            return \App\Models\UserBehavior::query()->selectRaw('product_id, COUNT(*) as recent_wishlist')->where('behavior_type', 'wishlist')->where('created_at', '>=', now()->subDays($timeWindow))->groupBy('product_id');
        }
        return Product::query()->selectRaw('id as product_id, 0 as recent_wishlist')->whereRaw('1 = 0');
    }
    /**
     * Handle getPreviousSalesSubquery functionality with proper error handling.
     * @param int $timeWindow
     * @return Illuminate\Database\Eloquent\Builder
     */
    private function getPreviousSalesSubquery(int $timeWindow): \Illuminate\Database\Eloquent\Builder
    {
        return \App\Models\OrderItem::query()->selectRaw('product_id, COUNT(*) as previous_sales')->whereHas('order', function ($query) use ($timeWindow) {
            $query->whereIn('status', ['completed', 'delivered'])->whereBetween('created_at', [now()->subDays($timeWindow * 2), now()->subDays($timeWindow)]);
        })->groupBy('product_id');
    }
    /**
     * Handle getPreviousViewsSubquery functionality with proper error handling.
     * @param int $timeWindow
     * @return Illuminate\Database\Eloquent\Builder
     */
    private function getPreviousViewsSubquery(int $timeWindow): \Illuminate\Database\Eloquent\Builder
    {
        if (class_exists(\App\Models\UserBehavior::class)) {
            return \App\Models\UserBehavior::query()->selectRaw('product_id, COUNT(*) as previous_views')->where('behavior_type', 'view')->whereBetween('created_at', [now()->subDays($timeWindow * 2), now()->subDays($timeWindow)])->groupBy('product_id');
        }
        return Product::query()->selectRaw('id as product_id, 0 as previous_views')->whereRaw('1 = 0');
    }
    /**
     * Handle getPreviousReviewsSubquery functionality with proper error handling.
     * @param int $timeWindow
     * @return Illuminate\Database\Eloquent\Builder
     */
    private function getPreviousReviewsSubquery(int $timeWindow): \Illuminate\Database\Eloquent\Builder
    {
        return \App\Models\Review::query()->selectRaw('product_id, COUNT(*) as previous_reviews')->whereBetween('created_at', [now()->subDays($timeWindow * 2), now()->subDays($timeWindow)])->groupBy('product_id');
    }
    /**
     * Handle getPreviousWishlistSubquery functionality with proper error handling.
     * @param int $timeWindow
     * @return Illuminate\Database\Eloquent\Builder
     */
    private function getPreviousWishlistSubquery(int $timeWindow): \Illuminate\Database\Eloquent\Builder
    {
        if (class_exists(\App\Models\UserBehavior::class)) {
            return \App\Models\UserBehavior::query()->selectRaw('product_id, COUNT(*) as previous_wishlist')->where('behavior_type', 'wishlist')->whereBetween('created_at', [now()->subDays($timeWindow * 2), now()->subDays($timeWindow)])->groupBy('product_id');
        }
        return Product::query()->selectRaw('id as product_id, 0 as previous_wishlist')->whereRaw('1 = 0');
    }
    /**
     * Handle getTrendingCategories functionality with proper error handling.
     * @return Collection
     */
    public function getTrendingCategories(): Collection
    {
        // Get trending products grouped by category
        $trendingProducts = $this->calculateTrendingRecommendations();
        return $trendingProducts->groupBy(function ($product) {
            return $product->categories->first()?->name ?? 'Uncategorized';
        })->map(function ($products, $categoryName) {
            return ['category' => $categoryName, 'products' => $products, 'trend_score' => $products->sum('trending_score')];
        })->sortByDesc('trend_score');
    }
    /**
     * Handle getTrendingBrands functionality with proper error handling.
     * @return Collection
     */
    public function getTrendingBrands(): Collection
    {
        // Get trending products grouped by brand
        $trendingProducts = $this->calculateTrendingRecommendations();
        return $trendingProducts->groupBy(function ($product) {
            return $product->brand?->name ?? 'No Brand';
        })->map(function ($products, $brandName) {
            return ['brand' => $brandName, 'products' => $products, 'trend_score' => $products->sum('trending_score')];
        })->sortByDesc('trend_score');
    }
}