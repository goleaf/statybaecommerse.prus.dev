<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\RecommendationAnalytics;
use App\Models\RecommendationBlock;
use App\Models\RecommendationCache;
use App\Models\User;
use App\Services\Recommendations\BaseRecommendation;
use App\Services\Recommendations\CategoryBasedRecommendation;
use App\Services\Recommendations\CollaborativeFilteringRecommendation;
use App\Services\Recommendations\ContentBasedRecommendation;
use App\Services\Recommendations\CrossSellRecommendation;
use App\Services\Recommendations\HybridRecommendation;
use App\Services\Recommendations\PersonalizedRecommendation;
use App\Services\Recommendations\PopularityRecommendation;
use App\Services\Recommendations\TrendingRecommendation;
use App\Services\Recommendations\UpSellRecommendation;
use Exception;
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
     * Cache the most recent per-config metrics so downstream calls (caching,
     * analytics, A/B comparisons) can reuse a consistent snapshot.
     *
     * @var array<int, array<string, mixed>>
     */
    private array $lastConfigMetrics = [];

    /**
     * Handle getRecommendations functionality with proper error handling.
     */
    public function getRecommendations(string $blockName, ?User $user = null, ?Product $product = null, array $context = []): Collection
    {
        $startTime = microtime(true);
        try {
            // Get recommendation block configuration
            $block = RecommendationBlock::where('name', $blockName)->active()->first();
            if (! $block) {
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
            $this->cacheRecommendations($cacheKey, $block, $user, $product, $context, $recommendations, $this->lastConfigMetrics);
            // Track performance
            $executionTime = microtime(true) - $startTime;
            $this->trackPerformance($block, $executionTime, $recommendations->count());

            return $recommendations;
        } catch (Exception $e) {
            Log::error("Recommendation generation failed for block '{$blockName}'", ['error' => $e->getMessage(), 'user_id' => $user?->id, 'product_id' => $product?->id, 'context' => $context]);

            return $this->getFallbackRecommendations($user, $product, $context);
        }
    }

    /**
     * Handle generateRecommendations functionality with proper error handling.
     */
    private function generateRecommendations(RecommendationBlock $block, ?User $user = null, ?Product $product = null, array $context = []): Collection
    {
        $configs = $block->getConfigs();
        if ($configs->isEmpty()) {
            // When no explicit configuration is attached fall back to the default algorithm map per block type.
            $configs = collect($this->resolveDefaultAlgorithms($block->type))
                ->map(function (string $algorithm): object {
                    return (object) [
                        'type'   => $algorithm,
                        'config' => [],
                        'id'     => null,
                    ];
                });
        }
        $allRecommendations = collect();
        $this->lastConfigMetrics = [];
        // Use LazyCollection with timeout to prevent long-running recommendation generation
        $timeout = now()->addSeconds(30);
        // 30 second timeout for recommendation generation
        LazyCollection::make($configs)->takeUntilTimeout($timeout)->each(function ($config) use (&$allRecommendations, $user, $product, $context): void {
            try {
                $configStart = microtime(true);
                $algorithm = $this->getAlgorithmInstance($config->type, $config->config);
                $recommendations = $algorithm->getRecommendations($user, $product, $context);
                if ($recommendations->isNotEmpty()) {
                    $allRecommendations = $allRecommendations->merge($recommendations);
                }
                $this->lastConfigMetrics[] = [
                    'config_id'       => $config->id,
                    'config_name'     => $config->name,
                    'type'            => $config->type,
                    'algorithm_class' => $algorithm::class,
                    'result_count'    => $recommendations->count(),
                    'execution_time'  => microtime(true) - $configStart,
                    'context'         => $context,
                ];
            } catch (Exception $e) {
                Log::error("Algorithm '{$config->type}' failed", ['error' => $e->getMessage(), 'config_id' => $config->id]);
            }
        });

        $this->recordConfigAnalytics($block);

        // Remove duplicates and filter out low quality results before limiting
        $filtered = $allRecommendations
            ->filter(function ($product): bool {
                // Guard against unexpected payloads and ensure only high-quality, merchandisable products surface
                return $product instanceof Product
                    && $product->relevance_score >= 0.3
                    && ! empty($product->name)
                    && $product->is_visible
                    && $product->price > 0;
            })
            ->unique('id')
            ->values()
            ->take($block->max_products);

        // Return a fresh Eloquent collection instance so downstream code receives
        // the familiar Collection contract without relying on builder helpers
        // that are unavailable in SQLite test contexts.
        return new Collection($filtered->all());
    }

    /**
     * Handle getAlgorithmInstance functionality with proper error handling.
     */
    private function getAlgorithmInstance(string $type, array $config = []): BaseRecommendation
    {
        $key = $type . '_' . md5(serialize($config));
        if (! isset($this->algorithmInstances[$key])) {
            $this->algorithmInstances[$key] = match ($type) {
                'content_based'  => new ContentBasedRecommendation($config),
                'collaborative'  => new CollaborativeFilteringRecommendation($config),
                'hybrid'         => new HybridRecommendation($config),
                'popularity'     => new PopularityRecommendation($config),
                'trending'       => new TrendingRecommendation($config),
                'personalized'   => new PersonalizedRecommendation($config),
                'category_based' => new CategoryBasedRecommendation($config),
                'cross_sell'     => new CrossSellRecommendation($config),
                'up_sell'        => new UpSellRecommendation($config),
                default          => new PopularityRecommendation($config),
            };
        }

        return $this->algorithmInstances[$key];
    }

    /**
     * Handle generateCacheKey functionality with proper error handling.
     */
    private function generateCacheKey(RecommendationBlock $block, ?User $user = null, ?Product $product = null, array $context = []): string
    {
        return RecommendationCache::generateCacheKey($block->name, $user?->id, $product?->id, $context['type'] ?? null, $context);
    }

    /**
     * Handle getCachedRecommendations functionality with proper error handling.
     */
    private function getCachedRecommendations(string $cacheKey): ?Collection
    {
        $cached = RecommendationCache::where('cache_key', $cacheKey)->valid()->first();
        if ($cached) {
            $cached->incrementHitCount();

            return $this->rehydrateCachedProducts($cached);
        }

        return null;
    }

    /**
     * Handle cacheRecommendations functionality with proper error handling.
     */
    private function cacheRecommendations(string $cacheKey, RecommendationBlock $block, ?User $user = null, ?Product $product = null, array $context = [], ?Collection $recommendations = null, array $configMetrics = []): void
    {
        if (! $recommendations || $recommendations->isEmpty()) {
            return;
        }
        RecommendationCache::updateOrCreate(
            ['cache_key' => $cacheKey],
            [
                'block_id'        => $block->id,
                'user_id'         => $user?->id,
                'product_id'      => $product?->id,
                'context_type'    => $context['type'] ?? null,
                'context_data'    => $context,
                'recommendations' => [
                    'products' => $recommendations->map(fn (Product $item): array => [
                        'id'    => $item->id,
                        'score' => $item->recommendation_score ?? null,
                    ])->all(),
                    'meta' => [
                        'cached_at'      => now()->toIso8601String(),
                        'result_count'   => $recommendations->count(),
                        'config_metrics' => $configMetrics,
                    ],
                ],
                'hit_count'  => 0,
                'expires_at' => now()->addSeconds($block->cache_duration),
            ],
        );
    }

    /**
     * Rehydrate cached recommendation payloads into Product models so Livewire
     * components receive fully-functional entities instead of raw arrays.
     */
    private function rehydrateCachedProducts(RecommendationCache $cache): Collection
    {
        $entries = $cache->recommendations['products'] ?? $cache->recommendations ?? [];
        $ids = collect($entries)
            ->pluck('id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return collect();
        }

        $products = Product::query()
            ->with(['brand', 'media'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $rehydrated = collect($entries)
            ->map(function (array $entry) use ($products) {
                $product = $products->get($entry['id'] ?? 0);
                if (! $product) {
                    return null;
                }
                if (array_key_exists('score', $entry)) {
                    $product->recommendation_score = $entry['score'];
                    $product->relevance_score = $entry['score'];
                }

                return $product;
            })
            ->filter()
            ->values();

        // Instantiate the collection manually to avoid invoking Builder-only
        // helpers that are not present when the query builder is mocked.
        return new Collection($rehydrated->all());
    }

    /**
     * Handle getFallbackRecommendations functionality with proper error handling.
     */
    private function getFallbackRecommendations(?User $user = null, ?Product $product = null, array $context = []): Collection
    {
        // Simple fallback to popular products
        $fallbackAlgorithm = new PopularityRecommendation;

        return $fallbackAlgorithm->getRecommendations($user, $product, $context);
    }

    /**
     * Resolve the default algorithm stack for a recommendation block type.
     *
     * @return list<string>
     */
    private function resolveDefaultAlgorithms(?string $blockType): array
    {
        return match ($blockType) {
            // Similar product widgets prioritise feature-based comparisons.
            'similar_products', 'related' => ['content_based'],
            // Collaborative suggestions thrive on co-purchase behaviour.
            'frequently_bought_together' => ['collaborative'],
            // Trending sections can use the dedicated trending scorer with a popularity fallback.
            'trending_products', 'trending' => ['trending', 'popularity'],
            // Personalised blocks first lean on behaviour then reuse collaborative signals if needed.
            'personalized' => ['personalized', 'collaborative'],
            // Within-category blocks surface contextual alternatives.
            'category_based', 'featured' => ['category_based', 'content_based'],
            default => ['popularity'],
        };
    }

    /**
     * Handle trackPerformance functionality with proper error handling.
     */
    private function trackPerformance(RecommendationBlock $block, float $executionTime, int $resultCount): void
    {
        Log::info('Recommendation Performance', ['block' => $block->name, 'execution_time' => $executionTime, 'result_count' => $resultCount, 'timestamp' => now()]);

        $record = RecommendationAnalytics::query()->firstOrNew([
            'block_id'  => $block->id,
            'config_id' => null,
            'date'      => now()->toDateString(),
            'action'    => 'aggregate',
        ]);

        $metrics = $record->metrics ?? [];
        $requests = ($metrics['requests'] ?? 0) + 1;
        $totalResults = ($metrics['total_results'] ?? 0) + $resultCount;
        $totalExecutionTime = ($metrics['total_execution_time'] ?? 0) + $executionTime;

        $record->metrics = [
            'requests'               => $requests,
            'total_results'          => $totalResults,
            'total_execution_time'   => $totalExecutionTime,
            'average_results'        => $totalResults / max(1, $requests),
            'average_execution_time' => $totalExecutionTime / max(1, $requests),
            'block_name'             => $block->name,
        ];
        $record->save();
    }

    /**
     * Persist per-config analytics for downstream dashboards and A/B testing.
     */
    private function recordConfigAnalytics(RecommendationBlock $block): void
    {
        foreach ($this->lastConfigMetrics as $metric) {
            $record = RecommendationAnalytics::query()->firstOrNew([
                'block_id'  => $block->id,
                'config_id' => $metric['config_id'],
                'date'      => now()->toDateString(),
                'action'    => 'serve',
            ]);

            $existing = $record->metrics ?? [];
            $requests = ($existing['requests'] ?? 0) + 1;
            $totalResults = ($existing['total_results'] ?? 0) + $metric['result_count'];
            $totalExecutionTime = ($existing['total_execution_time'] ?? 0) + $metric['execution_time'];

            $record->metrics = [
                'requests'               => $requests,
                'total_results'          => $totalResults,
                'total_execution_time'   => $totalExecutionTime,
                'average_results'        => $totalResults / max(1, $requests),
                'average_execution_time' => $totalExecutionTime / max(1, $requests),
                'algorithm'              => $metric['type'],
                'algorithm_class'        => $metric['algorithm_class'],
                'config_name'            => $metric['config_name'],
                'last_context'           => $metric['context'],
            ];
            $record->save();
        }
    }

    /**
     * Compare configuration performance for lightweight A/B testing insights.
     */
    public function compareConfigPerformance(RecommendationBlock $block, int $days = 30): array
    {
        $since = now()->subDays($days)->toDateString();
        $analytics = RecommendationAnalytics::query()
            ->byBlock($block->id)
            ->where('action', 'serve')
            ->where('date', '>=', $since)
            ->get();

        if ($analytics->isEmpty()) {
            return [];
        }

        $configs = $block->getConfigs()->keyBy('id');

        return $analytics
            ->groupBy('config_id')
            ->map(function ($records, $configId) use ($configs) {
                $totals = $records->reduce(function (array $carry, RecommendationAnalytics $record): array {
                    $metrics = $record->metrics ?? [];
                    $carry['requests'] += $metrics['requests'] ?? 0;
                    $carry['total_results'] += $metrics['total_results'] ?? 0;
                    $carry['total_execution_time'] += $metrics['total_execution_time'] ?? 0;

                    return $carry;
                }, ['requests' => 0, 'total_results' => 0, 'total_execution_time' => 0]);

                $requests = max(1, $totals['requests']);
                $avgResults = $totals['total_results'] / $requests;
                $avgExecution = $totals['total_execution_time'] / $requests;
                $score = $avgExecution > 0 ? $avgResults / $avgExecution : $avgResults;

                $config = $configs->get((int) $configId);

                return [
                    'config_id'          => (int) $configId,
                    'config_name'        => $config ? $config->name : 'unknown',
                    'requests'           => $totals['requests'],
                    'avg_results'        => $avgResults,
                    'avg_execution_time' => $avgExecution,
                    'score'              => $score,
                    'algorithm'          => $config ? $config->type : null,
                ];
            })
            ->sortByDesc('score')
            ->values()
            ->toArray();
    }

    /**
     * Handle trackUserInteraction functionality with proper error handling.
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
                $collaborative = new CollaborativeFilteringRecommendation;
                $collaborative->updateUserInteraction($user, $product, $interactionType, $rating);
            }
            // Update user preferences
            $this->updateUserPreferences($user, $product, $interactionType);
        } catch (Exception $e) {
            Log::error('Failed to track user interaction', ['error' => $e->getMessage(), 'user_id' => $user->id, 'product_id' => $product->id, 'event' => $interactionType]);
        }
    }

    /**
     * Handle updateUserPreferences functionality with proper error handling.
     */
    private function updateUserPreferences(User $user, Product $product, string $interactionType): void
    {
        if (! class_exists(\App\Models\UserPreference::class)) {
            return;
        }
        $preferenceScore = match ($interactionType) {
            'view'     => 0.1,
            'click'    => 0.2,
            'cart'     => 0.4,
            'purchase' => 0.8,
            'wishlist' => 0.6,
            'review'   => 0.7,
            default    => 0.1,
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
     */
    public function getRecommendationBlocks(): Collection
    {
        return RecommendationBlock::active()->get();
    }

    /**
     * Handle clearCache functionality with proper error handling.
     */
    public function clearCache(?string $blockName = null): void
    {
        try {
            if ($blockName) {
                RecommendationCache::whereHas('block', function ($query) use ($blockName): void {
                    $query->where('name', $blockName);
                })->delete();
            } else {
                RecommendationCache::truncate();
            }
        } catch (Exception $e) {
            // Table might not exist yet, ignore
        }
        // Clear Laravel cache as well
        Cache::flush();
    }

    /**
     * Handle getAnalytics functionality with proper error handling.
     */
    public function getAnalytics(string $blockName, int $days = 30): array
    {
        $block = RecommendationBlock::where('name', $blockName)->first();
        if (! $block) {
            return ['block_name' => $blockName, 'total_requests' => 0, 'unique_requests' => 0, 'avg_products_per_request' => 0, 'cache_hit_rate' => 0];
        }
        try {
            return ['block_name' => $blockName, 'total_requests' => RecommendationCache::where('block_id', $block->id)->where('created_at', '>=', now()->subDays($days))->sum('hit_count'), 'unique_requests' => RecommendationCache::where('block_id', $block->id)->where('created_at', '>=', now()->subDays($days))->count(), 'avg_products_per_request' => RecommendationCache::where('block_id', $block->id)->where('created_at', '>=', now()->subDays($days))->avg(DB::raw('JSON_LENGTH(recommendations)')), 'cache_hit_rate' => $this->calculateCacheHitRate($block->id, $days)];
        } catch (Exception $e) {
            return ['block_name' => $blockName, 'total_requests' => 0, 'unique_requests' => 0, 'avg_products_per_request' => 0, 'cache_hit_rate' => 0];
        }
    }

    /**
     * Handle calculateCacheHitRate functionality with proper error handling.
     */
    private function calculateCacheHitRate(int $blockId, int $days): float
    {
        try {
            $totalRequests = RecommendationCache::where('block_id', $blockId)->where('created_at', '>=', now()->subDays($days))->sum('hit_count');
            $cacheHits = RecommendationCache::where('block_id', $blockId)->where('created_at', '>=', now()->subDays($days))->where('hit_count', '>', 0)->sum('hit_count');

            return $totalRequests > 0 ? $cacheHits / $totalRequests * 100 : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Handle optimizeRecommendations functionality with proper error handling.
     */
    public function optimizeRecommendations(): void
    {
        try {
            // Clean up expired cache entries
            RecommendationCache::expired()->delete();
        } catch (Exception $e) {
            // Table might not exist yet, ignore
        }
        // Clean up old user behaviors (keep last 90 days)
        if (class_exists(\App\Models\UserBehavior::class)) {
            try {
                \App\Models\UserBehavior::where('created_at', '<', now()->subDays(90))->delete();
            } catch (Exception $e) {
                // Table might not exist yet, ignore
            }
        }
        // Clean up old product similarities (keep last 30 days)
        if (class_exists(\App\Models\ProductSimilarity::class)) {
            try {
                \App\Models\ProductSimilarity::where('calculated_at', '<', now()->subDays(30))->delete();
            } catch (Exception $e) {
                // Table might not exist yet, ignore
            }
        }
    }
}
