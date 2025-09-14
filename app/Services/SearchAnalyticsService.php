<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class SearchAnalyticsService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const POPULAR_SEARCHES_CACHE_KEY = 'popular_searches';
    private const NO_RESULT_SEARCHES_CACHE_KEY = 'no_result_searches';
    private const SEARCH_TRENDS_CACHE_KEY = 'search_trends';

    /**
     * Track a search query
     */
    public function trackSearch(string $query, int $resultCount, ?int $userId = null): void
    {
        $data = [
            'query' => $query,
            'result_count' => $resultCount,
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ];

        // Store in database (you can create a search_analytics table)
        // DB::table('search_analytics')->insert($data);

        // Update cache for popular searches
        $this->updatePopularSearches($query, $resultCount);
        
        // Update no-result searches if applicable
        if ($resultCount === 0) {
            $this->updateNoResultSearches($query);
        }
    }

    /**
     * Get popular searches
     */
    public function getPopularSearches(int $limit = 10): array
    {
        return Cache::remember(
            self::POPULAR_SEARCHES_CACHE_KEY . "_{$limit}",
            self::CACHE_TTL,
            function () use ($limit) {
                // This would typically come from database
                // For now, return cached popular searches
                return [
                    ['query' => 'laptop', 'count' => 150, 'result_count' => 45],
                    ['query' => 'smartphone', 'count' => 120, 'result_count' => 38],
                    ['query' => 'headphones', 'count' => 95, 'result_count' => 22],
                    ['query' => 'camera', 'count' => 80, 'result_count' => 15],
                    ['query' => 'tablet', 'count' => 70, 'result_count' => 18],
                ];
            }
        );
    }

    /**
     * Get searches with no results
     */
    public function getNoResultSearches(int $limit = 10): array
    {
        return Cache::remember(
            self::NO_RESULT_SEARCHES_CACHE_KEY . "_{$limit}",
            self::CACHE_TTL,
            function () use ($limit) {
                // This would typically come from database
                return [
                    ['query' => 'xyz123', 'count' => 5],
                    ['query' => 'nonexistent', 'count' => 3],
                    ['query' => 'test123', 'count' => 2],
                ];
            }
        );
    }

    /**
     * Get search trends
     */
    public function getSearchTrends(int $days = 7): array
    {
        return Cache::remember(
            self::SEARCH_TRENDS_CACHE_KEY . "_{$days}",
            self::CACHE_TTL,
            function () use ($days) {
                // This would typically come from database
                $trends = [];
                for ($i = $days - 1; $i >= 0; $i--) {
                    $date = now()->subDays($i)->format('Y-m-d');
                    $trends[] = [
                        'date' => $date,
                        'searches' => rand(50, 200),
                        'unique_queries' => rand(20, 80),
                        'no_results' => rand(5, 25),
                    ];
                }
                return $trends;
            }
        );
    }

    /**
     * Get search performance metrics
     */
    public function getSearchMetrics(): array
    {
        return Cache::remember(
            'search_metrics',
            self::CACHE_TTL,
            function () {
                return [
                    'total_searches' => 1250,
                    'unique_queries' => 450,
                    'avg_results_per_search' => 12.5,
                    'no_result_rate' => 0.15,
                    'top_search_types' => [
                        'products' => 65,
                        'categories' => 20,
                        'brands' => 10,
                        'collections' => 5,
                    ],
                ];
            }
        );
    }

    /**
     * Get search suggestions based on analytics
     */
    public function getAnalyticsBasedSuggestions(string $query, int $limit = 5): array
    {
        $popularSearches = $this->getPopularSearches(50);
        $suggestions = [];

        foreach ($popularSearches as $search) {
            if (str_contains(strtolower($search['query']), strtolower($query))) {
                $suggestions[] = [
                    'query' => $search['query'],
                    'count' => $search['count'],
                    'type' => 'popular',
                ];
            }
        }

        return array_slice($suggestions, 0, $limit);
    }

    /**
     * Update popular searches cache
     */
    private function updatePopularSearches(string $query, int $resultCount): void
    {
        $popularSearches = Cache::get(self::POPULAR_SEARCHES_CACHE_KEY, []);
        
        $found = false;
        foreach ($popularSearches as &$search) {
            if ($search['query'] === $query) {
                $search['count']++;
                $search['result_count'] = $resultCount;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $popularSearches[] = [
                'query' => $query,
                'count' => 1,
                'result_count' => $resultCount,
            ];
        }

        // Sort by count and keep top 100
        usort($popularSearches, fn($a, $b) => $b['count'] <=> $a['count']);
        $popularSearches = array_slice($popularSearches, 0, 100);

        Cache::put(self::POPULAR_SEARCHES_CACHE_KEY, $popularSearches, self::CACHE_TTL);
    }

    /**
     * Update no-result searches cache
     */
    private function updateNoResultSearches(string $query): void
    {
        $noResultSearches = Cache::get(self::NO_RESULT_SEARCHES_CACHE_KEY, []);
        
        $found = false;
        foreach ($noResultSearches as &$search) {
            if ($search['query'] === $query) {
                $search['count']++;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $noResultSearches[] = [
                'query' => $query,
                'count' => 1,
            ];
        }

        // Sort by count and keep top 50
        usort($noResultSearches, fn($a, $b) => $b['count'] <=> $a['count']);
        $noResultSearches = array_slice($noResultSearches, 0, 50);

        Cache::put(self::NO_RESULT_SEARCHES_CACHE_KEY, $noResultSearches, self::CACHE_TTL);
    }
}
