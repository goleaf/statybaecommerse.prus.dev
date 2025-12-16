<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\PerformanceMetrics;
use Exception;
use Illuminate\Support\Facades\DB;

class PerformanceHelper
{
    /**
     * Query budgets for key storefront pages.
     */
    public const QUERY_BUDGETS = [
        'localized.home'             => 5,
        'localized.categories.index' => 8,
        'localized.categories.show'  => 12,
        'localized.products.index'   => 10,
        'localized.products.show'    => 6,
        'localized.search'           => 10,
    ];

    /**
     * TTFB budgets in milliseconds for key storefront pages.
     */
    public const TTFB_BUDGETS = [
        'localized.home'             => 200,
        'localized.categories.index' => 300,
        'localized.categories.show'  => 400,
        'localized.products.index'   => 350,
        'localized.products.show'    => 250,
        'localized.search'           => 500,
    ];

    /**
     * Get baseline performance metrics for all measured routes.
     */
    public static function getBaselineMetrics(int $days = 7): array
    {
        $routes = array_keys(self::QUERY_BUDGETS);
        $baseline = [];

        foreach ($routes as $route) {
            $baseline[$route] = PerformanceMetrics::getAverageMetrics($route, $days);
            $baseline[$route]['query_budget'] = self::QUERY_BUDGETS[$route];
            $baseline[$route]['ttfb_budget'] = self::TTFB_BUDGETS[$route];
            $baseline[$route]['within_query_budget'] =
                $baseline[$route]['avg_query_count'] !== null &&
                $baseline[$route]['avg_query_count'] <= self::QUERY_BUDGETS[$route];
            $baseline[$route]['within_ttfb_budget'] =
                $baseline[$route]['avg_ttfb_p95'] !== null &&
                $baseline[$route]['avg_ttfb_p95'] <= self::TTFB_BUDGETS[$route];
        }

        return $baseline;
    }

    /**
     * Check if a route is within performance budgets.
     */
    public static function isWithinBudgets(string $route, int $queryCount, float $ttfb): array
    {
        $queryBudget = self::QUERY_BUDGETS[$route] ?? null;
        $ttfbBudget = self::TTFB_BUDGETS[$route] ?? null;

        return [
            'route'               => $route,
            'query_count'         => $queryCount,
            'query_budget'        => $queryBudget,
            'within_query_budget' => $queryBudget ? $queryCount <= $queryBudget : null,
            'ttfb'                => $ttfb,
            'ttfb_budget'         => $ttfbBudget,
            'within_ttfb_budget'  => $ttfbBudget ? $ttfb <= $ttfbBudget : null,
        ];
    }

    /**
     * Get performance summary for all routes.
     */
    public static function getPerformanceSummary(int $days = 7): array
    {
        $baseline = self::getBaselineMetrics($days);

        $summary = [
            'total_routes'               => count($baseline),
            'routes_within_query_budget' => 0,
            'routes_within_ttfb_budget'  => 0,
            'avg_query_count'            => 0,
            'avg_ttfb_p95'               => 0,
            'routes'                     => $baseline,
        ];

        $totalQueryCount = 0;
        $totalTtfb = 0;
        $routeCount = 0;

        foreach ($baseline as $route => $metrics) {
            if ($metrics['within_query_budget']) {
                $summary['routes_within_query_budget']++;
            }

            if ($metrics['within_ttfb_budget']) {
                $summary['routes_within_ttfb_budget']++;
            }

            if ($metrics['avg_query_count'] !== null) {
                $totalQueryCount += $metrics['avg_query_count'];
                $routeCount++;
            }

            if ($metrics['avg_ttfb_p95'] !== null) {
                $totalTtfb += $metrics['avg_ttfb_p95'];
            }
        }

        if ($routeCount > 0) {
            $summary['avg_query_count'] = round($totalQueryCount / $routeCount, 1);
            $summary['avg_ttfb_p95'] = round($totalTtfb / $routeCount, 1);
        }

        return $summary;
    }

    /**
     * Start measuring performance for the current request.
     */
    public static function startMeasurement(): array
    {
        return [
            'start_time'          => microtime(true),
            'start_memory'        => memory_get_peak_usage(true),
            'initial_query_count' => count(DB::getQueryLog()),
        ];
    }

    /**
     * End performance measurement and return metrics.
     */
    public static function endMeasurement(array $startMetrics): array
    {
        $endTime = microtime(true);
        $endMemory = memory_get_peak_usage(true);

        return [
            'ttfb_ms'            => ($endTime - $startMetrics['start_time']) * 1000,
            'query_count'        => count(DB::getQueryLog()) - $startMetrics['initial_query_count'],
            'peak_memory_mb'     => round($endMemory / 1024 / 1024),
            'memory_increase_mb' => round(($endMemory - $startMetrics['start_memory']) / 1024 / 1024),
        ];
    }

    /**
     * Assert that performance is within budgets (for testing).
     */
    public static function assertWithinBudgets(string $route, int $queryCount, float $ttfb): void
    {
        $budgets = self::isWithinBudgets($route, $queryCount, $ttfb);

        if ($budgets['query_budget'] && ! $budgets['within_query_budget']) {
            throw new Exception(
                "Query budget exceeded for {$route}: {$queryCount} queries (budget: {$budgets['query_budget']})"
            );
        }

        if ($budgets['ttfb_budget'] && ! $budgets['within_ttfb_budget']) {
            throw new Exception(
                "TTFB budget exceeded for {$route}: {$ttfb}ms (budget: {$budgets['ttfb_budget']}ms)"
            );
        }
    }
}
