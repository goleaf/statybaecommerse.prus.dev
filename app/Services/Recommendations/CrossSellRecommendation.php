<?php

declare(strict_types=1);

namespace App\Services\Recommendations;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * CrossSellRecommendation
 *
 * Service class containing CrossSellRecommendation business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class CrossSellRecommendation extends BaseRecommendation
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
            'min_co_purchase_count' => 2,
            'co_purchase_weight' => 0.6,
            'category_similarity_weight' => 0.3,
            'price_compatibility_weight' => 0.1,
            'max_price_ratio' => 2.0,
            // Max 2x the original price
            'min_price_ratio' => 0.5,
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
        $cacheKey = $this->generateCacheKey('cross_sell', $user, $product, $context);
        $cached = $this->getCachedResult($cacheKey);
        if ($cached) {
            return $cached;
        }
        $recommendations = $this->calculateCrossSellRecommendations($product, $user);
        $this->logPerformance('cross_sell', microtime(true) - $startTime, $recommendations->count());
        $this->trackRecommendation('cross_sell', $user, $product, $recommendations->toArray());

        return $this->cacheResult($cacheKey, $recommendations, $this->config['cache_ttl'] ?? 3600);
    }

    /**
     * Handle calculateCrossSellRecommendations functionality with proper error handling.
     */
    private function calculateCrossSellRecommendations(Product $product, ?User $user = null): Collection
    {
        // Get products frequently bought together
        $coPurchasedProducts = $this->getCoPurchasedProducts($product);
        if ($coPurchasedProducts->isEmpty()) {
            // Fallback to category-based recommendations
            return $this->getCategoryBasedCrossSell($product);
        }
        // Calculate cross-sell scores
        $scoredProducts = $this->calculateCrossSellScores($product, $coPurchasedProducts);
        // Get products and apply filters
        $productIds = $scoredProducts->pluck('product_id')->toArray();
        $query = Product::query()->with(['media', 'brand', 'categories'])->where('is_visible', true)->whereIn('id', $productIds);
        $query = $this->applyFilters($query);
        $products = $query->get()->keyBy('id');

        // Sort by score and return
        return $scoredProducts->sortByDesc('score')->take($this->maxResults)->map(function ($item) use ($products) {
            return $products->get($item['product_id']);
        })->filter()->values();
    }

    /**
     * Handle getCoPurchasedProducts functionality with proper error handling.
     */
    private function getCoPurchasedProducts(Product $product): Collection
    {
        $timeWindow = $this->config['time_window'];
        $minCoPurchaseCount = $this->config['min_co_purchase_count'];
        // Get orders that contain this product
        $orderIds = DB::table('order_items')->join('orders', 'order_items.order_id', '=', 'orders.id')->where('order_items.product_id', $product->id)->whereIn('orders.status', ['completed', 'delivered'])->where('orders.created_at', '>=', now()->subDays($timeWindow))->pluck('orders.id');
        if ($orderIds->isEmpty()) {
            return collect();
        }
        // Get products that were bought together with this product
        $coPurchasedData = DB::table('order_items')->join('orders', 'order_items.order_id', '=', 'orders.id')->whereIn('order_items.order_id', $orderIds)->where('order_items.product_id', '!=', $product->id)->whereIn('orders.status', ['completed', 'delivered'])->selectRaw('
                order_items.product_id,
                COUNT(*) as co_purchase_count,
                AVG(order_items.quantity) as avg_quantity,
                COUNT(DISTINCT orders.user_id) as unique_customers
            ')->groupBy('order_items.product_id')->having('co_purchase_count', '>=', $minCoPurchaseCount)->orderByDesc('co_purchase_count')->get();

        return collect($coPurchasedData);
    }

    /**
     * Handle calculateCrossSellScores functionality with proper error handling.
     */
    private function calculateCrossSellScores(Product $product, Collection $coPurchasedProducts): Collection
    {
        $scores = collect();
        foreach ($coPurchasedProducts as $coProduct) {
            $productId = $coProduct->product_id;
            $coPurchaseCount = $coProduct->co_purchase_count;
            $uniqueCustomers = $coProduct->unique_customers;
            // Get the candidate product for additional calculations
            $candidateProduct = Product::find($productId);
            if (! $candidateProduct || ! $candidateProduct->is_visible) {
                continue;
            }
            // Calculate different components of the cross-sell score
            $coPurchaseScore = $this->calculateCoPurchaseScore($coPurchaseCount, $uniqueCustomers);
            $categoryScore = $this->calculateCategorySimilarityScore($product, $candidateProduct);
            $priceScore = $this->calculatePriceCompatibilityScore($product, $candidateProduct);
            // Combine scores with weights
            $totalScore = $coPurchaseScore * $this->config['co_purchase_weight'] + $categoryScore * $this->config['category_similarity_weight'] + $priceScore * $this->config['price_compatibility_weight'];
            if ($totalScore >= $this->minScore) {
                $scores->push(['product_id' => $productId, 'score' => $totalScore, 'co_purchase_count' => $coPurchaseCount, 'unique_customers' => $uniqueCustomers, 'co_purchase_score' => $coPurchaseScore, 'category_score' => $categoryScore, 'price_score' => $priceScore]);
            }
        }

        return $scores;
    }

    /**
     * Handle calculateCoPurchaseScore functionality with proper error handling.
     */
    private function calculateCoPurchaseScore(int $coPurchaseCount, int $uniqueCustomers): float
    {
        // Normalize based on frequency and customer diversity
        $frequencyScore = min($coPurchaseCount / 10.0, 1.0);
        // Cap at 1.0 for 10+ co-purchases
        $diversityScore = min($uniqueCustomers / 5.0, 1.0);

        // Cap at 1.0 for 5+ unique customers
        return $frequencyScore * 0.7 + $diversityScore * 0.3;
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
     * Handle calculatePriceCompatibilityScore functionality with proper error handling.
     */
    private function calculatePriceCompatibilityScore(Product $product, Product $candidateProduct): float
    {
        $productPrice = $product->price;
        $candidatePrice = $candidateProduct->price;
        if ($productPrice == 0) {
            return 0;
        }
        $priceRatio = $candidatePrice / $productPrice;
        $maxRatio = $this->config['max_price_ratio'];
        $minRatio = $this->config['min_price_ratio'];
        // Check if price is within acceptable range
        if ($priceRatio < $minRatio || $priceRatio > $maxRatio) {
            return 0;
        }
        // Score based on how close to the original price (1.0 = same price)
        $targetRatio = 1.0;
        $distance = abs($priceRatio - $targetRatio);
        $maxDistance = max($targetRatio - $minRatio, $maxRatio - $targetRatio);

        return max(0, 1 - $distance / $maxDistance);
    }

    /**
     * Handle getCategoryBasedCrossSell functionality with proper error handling.
     */
    private function getCategoryBasedCrossSell(Product $product): Collection
    {
        // Fallback to category-based recommendations when no co-purchase data
        $categoryIds = $product->categories->pluck('id')->toArray();
        if (empty($categoryIds)) {
            return collect();
        }

        return Product::query()->with(['media', 'brand', 'categories'])->where('is_visible', true)->where('id', '!=', $product->id)->whereHas('categories', function ($query) use ($categoryIds) {
            $query->whereIn('categories.id', $categoryIds);
        })->whereBetween('price', [$product->price * $this->config['min_price_ratio'], $product->price * $this->config['max_price_ratio']])->inRandomOrder()->limit($this->maxResults)->get();
    }

    /**
     * Handle getTopCrossSellPairs functionality with proper error handling.
     */
    public function getTopCrossSellPairs(int $limit = 10): Collection
    {
        $timeWindow = $this->config['time_window'];

        return DB::table('order_items as oi1')->join('order_items as oi2', function ($join) {
            $join->on('oi1.order_id', '=', 'oi2.order_id')->on('oi1.product_id', '<', 'oi2.product_id');
            // Avoid duplicates
        })->join('orders', 'oi1.order_id', '=', 'orders.id')->join('products as p1', 'oi1.product_id', '=', 'p1.id')->join('products as p2', 'oi2.product_id', '=', 'p2.id')->whereIn('orders.status', ['completed', 'delivered'])->where('orders.created_at', '>=', now()->subDays($timeWindow))->where('p1.is_visible', true)->where('p2.is_visible', true)->selectRaw('
                oi1.product_id as product1_id,
                oi2.product_id as product2_id,
                p1.name as product1_name,
                p2.name as product2_name,
                COUNT(*) as co_purchase_count,
                COUNT(DISTINCT orders.user_id) as unique_customers
            ')->groupBy('oi1.product_id', 'oi2.product_id', 'p1.name', 'p2.name')->having('co_purchase_count', '>=', $this->config['min_co_purchase_count'])->orderByDesc('co_purchase_count')->limit($limit)->get();
    }

    /**
     * Handle getProductCrossSellAnalytics functionality with proper error handling.
     */
    public function getProductCrossSellAnalytics(Product $product): array
    {
        $coPurchasedProducts = $this->getCoPurchasedProducts($product);

        return ['total_co_purchase_products' => $coPurchasedProducts->count(), 'total_co_purchases' => $coPurchasedProducts->sum('co_purchase_count'), 'unique_customers' => $coPurchasedProducts->sum('unique_customers'), 'top_cross_sell_categories' => $this->getTopCrossSellCategories($product), 'price_distribution' => $this->getCrossSellPriceDistribution($product, $coPurchasedProducts)];
    }

    /**
     * Handle getTopCrossSellCategories functionality with proper error handling.
     */
    private function getTopCrossSellCategories(Product $product): Collection
    {
        $coPurchasedProducts = $this->getCoPurchasedProducts($product);
        $productIds = $coPurchasedProducts->pluck('product_id')->toArray();

        return Product::whereIn('id', $productIds)->with('categories')->get()->pluck('categories')->flatten()->groupBy('name')->map(function ($categories) {
            return ['category' => $categories->first(), 'count' => $categories->count()];
        })->sortByDesc('count')->take(5);
    }

    /**
     * Handle getCrossSellPriceDistribution functionality with proper error handling.
     */
    private function getCrossSellPriceDistribution(Product $product, Collection $coPurchasedProducts): array
    {
        $productIds = $coPurchasedProducts->pluck('product_id')->toArray();
        $prices = Product::whereIn('id', $productIds)->pluck('price')->toArray();

        return ['min_price' => min($prices), 'max_price' => max($prices), 'avg_price' => array_sum($prices) / count($prices), 'median_price' => $this->calculateMedian($prices)];
    }

    /**
     * Handle calculateMedian functionality with proper error handling.
     */
    private function calculateMedian(array $values): float
    {
        sort($values);
        $count = count($values);
        $middle = floor($count / 2);
        if ($count % 2 == 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        }

        return $values[$middle];
    }
}
