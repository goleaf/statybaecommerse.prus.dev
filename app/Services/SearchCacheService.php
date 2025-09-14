<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

final class SearchCacheService
{
    private const CACHE_PREFIX = 'search_cache:';
    private const DEFAULT_TTL = 3600; // 1 hour
    private const POPULAR_TTL = 7200; // 2 hours
    private const RECENT_TTL = 1800; // 30 minutes
    private const ANALYTICS_TTL = 86400; // 24 hours

    /**
     * Cache search results with intelligent TTL
     */
    public function cacheSearchResults(string $key, array $results, string $query, array $context = []): void
    {
        try {
            $ttl = $this->calculateIntelligentTTL($query, $results, $context);
            $cacheKey = self::CACHE_PREFIX . $key;
            
            $cacheData = [
                'results' => $results,
                'query' => $query,
                'context' => $context,
                'cached_at' => now()->toISOString(),
                'ttl' => $ttl,
                'result_count' => count($results),
            ];
            
            Cache::put($cacheKey, $cacheData, $ttl);
            
            // Store in Redis for advanced operations
            $this->storeInRedis($cacheKey, $cacheData, $ttl);
            
            // Update cache statistics
            $this->updateCacheStatistics($key, $query, count($results));
            
        } catch (\Exception $e) {
            \Log::warning('Search cache storage failed: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve cached search results
     */
    public function getCachedResults(string $key): ?array
    {
        try {
            $cacheKey = self::CACHE_PREFIX . $key;
            $cachedData = Cache::get($cacheKey);
            
            if ($cachedData) {
                // Update access statistics
                $this->updateAccessStatistics($key);
                
                return $cachedData['results'] ?? null;
            }
            
            return null;
        } catch (\Exception $e) {
            \Log::warning('Search cache retrieval failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get cache statistics
     */
    public function getCacheStatistics(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'statistics';
        
        return Cache::remember($cacheKey, 300, function () {
            return [
                'total_cached_queries' => $this->getTotalCachedQueries(),
                'cache_hit_rate' => $this->getCacheHitRate(),
                'most_popular_queries' => $this->getMostPopularQueries(),
                'cache_size' => $this->getCacheSize(),
                'memory_usage' => $this->getMemoryUsage(),
            ];
        });
    }

    /**
     * Warm up cache with popular searches
     */
    public function warmUpCache(array $popularQueries): void
    {
        foreach ($popularQueries as $query) {
            $key = $this->generateCacheKey($query, []);
            
            // Check if already cached
            if (!$this->getCachedResults($key)) {
                // This would typically trigger a search to populate cache
                \Log::info("Warming up cache for query: {$query}");
            }
        }
    }

    /**
     * Clear cache by pattern
     */
    public function clearCacheByPattern(string $pattern): int
    {
        try {
            $keys = Cache::getRedis()->keys(self::CACHE_PREFIX . $pattern);
            $deleted = 0;
            
            foreach ($keys as $key) {
                if (Cache::forget($key)) {
                    $deleted++;
                }
            }
            
            return $deleted;
        } catch (\Exception $e) {
            \Log::warning('Cache pattern clearing failed: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Optimize cache by removing least used entries
     */
    public function optimizeCache(int $maxEntries = 1000): int
    {
        try {
            $accessStats = $this->getAccessStatistics();
            $cachedQueries = $this->getAllCachedQueries();
            
            // Sort by access count (ascending)
            uasort($accessStats, fn($a, $b) => $a['access_count'] <=> $b['access_count']);
            
            $removed = 0;
            $entriesToRemove = count($cachedQueries) - $maxEntries;
            
            foreach (array_slice($accessStats, 0, $entriesToRemove, true) as $key => $stats) {
                if ($this->clearCacheByPattern($key)) {
                    $removed++;
                }
            }
            
            return $removed;
        } catch (\Exception $e) {
            \Log::warning('Cache optimization failed: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Generate cache key from query and context
     */
    public function generateCacheKey(string $query, array $context = []): string
    {
        $contextString = serialize($context);
        return md5($query . $contextString);
    }

    /**
     * Calculate intelligent TTL based on query characteristics
     */
    private function calculateIntelligentTTL(string $query, array $results, array $context): int
    {
        $baseTTL = self::DEFAULT_TTL;
        
        // Popular queries get longer TTL
        if ($this->isPopularQuery($query)) {
            $baseTTL = self::POPULAR_TTL;
        }
        
        // Recent queries get shorter TTL
        if ($this->isRecentQuery($query)) {
            $baseTTL = self::RECENT_TTL;
        }
        
        // Adjust based on result count
        $resultCount = count($results);
        if ($resultCount === 0) {
            $baseTTL = $baseTTL / 2; // Shorter TTL for no results
        } elseif ($resultCount > 50) {
            $baseTTL = $baseTTL * 1.5; // Longer TTL for many results
        }
        
        // Adjust based on context
        if (isset($context['user_id'])) {
            $baseTTL = $baseTTL * 0.8; // Shorter TTL for personalized results
        }
        
        return (int) $baseTTL;
    }

    /**
     * Store data in Redis for advanced operations
     */
    private function storeInRedis(string $key, array $data, int $ttl): void
    {
        try {
            Redis::setex($key, $ttl, json_encode($data));
        } catch (\Exception $e) {
            \Log::warning('Redis storage failed: ' . $e->getMessage());
        }
    }

    /**
     * Update cache statistics
     */
    private function updateCacheStatistics(string $key, string $query, int $resultCount): void
    {
        try {
            $statsKey = self::CACHE_PREFIX . 'statistics';
            $stats = Cache::get($statsKey, [
                'total_queries' => 0,
                'total_results' => 0,
                'query_counts' => [],
            ]);
            
            $stats['total_queries']++;
            $stats['total_results'] += $resultCount;
            $stats['query_counts'][$query] = ($stats['query_counts'][$query] ?? 0) + 1;
            
            Cache::put($statsKey, $stats, self::ANALYTICS_TTL);
        } catch (\Exception $e) {
            \Log::warning('Cache statistics update failed: ' . $e->getMessage());
        }
    }

    /**
     * Update access statistics
     */
    private function updateAccessStatistics(string $key): void
    {
        try {
            $accessKey = self::CACHE_PREFIX . 'access:' . $key;
            $accessCount = Cache::get($accessKey, 0);
            Cache::put($accessKey, $accessCount + 1, self::ANALYTICS_TTL);
        } catch (\Exception $e) {
            \Log::warning('Access statistics update failed: ' . $e->getMessage());
        }
    }

    /**
     * Check if query is popular
     */
    private function isPopularQuery(string $query): bool
    {
        $stats = Cache::get(self::CACHE_PREFIX . 'statistics', []);
        $queryCount = $stats['query_counts'][$query] ?? 0;
        
        return $queryCount > 10; // Threshold for popular queries
    }

    /**
     * Check if query is recent
     */
    private function isRecentQuery(string $query): bool
    {
        // This would typically check against recent search history
        return false;
    }

    /**
     * Get total cached queries
     */
    private function getTotalCachedQueries(): int
    {
        try {
            $keys = Cache::getRedis()->keys(self::CACHE_PREFIX . '*');
            return count($keys);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get cache hit rate
     */
    private function getCacheHitRate(): float
    {
        try {
            $stats = Cache::get(self::CACHE_PREFIX . 'statistics', []);
            $totalQueries = $stats['total_queries'] ?? 0;
            $totalResults = $stats['total_results'] ?? 0;
            
            if ($totalQueries === 0) {
                return 0.0;
            }
            
            return round(($totalResults / $totalQueries) * 100, 2);
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * Get most popular queries
     */
    private function getMostPopularQueries(): array
    {
        try {
            $stats = Cache::get(self::CACHE_PREFIX . 'statistics', []);
            $queryCounts = $stats['query_counts'] ?? [];
            
            arsort($queryCounts);
            
            return array_slice($queryCounts, 0, 10, true);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get cache size
     */
    private function getCacheSize(): int
    {
        try {
            $keys = Cache::getRedis()->keys(self::CACHE_PREFIX . '*');
            $totalSize = 0;
            
            foreach ($keys as $key) {
                $size = Cache::getRedis()->strlen($key);
                $totalSize += $size;
            }
            
            return $totalSize;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get memory usage
     */
    private function getMemoryUsage(): array
    {
        try {
            $info = Cache::getRedis()->info('memory');
            
            return [
                'used_memory' => $info['used_memory'] ?? 0,
                'used_memory_human' => $info['used_memory_human'] ?? '0B',
                'used_memory_peak' => $info['used_memory_peak'] ?? 0,
                'used_memory_peak_human' => $info['used_memory_peak_human'] ?? '0B',
            ];
        } catch (\Exception $e) {
            return [
                'used_memory' => 0,
                'used_memory_human' => '0B',
                'used_memory_peak' => 0,
                'used_memory_peak_human' => '0B',
            ];
        }
    }

    /**
     * Get access statistics
     */
    private function getAccessStatistics(): array
    {
        try {
            $keys = Cache::getRedis()->keys(self::CACHE_PREFIX . 'access:*');
            $stats = [];
            
            foreach ($keys as $key) {
                $accessCount = Cache::get($key, 0);
                $queryKey = str_replace(self::CACHE_PREFIX . 'access:', '', $key);
                $stats[$queryKey] = ['access_count' => $accessCount];
            }
            
            return $stats;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get all cached queries
     */
    private function getAllCachedQueries(): array
    {
        try {
            $keys = Cache::getRedis()->keys(self::CACHE_PREFIX . '*');
            $queries = [];
            
            foreach ($keys as $key) {
                if (!str_contains($key, 'access:') && !str_contains($key, 'statistics')) {
                    $queries[] = $key;
                }
            }
            
            return $queries;
        } catch (\Exception $e) {
            return [];
        }
    }
}
