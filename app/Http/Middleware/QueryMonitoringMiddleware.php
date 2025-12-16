<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\QueryMonitoringService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to monitor database queries and detect N+1 patterns in development.
 */
final class QueryMonitoringMiddleware
{
    public function __construct(
        private readonly QueryMonitoringService $queryMonitor
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only monitor in development or when explicitly enabled
        if (! app()->environment('local', 'testing') && ! config('app.debug')) {
            return $next($request);
        }

        // Skip monitoring for certain routes to avoid noise
        if ($this->shouldSkipMonitoring($request)) {
            return $next($request);
        }

        // Start monitoring
        $this->queryMonitor->startMonitoring(config('database.query_threshold', 20));

        $response = $next($request);

        // Stop monitoring and analyze results
        $queryData = $this->queryMonitor->stopMonitoring();

        // Log results if there are potential issues
        if ($queryData['threshold_exceeded'] || ! empty($queryData['n1_patterns'])) {
            Log::channel('performance')->warning('Potential query performance issue detected', [
                'url'               => $request->fullUrl(),
                'method'            => $request->method(),
                'total_queries'     => $queryData['total_queries'],
                'total_time'        => round($queryData['total_time'], 2) . 'ms',
                'n1_patterns_count' => count($queryData['n1_patterns']),
                'n1_patterns'       => array_map(function ($pattern) {
                    return [
                        'sql'   => $pattern['sql'],
                        'count' => $pattern['count'],
                        'time'  => round($pattern['total_time'], 2) . 'ms',
                    ];
                }, $queryData['n1_patterns']),
            ]);
        }

        // Add debug headers in development
        if (app()->environment('local') && config('app.debug')) {
            $response->headers->set('X-Query-Count', (string) $queryData['total_queries']);
            $response->headers->set('X-Query-Time', round($queryData['total_time'], 2) . 'ms');

            if (! empty($queryData['n1_patterns'])) {
                $response->headers->set('X-N1-Patterns', (string) count($queryData['n1_patterns']));
            }
        }

        return $response;
    }

    /**
     * Determine if monitoring should be skipped for this request.
     */
    private function shouldSkipMonitoring(Request $request): bool
    {
        $skipPatterns = [
            '/telescope',
            '/horizon',
            '/_debugbar',
            '/livewire/message',
            '/favicon.ico',
            '/robots.txt',
        ];

        $path = $request->path();

        foreach ($skipPatterns as $pattern) {
            if (str_starts_with($path, ltrim($pattern, '/'))) {
                return true;
            }
        }

        return false;
    }
}
