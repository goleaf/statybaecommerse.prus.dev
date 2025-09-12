<?php

declare(strict_types=1);

namespace App\Services\Shared;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class ComponentPerformanceService
{
    private const METRICS_CACHE_KEY = 'component_performance_metrics';

    private const METRICS_TTL = 3600; // 1 hour

    public function trackComponentRender(string $component, float $renderTime): void
    {
        $metrics = $this->getMetrics();

        if (! isset($metrics[$component])) {
            $metrics[$component] = [
                'total_renders' => 0,
                'total_time' => 0,
                'avg_time' => 0,
                'min_time' => PHP_FLOAT_MAX,
                'max_time' => 0,
                'last_render' => null,
            ];
        }

        $metrics[$component]['total_renders']++;
        $metrics[$component]['total_time'] += $renderTime;
        $metrics[$component]['avg_time'] = $metrics[$component]['total_time'] / $metrics[$component]['total_renders'];
        $metrics[$component]['min_time'] = min($metrics[$component]['min_time'], $renderTime);
        $metrics[$component]['max_time'] = max($metrics[$component]['max_time'], $renderTime);
        $metrics[$component]['last_render'] = now()->toISOString();

        $this->saveMetrics($metrics);

        // Log slow components
        if ($renderTime > 100) { // 100ms threshold
            Log::warning('Slow component render detected', [
                'component' => $component,
                'render_time' => $renderTime,
                'avg_time' => $metrics[$component]['avg_time'],
            ]);
        }
    }

    public function getComponentMetrics(string $component): ?array
    {
        $metrics = $this->getMetrics();

        return $metrics[$component] ?? null;
    }

    public function getAllMetrics(): array
    {
        return $this->getMetrics();
    }

    public function getSlowestComponents(int $limit = 10): array
    {
        $metrics = $this->getMetrics();

        uasort($metrics, fn ($a, $b) => $b['avg_time'] <=> $a['avg_time']);

        return array_slice($metrics, 0, $limit, true);
    }

    public function getMostUsedComponents(int $limit = 10): array
    {
        $metrics = $this->getMetrics();

        uasort($metrics, fn ($a, $b) => $b['total_renders'] <=> $a['total_renders']);

        return array_slice($metrics, 0, $limit, true);
    }

    public function getPerformanceReport(): array
    {
        $metrics = $this->getMetrics();

        if (empty($metrics)) {
            return [
                'total_components' => 0,
                'total_renders' => 0,
                'avg_render_time' => 0,
                'slowest_component' => null,
                'most_used_component' => null,
            ];
        }

        $totalRenders = array_sum(array_column($metrics, 'total_renders'));
        $totalTime = array_sum(array_column($metrics, 'total_time'));
        $avgRenderTime = $totalRenders > 0 ? $totalTime / $totalRenders : 0;

        $slowest = $this->getSlowestComponents(1);
        $mostUsed = $this->getMostUsedComponents(1);

        return [
            'total_components' => count($metrics),
            'total_renders' => $totalRenders,
            'avg_render_time' => round($avgRenderTime, 2),
            'slowest_component' => ! empty($slowest) ? array_key_first($slowest) : null,
            'slowest_time' => ! empty($slowest) ? round(array_values($slowest)[0]['avg_time'], 2) : 0,
            'most_used_component' => ! empty($mostUsed) ? array_key_first($mostUsed) : null,
            'most_used_count' => ! empty($mostUsed) ? array_values($mostUsed)[0]['total_renders'] : 0,
            'performance_score' => $this->calculatePerformanceScore($metrics),
        ];
    }

    public function resetMetrics(): void
    {
        Cache::forget(self::METRICS_CACHE_KEY);
    }

    public function exportMetrics(): string
    {
        $metrics = $this->getMetrics();
        $report = $this->getPerformanceReport();

        $export = [
            'generated_at' => now()->toISOString(),
            'summary' => $report,
            'detailed_metrics' => $metrics,
        ];

        return json_encode($export, JSON_PRETTY_PRINT);
    }

    public function optimizeSlowComponents(): array
    {
        $slowComponents = $this->getSlowestComponents(5);
        $recommendations = [];

        foreach ($slowComponents as $component => $metrics) {
            $avgTime = $metrics['avg_time'];

            if ($avgTime > 200) {
                $recommendations[$component] = [
                    'severity' => 'high',
                    'avg_time' => $avgTime,
                    'recommendations' => [
                        'Consider caching component output',
                        'Optimize database queries',
                        'Reduce component complexity',
                        'Use lazy loading for heavy content',
                    ],
                ];
            } elseif ($avgTime > 100) {
                $recommendations[$component] = [
                    'severity' => 'medium',
                    'avg_time' => $avgTime,
                    'recommendations' => [
                        'Review component logic',
                        'Consider prop optimization',
                        'Check for unnecessary re-renders',
                    ],
                ];
            } elseif ($avgTime > 50) {
                $recommendations[$component] = [
                    'severity' => 'low',
                    'avg_time' => $avgTime,
                    'recommendations' => [
                        'Monitor for performance degradation',
                        'Consider minor optimizations',
                    ],
                ];
            }
        }

        return $recommendations;
    }

    private function getMetrics(): array
    {
        return Cache::get(self::METRICS_CACHE_KEY, []);
    }

    private function saveMetrics(array $metrics): void
    {
        Cache::put(self::METRICS_CACHE_KEY, $metrics, self::METRICS_TTL);
    }

    private function calculatePerformanceScore(array $metrics): int
    {
        if (empty($metrics)) {
            return 100;
        }

        $totalComponents = count($metrics);
        $slowComponents = 0;
        $totalAvgTime = 0;

        foreach ($metrics as $metric) {
            $avgTime = $metric['avg_time'];
            $totalAvgTime += $avgTime;

            if ($avgTime > 100) {
                $slowComponents++;
            }
        }

        $avgRenderTime = $totalAvgTime / $totalComponents;
        $slowComponentRatio = $slowComponents / $totalComponents;

        // Score calculation (0-100)
        $timeScore = max(0, 100 - ($avgRenderTime / 2)); // Penalty for slow average time
        $ratioScore = max(0, 100 - ($slowComponentRatio * 100)); // Penalty for slow components

        return (int) round(($timeScore + $ratioScore) / 2);
    }
}
