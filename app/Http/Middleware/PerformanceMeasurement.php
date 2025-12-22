<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\PerformanceMetrics;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PerformanceMeasurement
{
    /**
     * Key storefront pages to measure performance for.
     */
    private const MEASURED_ROUTES = [
        'localized.home',
        'localized.categories.index',
        'localized.categories.show',
        'localized.products.index',
        'localized.products.show',
        'localized.search',
    ];

    /**
     * Cached route lookup for performance.
     */
    private static ?array $measuredRoutesLookup = null;

    /**
     * Handle an incoming request.
     *
     * @param Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Initialize route lookup cache once
        if (self::$measuredRoutesLookup === null) {
            self::$measuredRoutesLookup = array_flip(self::MEASURED_ROUTES);
        }

        // Only measure performance for key storefront pages - use array lookup for O(1) performance
        $routeName = $request->route()?->getName();
        if (! $routeName || ! isset(self::$measuredRoutesLookup[$routeName])) {
            return $next($request);
        }

        $startTime = microtime(true);
        $startMemory = memory_get_peak_usage(true);

        // Use lightweight query counting instead of full query logging
        $initialQueryCount = $this->getQueryCount();

        $response = $next($request);

        $endTime = microtime(true);
        $endMemory = memory_get_peak_usage(true);

        // Calculate metrics with optimized precision
        $ttfb = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds with 2 decimal precision
        $queryCount = $this->getQueryCount() - $initialQueryCount;
        $peakMemoryMb = round($endMemory / 1024 / 1024, 2); // More precise memory calculation

        // Store metrics based on environment
        if (app()->environment('testing')) {
            // Synchronous storage in tests for predictable behavior
            $this->storeMetricsSync($routeName, $ttfb, $queryCount, $peakMemoryMb);
        } else {
            // Asynchronous storage in production
            $this->storeMetricsAsync($routeName, $ttfb, $queryCount, $peakMemoryMb);
        }

        return $response;
    }

    /**
     * Get current query count without enabling full query logging.
     */
    private function getQueryCount(): int
    {
        // Use connection query count if available, fallback to query log
        $connection = DB::connection();

        if (method_exists($connection, 'getQueryCount')) {
            return $connection->getQueryCount();
        }

        // Fallback to query log count (less efficient but compatible)
        return count(DB::getQueryLog());
    }

    /**
     * Store performance metrics synchronously (for testing).
     */
    private function storeMetricsSync(string $routeName, float $ttfb, int $queryCount, float $peakMemoryMb): void
    {
        try {
            // Use raw DB insert for better performance in tests
            DB::table('performance_metrics')->insert([
                'page_route'         => $routeName,
                'ttfb_p50'           => $ttfb,
                'ttfb_p95'           => $ttfb,
                'query_count'        => $queryCount,
                'peak_memory_mb'     => $peakMemoryMb,
                'environment'        => app()->environment(),
                'additional_metrics' => json_encode([
                    'timestamp'  => now()->toISOString(),
                    'user_agent' => request()->userAgent(),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::warning('Failed to store performance metrics synchronously', [
                'route' => $routeName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Store performance metrics asynchronously.
     */
    private function storeMetricsAsync(string $routeName, float $ttfb, int $queryCount, float $peakMemoryMb): void
    {
        // Asynchronous storage in production to avoid blocking response
        $job = new \App\Jobs\StorePerformanceMetricsJob(
            $routeName,
            $ttfb,
            $queryCount,
            $peakMemoryMb,
            app()->environment()
        );

        dispatch($job)->onQueue('metrics');
    }

    /**
     * Store performance metrics synchronously (fallback).
     */
    private function storeMetrics(string $routeName, float $ttfb, int $queryCount, int $peakMemoryMb): void
    {
        try {
            PerformanceMetrics::create([
                'page_route'         => $routeName,
                'ttfb_p50'           => $ttfb, // Single measurement, will be aggregated later
                'ttfb_p95'           => $ttfb, // Single measurement, will be aggregated later
                'query_count'        => $queryCount,
                'peak_memory_mb'     => $peakMemoryMb,
                'environment'        => app()->environment(),
                'additional_metrics' => [
                    'timestamp'  => now()->toISOString(),
                    'user_agent' => request()->userAgent(),
                ],
            ]);
        } catch (Throwable $e) {
            // Log the error but don't fail the request
            Log::warning('Failed to store performance metrics', [
                'route' => $routeName,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
