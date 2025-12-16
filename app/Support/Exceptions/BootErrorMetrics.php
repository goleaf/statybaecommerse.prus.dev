<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

use Throwable;

/**
 * Metrics collection for boot error analysis and monitoring.
 */
final class BootErrorMetrics
{
    private const METRICS_KEY_PREFIX = 'boot_errors_metrics_';

    private const METRICS_TTL_MINUTES = 60;

    public static function recordRateLimitHit(): void
    {
        if (! config('exception-handling.performance.track_boot_errors', false)) {
            return;
        }

        $key = self::METRICS_KEY_PREFIX . 'rate_limit_hits_' . date('Y-m-d-H');

        try {
            cache()->increment($key, 1);
            cache()->put($key, cache()->get($key, 0), now()->addMinutes(self::METRICS_TTL_MINUTES));
        } catch (Throwable) {
            // Silently fail metrics collection to avoid impacting error handling
        }
    }

    public static function recordBootErrorPattern(string $pattern): void
    {
        if (! config('exception-handling.performance.track_boot_errors', false)) {
            return;
        }

        $key = self::METRICS_KEY_PREFIX . 'patterns_' . date('Y-m-d-H');

        try {
            $patterns = cache()->get($key, []);
            $patterns[$pattern] = ($patterns[$pattern] ?? 0) + 1;
            cache()->put($key, $patterns, now()->addMinutes(self::METRICS_TTL_MINUTES));
        } catch (Throwable) {
            // Silently fail metrics collection
        }
    }

    public static function getMetrics(): array
    {
        if (! config('exception-handling.performance.track_boot_errors', false)) {
            return [];
        }

        $currentHour = date('Y-m-d-H');

        return [
            'rate_limit_hits' => cache()->get(self::METRICS_KEY_PREFIX . 'rate_limit_hits_' . $currentHour, 0),
            'error_patterns'  => cache()->get(self::METRICS_KEY_PREFIX . 'patterns_' . $currentHour, []),
            'timestamp'       => now()->toISOString(),
        ];
    }
}
