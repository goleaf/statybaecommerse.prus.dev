<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

use Throwable;

/**
 * Performance profiler for boot error handling system.
 */
final class BootErrorProfiler
{
    private const PROFILING_KEY_PREFIX = 'boot_error_profiling_';

    private const PROFILING_TTL_MINUTES = 30;

    private static array $timings = [];

    private static array $memoryUsage = [];

    private static array $callCounts = [];

    public static function startTiming(string $operation): void
    {
        if (! config('exception-handling.performance.enable_profiling', false)) {
            return;
        }

        self::$timings[$operation] = microtime(true);
    }

    public static function endTiming(string $operation): float
    {
        if (! config('exception-handling.performance.enable_profiling', false)) {
            return 0.0;
        }

        if (! isset(self::$timings[$operation])) {
            return 0.0;
        }

        $duration = microtime(true) - self::$timings[$operation];
        unset(self::$timings[$operation]);

        self::recordTiming($operation, $duration);

        return $duration;
    }

    public static function recordMemoryUsage(string $operation): void
    {
        if (! config('exception-handling.performance.enable_profiling', false)) {
            return;
        }

        $memoryUsage = memory_get_usage(true);
        self::$memoryUsage[$operation] = $memoryUsage;

        self::recordMemory($operation, $memoryUsage);
    }

    public static function incrementCallCount(string $operation): void
    {
        if (! config('exception-handling.performance.enable_profiling', false)) {
            return;
        }

        self::$callCounts[$operation] = (self::$callCounts[$operation] ?? 0) + 1;

        self::recordCallCount($operation, self::$callCounts[$operation]);
    }

    public static function getProfilingData(): array
    {
        if (! config('exception-handling.performance.enable_profiling', false)) {
            return [];
        }

        $currentHour = date('Y-m-d-H');

        return [
            'timings' => cache()->get(self::PROFILING_KEY_PREFIX . 'timings_' . $currentHour, []),
            'memory_usage' => cache()->get(self::PROFILING_KEY_PREFIX . 'memory_' . $currentHour, []),
            'call_counts' => cache()->get(self::PROFILING_KEY_PREFIX . 'calls_' . $currentHour, []),
            'timestamp' => now()->toISOString(),
        ];
    }

    public static function detectPerformanceRegression(): array
    {
        if (! config('exception-handling.performance.enable_profiling', false)) {
            return [];
        }

        $data = self::getProfilingData();
        $regressions = [];

        // Check timing regressions
        foreach ($data['timings'] as $operation => $timings) {
            $averageTime = array_sum($timings) / count($timings);
            $budget = config("exception-handling.budgets.{$operation}_max_ms", 5);

            if ($averageTime * 1000 > $budget) {
                $regressions[] = [
                    'type' => 'timing',
                    'operation' => $operation,
                    'average_ms' => round($averageTime * 1000, 2),
                    'budget_ms' => $budget,
                    'severity' => $averageTime * 1000 > $budget * 2 ? 'high' : 'medium',
                ];
            }
        }

        // Check memory usage regressions
        foreach ($data['memory_usage'] as $operation => $memoryUsages) {
            $averageMemory = array_sum($memoryUsages) / count($memoryUsages);
            $budgetMB = config("exception-handling.budgets.{$operation}_max_memory_mb", 1);

            if ($averageMemory / 1024 / 1024 > $budgetMB) {
                $regressions[] = [
                    'type' => 'memory',
                    'operation' => $operation,
                    'average_mb' => round($averageMemory / 1024 / 1024, 2),
                    'budget_mb' => $budgetMB,
                    'severity' => $averageMemory / 1024 / 1024 > $budgetMB * 2 ? 'high' : 'medium',
                ];
            }
        }

        return $regressions;
    }

    private static function recordTiming(string $operation, float $duration): void
    {
        try {
            $key = self::PROFILING_KEY_PREFIX . 'timings_' . date('Y-m-d-H');
            $timings = cache()->get($key, []);
            $timings[$operation] = $timings[$operation] ?? [];
            $timings[$operation][] = $duration;

            // Keep only last 100 measurements per operation
            if (count($timings[$operation]) > 100) {
                $timings[$operation] = array_slice($timings[$operation], -100);
            }

            cache()->put($key, $timings, now()->addMinutes(self::PROFILING_TTL_MINUTES));
        } catch (Throwable) {
            // Silent failure for profiling
        }
    }

    private static function recordMemory(string $operation, int $memoryUsage): void
    {
        try {
            $key = self::PROFILING_KEY_PREFIX . 'memory_' . date('Y-m-d-H');
            $memory = cache()->get($key, []);
            $memory[$operation] = $memory[$operation] ?? [];
            $memory[$operation][] = $memoryUsage;

            // Keep only last 100 measurements per operation
            if (count($memory[$operation]) > 100) {
                $memory[$operation] = array_slice($memory[$operation], -100);
            }

            cache()->put($key, $memory, now()->addMinutes(self::PROFILING_TTL_MINUTES));
        } catch (Throwable) {
            // Silent failure for profiling
        }
    }

    private static function recordCallCount(string $operation, int $count): void
    {
        try {
            $key = self::PROFILING_KEY_PREFIX . 'calls_' . date('Y-m-d-H');
            $calls = cache()->get($key, []);
            $calls[$operation] = $count;

            cache()->put($key, $calls, now()->addMinutes(self::PROFILING_TTL_MINUTES));
        } catch (Throwable) {
            // Silent failure for profiling
        }
    }
}