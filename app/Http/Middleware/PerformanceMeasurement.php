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

        // Skip measurement in testing environment to avoid interference
        if (app()->environment('testing')) {
            return $next($request);
        }

        $startTime = microtime(true);
        $startMemory = memory_get_peak_usage(true);

        // Use lightweight query counting instead of full query logging
        $initialQueryCount = $this->getQueryCount();

        $response = $next($request);

        $endTime = microtime(true);
        $endMemory = memory_get_peak_usage(true);

        // Calculate metrics
        $ttfb = ($endTime - $startTime) * 1000; // Convert to milliseconds
        $queryCount = $this->getQueryCount() - $initialQueryCount;
        $peakMemoryMb = (int) round($endMemory / 1024 / 1024);

        // Store metrics asynchronously to avoid impacting response time
        $this->storeMetricsAsync($routeName, $ttfb, $queryCount, $peakMemoryMb);

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
     * Store performance metrics asynchronously.
     */
    private function storeMetricsAsync(string $routeName, float $ttfb, int $queryCount, int $peakMemoryMb): void
    {
        // Use dispatch_sync in testing, async in production
        $job = new \App\Jobs\StorePerformanceMetricsJob(
            $routeName,
            $ttfb,
            $queryCount,
            $peakMemoryMb,
            app()->environment()
        );

        if (app()->environment('testing')) {
            // Synchronous in tests for predictable behavior
            try {
                $job->handle();
            } catch (Throwable $e) {
                Log::warning('Failed to store performance metrics in test', [
                    'route' => $routeName,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            // Asynchronous in production to avoid blocking response
            dispatch($job)->onQueue('metrics');
        }
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
