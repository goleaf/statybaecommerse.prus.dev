<?php

declare(strict_types=1);

namespace App\Services\VersionCompatibility;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Log;

/**
 * Performance monitoring for version compatibility transformations
 */
final class PerformanceMonitor
{
    private array $metrics = [];

    public function __construct(
        private readonly CacheRepository $cache
    ) {}

    /**
     * Start timing a transformation operation
     */
    public function startTiming(string $operation): string
    {
        $id = uniqid($operation . '_', true);
        $this->metrics[$id] = [
            'operation'    => $operation,
            'start_time'   => microtime(true),
            'memory_start' => memory_get_usage(true),
        ];

        return $id;
    }

    /**
     * End timing and record metrics
     */
    public function endTiming(string $id, array $additionalData = []): array
    {
        if (! isset($this->metrics[$id])) {
            return [];
        }

        $metric = $this->metrics[$id];
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);

        $result = [
            'operation'      => $metric['operation'],
            'duration_ms'    => round(($endTime - $metric['start_time']) * 1000, 2),
            'memory_used_mb' => round(($endMemory - $metric['memory_start']) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            ...$additionalData,
        ];

        // Log slow operations
        if ($result['duration_ms'] > 100) {
            Log::warning('Slow transformation detected', $result);
        }

        // Cache performance stats for analysis
        $this->cachePerformanceStats($result);

        unset($this->metrics[$id]);

        return $result;
    }

    /**
     * Get aggregated performance statistics
     */
    public function getAggregatedStats(): array
    {
        $stats = $this->cache->get('version_compat_perf_stats', []);

        if (empty($stats)) {
            return [
                'total_operations' => 0,
                'avg_duration_ms'  => 0,
                'avg_memory_mb'    => 0,
                'slow_operations'  => 0,
            ];
        }

        return [
            'total_operations'   => count($stats),
            'avg_duration_ms'    => round(array_sum(array_column($stats, 'duration_ms')) / count($stats), 2),
            'avg_memory_mb'      => round(array_sum(array_column($stats, 'memory_used_mb')) / count($stats), 2),
            'slow_operations'    => count(array_filter($stats, fn ($s) => $s['duration_ms'] > 100)),
            'operations_by_type' => array_count_values(array_column($stats, 'operation')),
        ];
    }

    /**
     * Clear performance statistics
     */
    public function clearStats(): void
    {
        $this->cache->forget('version_compat_perf_stats');
        $this->metrics = [];
    }

    /**
     * Cache performance statistics for analysis
     */
    private function cachePerformanceStats(array $result): void
    {
        $stats = $this->cache->get('version_compat_perf_stats', []);
        $stats[] = $result;

        // Keep only last 1000 operations
        if (count($stats) > 1000) {
            $stats = array_slice($stats, -1000);
        }

        $this->cache->put('version_compat_perf_stats', $stats, 3600);
    }
}
