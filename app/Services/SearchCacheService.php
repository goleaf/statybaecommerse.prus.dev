<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\Cache\CacheTags;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Log;
use Throwable;

final class SearchCacheService
{
    private const CACHE_PREFIX = 'search_cache:';

    private function getDefaultTTL(): int
    {
        return (int) config('search.cache.default_ttl', 3600);
    }

    private function getPopularTTL(): int
    {
        return (int) config('search.cache.popular_ttl', 7200);
    }

    private function getRecentTTL(): int
    {
        return (int) config('search.cache.recent_ttl', 1800);
    }

    private function isCacheEnabled(): bool
    {
        return (bool) config('search.cache.enabled', true);
    }

    private function areTagsEnabled(): bool
    {
        return (bool) config('search.cache.tags.enabled', true);
    }

    /**
     * Cache search results with intelligent TTL and proper tagging
     */
    public function cacheSearchResults(string $key, array $results, string $query, array $context = []): void
    {
        // Skip caching if disabled
        if (! $this->isCacheEnabled()) {
            return;
        }

        try {
            $ttl = $this->calculateIntelligentTTL($query, $results, $context);
            $cacheKey = self::CACHE_PREFIX . $key;
            $tags = $this->generateCacheTags($context, $results);

            $cacheData = [
                'results'      => $results,
                'query'        => $query,
                'context'      => $context,
                'cached_at'    => now()->toISOString(),
                'ttl'          => $ttl,
                'result_count' => count($results['data'] ?? $results),
                'tags'         => $tags,
            ];

            // Use tag-aware caching when supported by the cache driver and enabled
            if ($this->supportsCacheTags() && $this->areTagsEnabled()) {
                Cache::tags($tags)->put($cacheKey, $cacheData, $ttl);
            } else {
                Cache::put($cacheKey, $cacheData, $ttl);
            }

            // Store in Redis for advanced operations
            $this->storeInRedis($cacheKey, $cacheData, $ttl);

        } catch (Throwable $e) {
            // We intentionally swallow any cache backend issues so the search response
            // still completes even when Redis or other stores are unavailable.
            Log::warning('Search cache storage failed: ' . $e->getMessage());
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
                return $cachedData['results'] ?? null;
            }

            return null;
        } catch (Throwable $e) {
            Log::warning('Search cache retrieval failed: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Clear cache by tags
     */
    public function clearCacheByTags(array $tags): void
    {
        try {
            if ($this->supportsCacheTags()) {
                Cache::tags($tags)->flush();
                Log::info('Cache cleared by tags', ['tags' => $tags]);
            } else {
                // Fallback: clear all search cache when tags not supported
                $this->clearCacheByPattern('*');
                Log::info('Cache cleared by pattern (tags not supported)', ['tags' => $tags]);
            }
        } catch (Throwable $e) {
            Log::warning('Cache tag clearing failed: ' . $e->getMessage(), ['tags' => $tags]);
        }
    }

    /**
     * Warm up cache with popular searches
     */
    public function warmUpCache(array $popularQueries): void
    {
        foreach ($popularQueries as $query) {
            $key = $this->generateCacheKey($query, []);

            // Check if already cached
            if (! $this->getCachedResults($key)) {
                // This would typically trigger a search to populate cache
                Log::info("Warming up cache for query: {$query}");
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
        } catch (Throwable $e) {
            Log::warning('Cache pattern clearing failed: ' . $e->getMessage());

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
     * Generate cache tags for search results based on context and results
     */
    private function generateCacheTags(array $context, array $results): array
    {
        $tags = [];

        // Include locale tag if enabled in config
        if (config('search.cache.tags.include_locale', true)) {
            $locale = $context['locale'] ?? app()->getLocale();
            $tags[] = CacheTags::locale($locale);
        }

        // Include catalog tags if enabled in config
        if (config('search.cache.tags.include_catalog', true)) {
            $resultData = $results['data'] ?? $results;

            if (is_array($resultData)) {
                $hasProducts = false;
                $hasBrands = false;
                $hasCategories = false;
                $hasCollections = false;

                // Check for different result types in flat results
                foreach ($resultData as $result) {
                    if (! is_array($result)) {
                        continue;
                    }

                    $type = $result['type'] ?? null;
                    if ($type === 'product') {
                        $hasProducts = true;
                    } elseif ($type === 'brand') {
                        $hasBrands = true;
                    } elseif ($type === 'category') {
                        $hasCategories = true;
                    } elseif ($type === 'collection') {
                        $hasCollections = true;
                    }
                }

                // Check for aggregated results structure
                if (isset($resultData['products']) || isset($resultData['categories']) || isset($resultData['brands'])) {
                    if (isset($resultData['products'])) {
                        $hasProducts = true;
                    }
                    if (isset($resultData['categories'])) {
                        $hasCategories = true;
                    }
                    if (isset($resultData['brands'])) {
                        $hasBrands = true;
                    }
                }

                // Add appropriate catalog tags
                if ($hasProducts) {
                    $tags[] = CacheTags::products();
                }
                if ($hasBrands) {
                    $tags[] = CacheTags::brands();
                }
                if ($hasCategories) {
                    $tags[] = CacheTags::categories();
                }
                if ($hasCollections) {
                    $tags[] = CacheTags::collections();
                }
            }
        }

        // Add search-specific tag for bulk invalidation
        $tags[] = 'search';

        return array_unique($tags);
    }

    /**
     * Check if the current cache driver supports tags
     */
    private function supportsCacheTags(): bool
    {
        $driver = config('cache.default');

        // Redis and Memcached support tags, database and file do not
        return in_array($driver, ['redis', 'memcached'], true);
    }

    /**
     * Calculate intelligent TTL based on query characteristics
     */
    private function calculateIntelligentTTL(string $query, array $results, array $context): int
    {
        $baseTTL = $this->getDefaultTTL();

        // Recent queries get shorter TTL
        if ($this->isRecentQuery($query)) {
            $baseTTL = $this->getRecentTTL();
        }

        // Adjust based on result count
        $resultCount = count($results['data'] ?? $results);
        if ($resultCount === 0) {
            $baseTTL = (int) ($baseTTL / 2); // Shorter TTL for no results
        } elseif ($resultCount > 50) {
            $baseTTL = (int) ($baseTTL * 1.5); // Longer TTL for many results
        }

        // Adjust based on context
        if (isset($context['user_id'])) {
            $baseTTL = (int) ($baseTTL * 0.8); // Shorter TTL for personalized results
        }

        return $baseTTL;
    }

    /**
     * Store data in Redis for advanced operations
     */
    private function storeInRedis(string $key, array $data, int $ttl): void
    {
        try {
            Redis::setex($key, $ttl, json_encode($data));
        } catch (Throwable $e) {
            Log::warning('Redis storage failed: ' . $e->getMessage());
        }
    }

    /**
     * Check if query is recent
     */
    private function isRecentQuery(string $query): bool
    {
        // This would typically check against recent search history
        return false;
    }
}
