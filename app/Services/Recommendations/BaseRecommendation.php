<?php

declare (strict_types=1);
namespace App\Services\Recommendations;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
/**
 * BaseRecommendation
 * 
 * Service class containing BaseRecommendation business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 * @property array $config
 * @property int $maxResults
 * @property float $minScore
 * @property array $filters
 */
abstract class BaseRecommendation
{
    protected array $config = [];
    protected int $maxResults = 10;
    protected float $minScore = 0.1;
    protected array $filters = [];
    /**
     * Initialize the class instance with required dependencies.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->maxResults = $this->config['max_results'] ?? 10;
        $this->minScore = $this->config['min_score'] ?? 0.1;
        $this->filters = $this->config['filters'] ?? [];
    }
    /**
     * Handle getDefaultConfig functionality with proper error handling.
     * @return array
     */
    abstract protected function getDefaultConfig(): array;
    /**
     * Handle getRecommendations functionality with proper error handling.
     * @param User|null $user
     * @param Product|null $product
     * @param array $context
     * @return Collection
     */
    abstract public function getRecommendations(?User $user = null, ?Product $product = null, array $context = []): Collection;
    /**
     * Handle applyFilters functionality with proper error handling.
     * @param mixed $query
     */
    protected function applyFilters($query)
    {
        if (empty($this->filters)) {
            return $query;
        }
        foreach ($this->filters as $filter) {
            $query = $this->applyFilter($query, $filter);
        }
        return $query;
    }
    /**
     * Handle applyFilter functionality with proper error handling.
     * @param mixed $query
     * @param array $filter
     */
    protected function applyFilter($query, array $filter)
    {
        $type = $filter['type'] ?? null;
        $field = $filter['field'] ?? null;
        $value = $filter['value'] ?? null;
        $operator = $filter['operator'] ?? '=';
        if (!$type || !$field) {
            return $query;
        }
        return match ($type) {
            'where' => $query->where($field, $operator, $value),
            'whereIn' => $query->whereIn($field, $value),
            'whereNotIn' => $query->whereNotIn($field, $value),
            'whereBetween' => $query->whereBetween($field, $value),
            'whereHas' => $query->whereHas($field, function ($q) use ($value) {
                $q->where($value['field'], $value['operator'] ?? '=', $value['value']);
            }),
            default => $query,
        };
    }
    /**
     * Handle cacheResult functionality with proper error handling.
     * @param string $key
     * @param Collection $result
     * @param int $ttl
     * @return Collection
     */
    protected function cacheResult(string $key, Collection $result, int $ttl = 3600): Collection
    {
        Cache::put($key, $result->toArray(), $ttl);
        return $result;
    }
    /**
     * Handle getCachedResult functionality with proper error handling.
     * @param string $key
     * @return Collection|null
     */
    protected function getCachedResult(string $key): ?Collection
    {
        $cached = Cache::get($key);
        return $cached ? collect($cached) : null;
    }
    /**
     * Handle generateCacheKey functionality with proper error handling.
     * @param string $prefix
     * @param User|null $user
     * @param Product|null $product
     * @param array $context
     * @return string
     */
    protected function generateCacheKey(string $prefix, ?User $user = null, ?Product $product = null, array $context = []): string
    {
        $parts = [$prefix];
        if ($user) {
            $parts[] = "user:{$user->id}";
        }
        if ($product) {
            $parts[] = "product:{$product->id}";
        }
        if (!empty($context)) {
            $parts[] = 'context:' . md5(serialize($context));
        }
        return implode('|', $parts);
    }
    /**
     * Handle logPerformance functionality with proper error handling.
     * @param string $algorithm
     * @param float $time
     * @param int $resultCount
     * @return void
     */
    protected function logPerformance(string $algorithm, float $time, int $resultCount): void
    {
        Log::info('Recommendation Performance', ['algorithm' => $algorithm, 'execution_time' => $time, 'result_count' => $resultCount, 'timestamp' => now()]);
    }
    /**
     * Handle calculateSimilarity functionality with proper error handling.
     * @param array $vector1
     * @param array $vector2
     * @return float
     */
    protected function calculateSimilarity(array $vector1, array $vector2): float
    {
        if (empty($vector1) || empty($vector2)) {
            return 0.0;
        }
        $keys = array_intersect_key($vector1, $vector2);
        if (empty($keys)) {
            return 0.0;
        }
        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;
        foreach ($keys as $key => $value1) {
            $value2 = $vector2[$key];
            $dotProduct += $value1 * $value2;
            $magnitude1 += $value1 * $value1;
            $magnitude2 += $value2 * $value2;
        }
        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0.0;
        }
        return $dotProduct / (sqrt($magnitude1) * sqrt($magnitude2));
    }
    /**
     * Handle normalizeVector functionality with proper error handling.
     * @param array $vector
     * @return array
     */
    protected function normalizeVector(array $vector): array
    {
        $magnitude = sqrt(array_sum(array_map(fn($v) => $v * $v, $vector)));
        if ($magnitude == 0) {
            return $vector;
        }
        return array_map(fn($v) => $v / $magnitude, $vector);
    }
    /**
     * Handle getProductFeatures functionality with proper error handling.
     * @param Product $product
     * @return array
     */
    protected function getProductFeatures(Product $product): array
    {
        $features = [];
        // Category features
        $categoryIds = $product->categories->pluck('id')->toArray();
        foreach ($categoryIds as $categoryId) {
            $features["category_{$categoryId}"] = 1.0;
        }
        // Brand features
        if ($product->brand_id) {
            $features["brand_{$product->brand_id}"] = 1.0;
        }
        // Price range features
        $priceRange = $this->getPriceRange($product->price);
        $features["price_range_{$priceRange}"] = 1.0;
        // Attribute features
        foreach ($product->attributes as $attribute) {
            $features["attr_{$attribute->id}"] = 1.0;
        }
        return $features;
    }
    /**
     * Handle getPriceRange functionality with proper error handling.
     * @param float $price
     * @return string
     */
    protected function getPriceRange(float $price): string
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
     * Handle trackRecommendation functionality with proper error handling.
     * @param string $algorithm
     * @param User|null $user
     * @param Product|null $product
     * @param array $recommendations
     * @return void
     */
    protected function trackRecommendation(string $algorithm, ?User $user = null, ?Product $product = null, array $recommendations = []): void
    {
        if (class_exists(\App\Models\UserBehavior::class)) {
            \App\Models\UserBehavior::create(['user_id' => $user?->id, 'session_id' => session()->getId(), 'product_id' => $product?->id, 'behavior_type' => 'recommendation_view', 'metadata' => ['algorithm' => $algorithm, 'recommendation_count' => count($recommendations), 'recommended_products' => array_column($recommendations, 'id')], 'created_at' => now()]);
        }
    }
}