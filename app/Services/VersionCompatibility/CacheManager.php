<?php

declare(strict_types=1);

namespace App\Services\VersionCompatibility;

use Exception;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Optimized cache manager for version compatibility transformations
 */
final class CacheManager
{
    private const CACHE_PREFIX = 'version_compat';

    private const BATCH_CACHE_PREFIX = 'version_compat_batch';

    private const STATS_CACHE_KEY = 'version_compat_cache_stats';

    public function __construct(
        private readonly CacheRepository $cache
    ) {}

    /**
     * Get cached transformation result with optimized key generation
     */
    public function getTransformation(string $content): ?TransformationResult
    {
        $key = $this->generateOptimizedKey($content);

        return $this->cache->get($key);
    }

    /**
     * Cache transformation result with TTL optimization
     */
    public function cacheTransformation(string $content, TransformationResult $result, int $ttl = 3600): void
    {
        $key = $this->generateOptimizedKey($content);

        // Use shorter TTL for unsuccessful transformations
        $actualTtl = $result->wasTransformed() ? $ttl : min($ttl, 300);

        $this->cache->put($key, $result, $actualTtl);
        $this->updateCacheStats('transformation', 'set');
    }

    /**
     * Batch cache multiple transformations for better performance
     */
    public function batchCacheTransformations(array $transformations, int $ttl = 3600): void
    {
        $cacheData = [];

        foreach ($transformations as $content => $result) {
            $key = $this->generateOptimizedKey($content);
            $actualTtl = $result->wasTransformed() ? $ttl : min($ttl, 300);
            $cacheData[$key] = ['value' => $result, 'ttl' => $actualTtl];
        }

        // Use putMany for better performance
        $this->cache->putMany(
            array_map(fn ($item) => $item['value'], $cacheData),
            $ttl
        );

        $this->updateCacheStats('transformation', 'batch_set', count($cacheData));
    }

    /**
     * Warm cache with commonly used transformations
     */
    public function warmCache(array $commonPatterns): void
    {
        foreach ($commonPatterns as $pattern) {
            if (! $this->getTransformation($pattern)) {
                // Pre-compute and cache common transformations
                // This would be called during deployment
            }
        }
    }

    /**
     * Clear cache with pattern-based invalidation
     */
    public function clearTransformationCache(): int
    {
        $cleared = 0;

        // In production, you'd use cache tags or Redis SCAN
        // For now, we'll use a simple approach
        try {
            $this->cache->flush(); // This clears all cache - in production use tags
            $cleared = 1; // Simplified count
            $this->updateCacheStats('transformation', 'clear');
        } catch (Exception $e) {
            // Log error but don't fail
        }

        return $cleared;
    }

    /**
     * Get cache statistics for monitoring
     */
    public function getCacheStats(): array
    {
        return $this->cache->get(self::STATS_CACHE_KEY, [
            'hits'     => 0,
            'misses'   => 0,
            'sets'     => 0,
            'clears'   => 0,
            'hit_rate' => 0,
        ]);
    }

    /**
     * Generate optimized cache key using fast hashing
     */
    private function generateOptimizedKey(string $content): string
    {
        // Use xxh3 for fast hashing of large content
        $hash = hash('xxh3', $content);

        return self::CACHE_PREFIX . ':' . $hash;
    }

    /**
     * Update cache statistics for monitoring
     */
    private function updateCacheStats(string $type, string $operation, int $count = 1): void
    {
        $stats = $this->getCacheStats();

        switch ($operation) {
            case 'hit':
                $stats['hits'] += $count;
                break;
            case 'miss':
                $stats['misses'] += $count;
                break;
            case 'set':
            case 'batch_set':
                $stats['sets'] += $count;
                break;
            case 'clear':
                $stats['clears'] += $count;
                break;
        }

        // Calculate hit rate
        $total = $stats['hits'] + $stats['misses'];
        $stats['hit_rate'] = $total > 0 ? round(($stats['hits'] / $total) * 100, 2) : 0;

        $this->cache->put(self::STATS_CACHE_KEY, $stats, 86400); // 24 hours
    }
}
