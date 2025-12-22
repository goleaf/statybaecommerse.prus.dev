<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Optimizes cache operations for better performance.
 */
final class CacheOptimizationService
{
    private const CACHE_PREFIX = 'cache_opt_';

    private const DEFAULT_TTL = 3600; // 1 hour

    /**
     * Cache with optimized serialization and compression.
     */
    public function remember(string $key, mixed $value, ?int $ttl = null): mixed
    {
        $ttl ??= self::DEFAULT_TTL;
        $optimizedKey = $this->optimizeKey($key);

        return Cache::remember($optimizedKey, $ttl, function () use ($value) {
            return $this->optimizeValue($value);
        });
    }

    /**
     * Batch cache operations for better performance.
     */
    public function rememberMany(array $items, ?int $ttl = null): array
    {
        $ttl ??= self::DEFAULT_TTL;
        $results = [];
        $missing = [];

        // Check which items are already cached
        foreach ($items as $key => $callback) {
            $optimizedKey = $this->optimizeKey($key);
            $cached = Cache::get($optimizedKey);

            if ($cached !== null) {
                $results[$key] = $this->unoptimizeValue($cached);
            } else {
                $missing[$key] = $callback;
            }
        }

        // Batch process missing items
        if (! empty($missing)) {
            $newValues = [];

            foreach ($missing as $key => $callback) {
                try {
                    $value = is_callable($callback) ? $callback() : $callback;
                    $optimizedValue = $this->optimizeValue($value);
                    $optimizedKey = $this->optimizeKey($key);

                    $newValues[$optimizedKey] = $optimizedValue;
                    $results[$key] = $value;
                } catch (Throwable $e) {
                    Log::warning('Cache optimization failed for key', [
                        'key'   => $key,
                        'error' => $e->getMessage(),
                    ]);

                    // Fallback to direct execution
                    $results[$key] = is_callable($callback) ? $callback() : $callback;
                }
            }

            // Batch store new values
            if (! empty($newValues)) {
                Cache::putMany($newValues, $ttl);
            }
        }

        return $results;
    }

    /**
     * Invalidate cache with pattern matching.
     */
    public function forgetPattern(string $pattern): int
    {
        $count = 0;

        try {
            // Use Redis SCAN if available for better performance
            if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                $redis = Cache::getStore()->connection();
                $iterator = null;
                $keys = [];

                do {
                    $result = $redis->scan($iterator, [
                        'MATCH' => self::CACHE_PREFIX . $pattern,
                        'COUNT' => 100,
                    ]);

                    if ($result !== false) {
                        $keys = array_merge($keys, $result);
                    }
                } while ($iterator > 0);

                if (! empty($keys)) {
                    $redis->del($keys);
                    $count = count($keys);
                }
            } else {
                // Fallback for other cache drivers
                Log::info('Pattern cache invalidation not optimized for current driver');
            }
        } catch (Throwable $e) {
            Log::warning('Cache pattern invalidation failed', [
                'pattern' => $pattern,
                'error'   => $e->getMessage(),
            ]);
        }

        return $count;
    }

    /**
     * Optimize cache key for better performance.
     */
    private function optimizeKey(string $key): string
    {
        // Add prefix and hash long keys
        $prefixedKey = self::CACHE_PREFIX . $key;

        if (strlen($prefixedKey) > 250) {
            return self::CACHE_PREFIX . hash('xxh3', $key);
        }

        return $prefixedKey;
    }

    /**
     * Optimize value for cache storage.
     */
    private function optimizeValue(mixed $value): mixed
    {
        if (! config('performance.cache.optimize_serialization', true)) {
            return $value;
        }

        // Convert Eloquent models to arrays to reduce serialization overhead
        if (is_object($value) && method_exists($value, 'toArray')) {
            return [
                '_optimized' => true,
                '_type'      => get_class($value),
                'data'       => $value->toArray(),
            ];
        }

        // Optimize collections
        if ($value instanceof \Illuminate\Support\Collection) {
            return [
                '_optimized' => true,
                '_type'      => 'Collection',
                'data'       => $value->toArray(),
            ];
        }

        return $value;
    }

    /**
     * Restore optimized value from cache.
     */
    private function unoptimizeValue(mixed $value): mixed
    {
        if (is_array($value) && isset($value['_optimized']) && $value['_optimized'] === true) {
            $type = $value['_type'] ?? null;
            $data = $value['data'] ?? [];

            if ($type === 'Collection') {
                return collect($data);
            }

            // For other types, return the raw data
            return $data;
        }

        return $value;
    }

    /**
     * Get cache statistics for monitoring.
     */
    public function getStatistics(): array
    {
        try {
            if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                $redis = Cache::getStore()->connection();
                $info = $redis->info('memory');

                return [
                    'memory_used'     => $info['used_memory_human'] ?? 'unknown',
                    'memory_peak'     => $info['used_memory_peak_human'] ?? 'unknown',
                    'keyspace_hits'   => $info['keyspace_hits'] ?? 0,
                    'keyspace_misses' => $info['keyspace_misses'] ?? 0,
                    'hit_rate'        => $this->calculateHitRate($info),
                ];
            }
        } catch (Throwable $e) {
            Log::warning('Failed to get cache statistics', [
                'error' => $e->getMessage(),
            ]);
        }

        return [
            'memory_used'     => 'unknown',
            'memory_peak'     => 'unknown',
            'keyspace_hits'   => 0,
            'keyspace_misses' => 0,
            'hit_rate'        => 0.0,
        ];
    }

    /**
     * Calculate cache hit rate.
     */
    private function calculateHitRate(array $info): float
    {
        $hits = (int) ($info['keyspace_hits'] ?? 0);
        $misses = (int) ($info['keyspace_misses'] ?? 0);
        $total = $hits + $misses;

        if ($total === 0) {
            return 0.0;
        }

        return round(($hits / $total) * 100, 2);
    }
}
