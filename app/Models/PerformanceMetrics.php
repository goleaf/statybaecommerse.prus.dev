<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceMetrics extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_route',
        'ttfb_p50',
        'ttfb_p95',
        'query_count',
        'peak_memory_mb',
        'environment',
        'additional_metrics',
    ];

    protected $casts = [
        'ttfb_p50'           => 'decimal:3',
        'ttfb_p95'           => 'decimal:3',
        'query_count'        => 'integer',
        'peak_memory_mb'     => 'integer',
        'additional_metrics' => 'array',
    ];

    /**
     * Boot the model and register event listeners.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Clear cache when new metrics are added
        static::created(function (PerformanceMetrics $metrics) {
            $metrics->clearRelatedCache();
        });
    }

    /**
     * Clear related cache entries when metrics change.
     */
    public function clearRelatedCache(): void
    {
        $patterns = [
            "performance_metrics:{$this->page_route}:*",
            "performance_trends:{$this->page_route}:*",
        ];

        foreach ($patterns as $pattern) {
            // Clear cache entries matching the pattern
            cache()->forget($pattern);
        }
    }

    /**
     * Get metrics for a specific page route.
     */
    public function scopeForRoute($query, string $route)
    {
        return $query->where('page_route', $route);
    }

    /**
     * Get metrics for a specific environment.
     */
    public function scopeForEnvironment($query, string $environment)
    {
        return $query->where('environment', $environment);
    }

    /**
     * Get recent metrics within the specified days.
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get average metrics for a route with caching.
     */
    public static function getAverageMetrics(string $route, int $days = 7): array
    {
        // Cache key for performance metrics
        $cacheKey = "performance_metrics:{$route}:{$days}";

        return cache()->remember($cacheKey, now()->addMinutes(5), function () use ($route, $days) {
            $metrics = static::forRoute($route)
                ->recent($days)
                ->selectRaw('
                    AVG(ttfb_p50) as avg_ttfb_p50,
                    AVG(ttfb_p95) as avg_ttfb_p95,
                    AVG(query_count) as avg_query_count,
                    AVG(peak_memory_mb) as avg_peak_memory_mb,
                    COUNT(*) as sample_count
                ')
                ->first();

            return [
                'avg_ttfb_p50'       => $metrics->avg_ttfb_p50 ? round($metrics->avg_ttfb_p50, 3) : null,
                'avg_ttfb_p95'       => $metrics->avg_ttfb_p95 ? round($metrics->avg_ttfb_p95, 3) : null,
                'avg_query_count'    => $metrics->avg_query_count ? round($metrics->avg_query_count) : null,
                'avg_peak_memory_mb' => $metrics->avg_peak_memory_mb ? round($metrics->avg_peak_memory_mb) : null,
                'sample_count'       => $metrics->sample_count ?? 0,
            ];
        });
    }

    /**
     * Get performance trends for a route.
     */
    public static function getPerformanceTrends(string $route, int $days = 30): array
    {
        $cacheKey = "performance_trends:{$route}:{$days}";

        return cache()->remember($cacheKey, now()->addMinutes(15), function () use ($route, $days) {
            return static::forRoute($route)
                ->recent($days)
                ->selectRaw('
                    DATE(created_at) as date,
                    AVG(ttfb_p50) as avg_ttfb,
                    AVG(query_count) as avg_queries,
                    COUNT(*) as sample_count
                ')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(function ($item) {
                    return [
                        'date'         => $item->date,
                        'avg_ttfb'     => round($item->avg_ttfb, 2),
                        'avg_queries'  => round($item->avg_queries, 1),
                        'sample_count' => $item->sample_count,
                    ];
                })
                ->toArray();
        });
    }
}
