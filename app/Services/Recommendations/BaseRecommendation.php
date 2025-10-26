<?php

declare(strict_types=1);

namespace App\Services\Recommendations;

use App\Models\Product;
use App\Models\User;
use App\Models\UserBehavior;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * BaseRecommendation
 *
 * Service class containing BaseRecommendation business logic, external integrations, and complex operations with proper error handling and logging.
 *
 * @property array $config
 * @property int   $maxResults
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
     */
    abstract protected function getDefaultConfig(): array;

    /**
     * Handle getRecommendations functionality with proper error handling.
     */
    abstract public function getRecommendations(?User $user = null, ?Product $product = null, array $context = []): Collection;

    /**
     * Handle applyFilters functionality with proper error handling.
     *
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
     *
     * @param mixed $query
     */
    protected function applyFilter($query, array $filter)
    {
        $type = $filter['type'] ?? null;
        $field = $filter['field'] ?? null;
        $value = $filter['value'] ?? null;
        $operator = $filter['operator'] ?? '=';
        if (! $type || ! $field) {
            return $query;
        }

        return match ($type) {
            'where'        => $query->where($field, $operator, $value),
            'whereIn'      => $query->whereIn($field, $value),
            'whereNotIn'   => $query->whereNotIn($field, $value),
            'whereBetween' => $query->whereBetween($field, $value),
            'whereHas'     => $query->whereHas($field, function ($q) use ($value) {
                $q->where($value['field'], $value['operator'] ?? '=', $value['value']);
            }),
            default => $query,
        };
    }

    /**
     * Handle cacheResult functionality with proper error handling.
     */
    protected function cacheResult(string $key, Collection $result, int $ttl = 3600): Collection
    {
        // Default to caching an empty marker so downstream callers can short-circuit quickly.
        $payload = ['type' => 'empty'];

        if ($result->isNotEmpty()) {
            $firstItem = $result->first();

            // When the collection contains Eloquent models we cache identifiers and relation names
            // so cache hits can be re-hydrated with the same eager-loaded associations.
            if ($firstItem instanceof Model) {
                $modelClass = $firstItem::class;
                $keyName = (new $modelClass())->getKeyName();

                $payload = [
                    'type'       => 'model_collection',
                    'model'      => $modelClass,
                    'key_name'   => $keyName,
                    'ids'        => $result->map(static function (Model $model) use ($keyName): int|string|null {
                        // Capture the model key for deterministic ordering after cache retrieval.
                        return $model->getAttribute($keyName);
                    })->filter()->values()->all(),
                    'relations'  => array_keys($firstItem->getRelations()),
                ];
            } else {
                // Fallback to storing the raw array data for non-model collections.
                $payload = ['type' => 'array', 'items' => $result->toArray()];
            }
        }

        Cache::put($key, $payload, $ttl);

        return $result;
    }

    /**
     * Handle getCachedResult functionality with proper error handling.
     */
    protected function getCachedResult(string $key): ?Collection
    {
        $cached = Cache::get($key);

        if (! $cached) {
            // No cache hit.
            return null;
        }

        if (Arr::get($cached, 'type') === 'empty') {
            // Respect cached empty results to avoid redundant database work.
            return new Collection();
        }

        // Handle hydrated model collections by re-querying the database with eager-loaded relations.
        if (Arr::get($cached, 'type') === 'model_collection') {
            $modelClass = Arr::get($cached, 'model');
            $ids = Arr::get($cached, 'ids', []);
            $keyName = Arr::get($cached, 'key_name');

            if (! $modelClass || empty($ids) || ! is_subclass_of($modelClass, Model::class)) {
                return new Collection();
            }

            $query = $modelClass::query();

            // Apply eager-loaded relations if they were cached previously.
            $relations = Arr::get($cached, 'relations', []);
            if (! empty($relations)) {
                $query->with($relations);
            }

            $models = $query->whereIn($keyName, $ids)->get()->keyBy($keyName);

            $ordered = collect($ids)
                ->map(static function ($id) use ($models) {
                    // Rebuild the collection in the cached order while dropping missing records.
                    return $models->get($id);
                })
                ->filter()
                ->values();

            return new Collection($ordered->all());
        }

        // For raw arrays we simply convert back into a collection of the stored items.
        if (Arr::get($cached, 'type') === 'array') {
            return new Collection(Arr::get($cached, 'items', []));
        }

        // Support legacy cache payloads that stored entire collections.
        if ($cached instanceof Collection) {
            return new Collection($cached->all());
        }

        return null;
    }

    /**
     * Handle generateCacheKey functionality with proper error handling.
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
        if (! empty($context)) {
            $parts[] = 'context:' . md5(serialize($context));
        }

        return implode('|', $parts);
    }

    /**
     * Handle logPerformance functionality with proper error handling.
     */
    protected function logPerformance(string $algorithm, float $time, int $resultCount): void
    {
        Log::info('Recommendation Performance', ['algorithm' => $algorithm, 'execution_time' => $time, 'result_count' => $resultCount, 'timestamp' => now()]);
    }

    /**
     * Handle calculateSimilarity functionality with proper error handling.
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
     */
    protected function normalizeVector(array $vector): array
    {
        $magnitude = sqrt(array_sum(array_map(fn ($v) => $v * $v, $vector)));
        if ($magnitude == 0) {
            return $vector;
        }

        return array_map(fn ($v) => $v / $magnitude, $vector);
    }

    /**
     * Handle getProductFeatures functionality with proper error handling.
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
        // Cast to float because decimal casts surface as strings when the
        // model is hydrated, but our price buckets expect numeric comparison.
        $priceRange = $this->getPriceRange((float) $product->price);
        $features["price_range_{$priceRange}"] = 1.0;
        // Attribute features
        foreach ($product->attributes as $attribute) {
            $features["attr_{$attribute->id}"] = 1.0;
        }

        return $features;
    }

    /**
     * Handle getPriceRange functionality with proper error handling.
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
     */
    protected function trackRecommendation(string $algorithm, ?User $user = null, ?Product $product = null, array $recommendations = []): void
    {
        if (class_exists(UserBehavior::class)) {
            UserBehavior::create([
                'user_id'       => $user?->id,
                'session_id'    => session()->getId(),
                'product_id'    => $product?->id,
                'behavior_type' => 'recommendation_view',
                'metadata'      => [
                    'algorithm'            => $algorithm,
                    'recommendation_count' => count($recommendations),
                    'recommended_products' => array_column($recommendations, 'id'),
                ],
                'created_at' => now(),
            ]);
        }
    }
}
