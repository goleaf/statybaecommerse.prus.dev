<?php

declare (strict_types=1);
namespace App\Services;

use App\Models\Product;
use App\Models\RecommendationBlock;
use App\Models\RecommendationCache;
use App\Models\User;
use App\Services\Recommendations\BaseRecommendation;
use App\Services\Recommendations\ContentBasedRecommendation;
use App\Services\Recommendations\CollaborativeFilteringRecommendation;
use App\Services\Recommendations\HybridRecommendation;
use App\Services\Recommendations\PopularityRecommendation;
use App\Services\Recommendations\TrendingRecommendation;
use App\Services\Recommendations\CrossSellRecommendation;
use App\Services\Recommendations\UpSellRecommendation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;
/**
 * RecommendationService
 * 
 * Service class containing RecommendationService business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 * @property array $algorithmInstances
 */
final class RecommendationService
{
    private array $algorithmInstances = [];
    /**
     * Handle getRecommendations functionality with proper error handling.
     * @param string $blockName
     * @param User|null $user
     * @param Product|null $product
     * @param array $context
     * @return Collection
     */
    public function getRecommendations(string $blockName, ?User $user = null, ?Product $product = null, array $context = []): Collection
    {
        $startTime = microtime(true);
        try {
            // Get recommendation block configuration
            $block = RecommendationBlock::where('name', $blockName)->active()->first();
            if (!$block) {
                Log::warning("Recommendation block '{$blockName}' not found or inactive");
                return $this->getFallbackRecommendations($user, $product, $context);
            }
            // Check cache first
            $cacheKey = $this->generateCacheKey($block, $user, $product, $context);
            $cached = $this->getCachedRecommendations($cacheKey);
            if ($cached) {
                return $cached;
            }
            // Generate recommendations using configured algorithms
            $recommendations = $this->generateRecommendations($block, $user, $product, $context);
            // Cache the results
            $this->cacheRecommendations($cacheKey, $block, $user, $product, $context, $recommendations);
            // Track performance
            $executionTime = microtime(true) - $startTime;
            $this->trackPerformance($blockName, $executionTime, $recommendations->count());
            return $recommendations;
        } catch (\Exception $e) {
            Log::error("Recommendation generation failed for block '{$blockName}'", ['error' => $e->getMessage(), 'user_id' => $user?->id, 'product_id' => $product?->id, 'context' => $context]);
            return $this->getFallbackRecommendations($user, $product, $context);
        }
    }
    /**
     * Handle generateRecommendations functionality with proper error handling.
     * @param RecommendationBlock $block
     * @param User|null $user
     * @param Product|null $product
     * @param array $context
     * @return Collection
     */
    private function generateRecommendations(RecommendationBlock $block, ?User $user = null, ?Product $product = null, array $context = []): Collection
    {
        $configs = $block->getConfigs();
        $allRecommendations = collect();
        // Use LazyCollection with timeout to prevent long-running recommendation generation
        $timeout = now()->addSeconds(30);
        // 30 second timeout for recommendation generation
        LazyCollection::make($configs)->takeUntilTimeout($timeout)->each(function ($config) use (&$allRecommendations, $user, $product, $context) {
            try {
                $algorithm = $this->getAlgorithmInstance($config->type, $config->config);
                $recommendations = $algorithm->getRecommendations($user, $product, $context);
                if ($recommendations->isNotEmpty()) {
                    $allRecommendations = $allRecommendations->merge($recommendations);
                }
            } catch (\Exception $e) {
                Log::error("Algorithm '{$config->type}' failed", ['error' => $e->getMessage(), 'config_id' => $config->id]);
            }
        });
        // Remove duplicates and limit results
        return $allRecommendations->unique('id')->skipWhile(function ($product) {
            // Skip products with low relevance scores or missing essential data
            return $product->relevance_score < 0.3 || empty($product->name) || !$product->is_visible || $product->price <= 0;
        })->take($block->max_products);
    }
    /**
     * Handle getAlgorithmInstance functionality with proper error handling.
     * @param string $type
     * @param array $config
     * @return BaseRecommendation
     */
    private function getAlgorithmInstance(string $type, array $config = []): BaseRecommendation
    {
        $key = $type . '_' . md5(serialize($config));
        if (!isset($this->algorithmInstances[$key])) {
            $this->algorithmInstances[$key] = match ($type) {
                'content_based' => new ContentBasedRecommendation($config),
                'collaborative' => new CollaborativeFilteringRecommendation($config),
                'hybrid' => new HybridRecommendation($config),
                'popularity' => new PopularityRecommendation($config),
                'trending' => new TrendingRecommendation($config),
                'cross_sell' => new CrossSellRecommendation($config),
                'up_sell' => new UpSellRecommendation($config),
                default => new PopularityRecommendation($config),
            };
        }
        return $this->algorithmInstances[$key];
    }
    /**
     * Handle generateCacheKey functionality with proper error handling.
     * @param RecommendationBlock $block
     * @param User|null $user
     * @param Product|null $product
     * @param array $context
     * @return string
     */
    private function generateCacheKey(RecommendationBlock $block, ?User $user = null, ?Product $product = null, array $context = []): string
    {
        return RecommendationCache::generateCacheKey($block->name, $user?->id, $product?->id, $context['type'] ?? null, $context);
    }
    /**
     * Handle getCachedRecommendations functionality with proper error handling.
     * @param string $cacheKey
     * @return Collection|null
     */
    private function getCachedRecommendations(string $cacheKey): ?Collection
    {
        $cached = RecommendationCache::where('cache_key', $cacheKey)->valid()->first();
        if ($cached) {
            $cached->incrementHitCount();
            return collect($cached->recommendations);
        }
        return null;
    }
    /**
     * Handle cacheRecommendations functionality with proper error handling.
     * @param string $cacheKey
     * @param RecommendationBlock $block
     * @param User|null $user
     * @param Product|null $product
     * @param array $context
     * @param Collection $recommendations
     * @return void
     */
    private function cacheRecommendations(string $cacheKey, RecommendationBlock $block, ?User $user = null, ?Product $product = null, array $context = [], Collection $recommendations = null): void
    {
        if (!$recommendations || $recommendations->isEmpty()) {
            return;
        }
        RecommendationCache::updateOrCreate(['cache_key' => $cacheKey], ['block_id' => $block->id, 'user_id' => $user?->id, 'product_id' => $product?->id, 'context_type' => $context['type'] ?? null, 'context_data' => $context, 'recommendations' => $recommendations->toArray(), 'hit_count' => 0, 'expires_at' => now()->addSeconds($block->cache_duration)]);
    }
    /**
     * Handle getFallbackRecommendations functionality with proper error handling.
     * @param User|null $user
     * @param Product|null $product
     * @param array $context
     * @return Collection
     */
    private function getFallbackRecommendations(?User $user = null, ?Product $product = null, array $context = []): Collection
    {
        // Simple fallback to popular products
        $fallbackAlgorithm = new PopularityRecommendation();
        return $fallbackAlgorithm->getRecommendations($user, $product, $context);
    }
    /**
     * Handle trackPerformance functionality with proper error handling.
     * @param string $blockName
     * @param float $executionTime
     * @param int $resultCount
     * @return void
     */
    private function trackPerformance(string $blockName, float $executionTime, int $resultCount): void
    {
        Log::info('Recommendation Performance', ['block' => $blockName, 'execution_time' => $executionTime, 'result_count' => $resultCount, 'timestamp' => now()]);
    }
    /**
     * Handle trackUserInteraction functionality with proper error handling.
     * @param User $user
     * @param Product $product
     * @param string $interactionType
     * @param float|null $rating
     * @return void
     */
    public function trackUserInteraction(User $user, Product $product, string $interactionType, ?float $rating = null): void
    {
        try {
            // Track in user behavior
            if (class_exists(\App\Models\UserBehavior::class)) {
                \App\Models\UserBehavior::create(['user_id' => $user->id, 'product_id' => $product->id, 'behavior_type' => $interactionType, 'metadata' => ['rating' => $rating, 'timestamp' => now()], 'created_at' => now()]);
            }
            // Update collaborative filtering data
            if (class_exists(\App\Models\UserProductInteraction::class)) {
                $collaborative = new CollaborativeFilteringRecommendation();
                $collaborative->updateUserInteraction($user, $product, $interactionType, $rating);
            }
            // Update user preferences
            $this->updateUserPreferences($user, $product, $interactionType);
        } catch (\Exception $e) {
            Log::error('Failed to track user interaction', ['error' => $e->getMessage(), 'user_id' => $user->id, 'product_id' => $product->id, 'interaction_type' => $interactionType]);
        }
    }
    /**
     * Handle updateUserPreferences functionality with proper error handling.
     * @param User $user
     * @param Product $product
     * @param string $interactionType
     * @return void
     */
    private function updateUserPreferences(User $user, Product $product, string $interactionType): void
    {
        if (!class_exists(\App\Models\UserPreference::class)) {
            return;
        }
        $preferenceScore = match ($interactionType) {
            'view' => 0.1,
            'click' => 0.2,
            'cart' => 0.4,
            'purchase' => 0.8,
            'wishlist' => 0.6,
            'review' => 0.7,
            default => 0.1,
        };
        // Update category preferences
        foreach ($product->categories as $category) {
            \App\Models\UserPreference::updateOrCreate(['user_id' => $user->id, 'preference_type' => 'category', 'preference_key' => $category->id], ['preference_score' => DB::raw("GREATEST(preference_score + {$preferenceScore}, 1.0)"), 'last_updated' => now()]);
        }
        // Update brand preferences
        if ($product->brand_id) {
            \App\Models\UserPreference::updateOrCreate(['user_id' => $user->id, 'preference_type' => 'brand', 'preference_key' => $product->brand_id], ['preference_score' => DB::raw("GREATEST(preference_score + {$preferenceScore}, 1.0)"), 'last_updated' => now()]);
        }
        // Update price range preferences
        $priceRange = $this->getPriceRange($product->price);
        \App\Models\UserPreference::updateOrCreate(['user_id' => $user->id, 'preference_type' => 'price_range', 'preference_key' => $priceRange], ['preference_score' => DB::raw("GREATEST(preference_score + {$preferenceScore}, 1.0)"), 'last_updated' => now()]);
    }
    /**
     * Handle getPriceRange functionality with proper error handling.
     * @param float $price
     * @return string
     */
    private function getPriceRange(float $price): string
    {
        if ($price < 10) {
            return 'budget';
        }
        if ($price < 50) {
            return 'low';
        }
        if ($price < 100) {
            return 'medium';
        }
        if ($price < 500) {
            return 'high';
        }
        return 'premium';
    }
    /**
     * Handle getRecommendationBlocks functionality with proper error handling.
     * @return Collection
     */
    public function getRecommendationBlocks(): Collection
    {
        return RecommendationBlock::active()->get();
    }
    /**
     * Handle clearCache functionality with proper error handling.
     * @param string|null $blockName
     * @return void
     */
    public function clearCache(?string $blockName = null): void
    {
        try {
            if ($blockName) {
                RecommendationCache::whereHas('block', function ($query) use ($blockName) {
                    $query->where('name', $blockName);
                })->delete();
            } else {
                RecommendationCache::truncate();
            }
        } catch (\Exception $e) {
            // Table might not exist yet, ignore
        }
        // Clear Laravel cache as well
        Cache::flush();
    }
    /**
     * Handle getAnalytics functionality with proper error handling.
     * @param string $blockName
     * @param int $days
     * @return array
     */
    public function getAnalytics(string $blockName, int $days = 30): array
    {
        $block = RecommendationBlock::where('name', $blockName)->first();
        if (!$block) {
            return ['block_name' => $blockName, 'total_requests' => 0, 'unique_requests' => 0, 'avg_products_per_request' => 0, 'cache_hit_rate' => 0];
        }
        try {
            return ['block_name' => $blockName, 'total_requests' => RecommendationCache::where('block_id', $block->id)->where('created_at', '>=', now()->subDays($days))->sum('hit_count'), 'unique_requests' => RecommendationCache::where('block_id', $block->id)->where('created_at', '>=', now()->subDays($days))->count(), 'avg_products_per_request' => RecommendationCache::where('block_id', $block->id)->where('created_at', '>=', now()->subDays($days))->avg(DB::raw('JSON_LENGTH(recommendations)')), 'cache_hit_rate' => $this->calculateCacheHitRate($block->id, $days)];
        } catch (\Exception $e) {
            return ['block_name' => $blockName, 'total_requests' => 0, 'unique_requests' => 0, 'avg_products_per_request' => 0, 'cache_hit_rate' => 0];
        }
    }
    /**
     * Handle calculateCacheHitRate functionality with proper error handling.
     * @param int $blockId
     * @param int $days
     * @return float
     */
    private function calculateCacheHitRate(int $blockId, int $days): float
    {
        try {
            $totalRequests = RecommendationCache::where('block_id', $blockId)->where('created_at', '>=', now()->subDays($days))->sum('hit_count');
            $cacheHits = RecommendationCache::where('block_id', $blockId)->where('created_at', '>=', now()->subDays($days))->where('hit_count', '>', 0)->sum('hit_count');
            return $totalRequests > 0 ? $cacheHits / $totalRequests * 100 : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
    /**
     * Handle optimizeRecommendations functionality with proper error handling.
     * @return void
     */
    public function optimizeRecommendations(): void
    {
        try {
            // Clean up expired cache entries
            RecommendationCache::expired()->delete();
        } catch (\Exception $e) {
            // Table might not exist yet, ignore
        }
        // Clean up old user behaviors (keep last 90 days)
        if (class_exists(\App\Models\UserBehavior::class)) {
            try {
                \App\Models\UserBehavior::where('created_at', '<', now()->subDays(90))->delete();
            } catch (\Exception $e) {
                // Table might not exist yet, ignore
            }
        }
        // Clean up old product similarities (keep last 30 days)
        if (class_exists(\App\Models\ProductSimilarity::class)) {
            try {
                \App\Models\ProductSimilarity::where('calculated_at', '<', now()->subDays(30))->delete();
            } catch (\Exception $e) {
                // Table might not exist yet, ignore
            }
        }
    }
}