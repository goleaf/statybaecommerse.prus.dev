<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

/**
 * Rate limiter for boot error logging to prevent spam attacks.
 */
final class BootErrorRateLimiter
{
    private const DEFAULT_RATE_LIMIT_ENABLED = true;

    private const DEFAULT_MAX_ERRORS_PER_MINUTE = 10;

    private const DEFAULT_CLEANUP_THRESHOLD = 60;

    private const CLEANUP_WINDOW_MINUTES = 5;

    private static array $bootErrorCounts = [];

    private static ?bool $rateLimitEnabled = null;

    private static ?int $maxErrorsPerMinute = null;

    private static ?int $cleanupThreshold = null;

    public function isRateLimited(): bool
    {
        BootErrorProfiler::startTiming('rate_limit_check');
        BootErrorProfiler::incrementCallCount('rate_limit_check');

        $this->initializeConfig();

        if (! self::$rateLimitEnabled) {
            BootErrorProfiler::endTiming('rate_limit_check');

            return false;
        }

        $key = $this->generateKey();

        if (! isset(self::$bootErrorCounts[$key])) {
            self::$bootErrorCounts[$key] = 0;
        }

        // Check if we're already at the limit BEFORE incrementing
        if (self::$bootErrorCounts[$key] >= self::$maxErrorsPerMinute) {
            BootErrorMetrics::recordRateLimitHit();
            BootErrorProfiler::endTiming('rate_limit_check');

            return true;
        }

        // Increment counter - this is safe in PHP's single-threaded model
        self::$bootErrorCounts[$key]++;

        // Clean old entries to prevent memory leaks
        $this->cleanupOldEntries();

        BootErrorProfiler::recordMemoryUsage('rate_limit_check');
        BootErrorProfiler::endTiming('rate_limit_check');

        return false;
    }

    /**
     * Get current error count for testing purposes.
     */
    public function getCurrentCount(): int
    {
        $this->initializeConfig();
        $key = $this->generateKey();

        return self::$bootErrorCounts[$key] ?? 0;
    }

    /**
     * Check if rate limiting would be triggered without incrementing.
     */
    public function wouldBeRateLimited(): bool
    {
        $this->initializeConfig();

        if (! self::$rateLimitEnabled) {
            return false;
        }

        $key = $this->generateKey();
        $currentCount = self::$bootErrorCounts[$key] ?? 0;

        return $currentCount >= self::$maxErrorsPerMinute;
    }

    /**
     * Get rate limiting statistics for monitoring.
     */
    public function getStatistics(): array
    {
        $this->initializeConfig();

        return [
            'enabled'               => self::$rateLimitEnabled,
            'max_errors_per_minute' => self::$maxErrorsPerMinute,
            'current_counts'        => self::$bootErrorCounts,
            'total_active_windows'  => count(self::$bootErrorCounts),
            'cleanup_threshold'     => self::$cleanupThreshold,
        ];
    }

    /**
     * Reset rate limiting counters (for testing).
     */
    public static function reset(): void
    {
        self::$bootErrorCounts = [];
        self::$rateLimitEnabled = null;
        self::$maxErrorsPerMinute = null;
        self::$cleanupThreshold = null;
    }

    private function initializeConfig(): void
    {
        // In testing environment, always get fresh config to allow dynamic changes
        if (app()->environment('testing')) {
            self::$rateLimitEnabled = config('exception-handling.security.rate_limit_enabled', self::DEFAULT_RATE_LIMIT_ENABLED);
            self::$maxErrorsPerMinute = config('exception-handling.security.max_boot_errors_per_minute', self::DEFAULT_MAX_ERRORS_PER_MINUTE);
            self::$cleanupThreshold = config('exception-handling.security.rate_limit_cleanup_threshold', self::DEFAULT_CLEANUP_THRESHOLD);

            return;
        }

        if (self::$rateLimitEnabled === null) {
            self::$rateLimitEnabled = config('exception-handling.security.rate_limit_enabled', self::DEFAULT_RATE_LIMIT_ENABLED);
            self::$maxErrorsPerMinute = config('exception-handling.security.max_boot_errors_per_minute', self::DEFAULT_MAX_ERRORS_PER_MINUTE);
            self::$cleanupThreshold = config('exception-handling.security.rate_limit_cleanup_threshold', self::DEFAULT_CLEANUP_THRESHOLD);
        }
    }

    private function generateKey(): string
    {
        $baseKey = 'boot_errors_' . date('Y-m-d-H-i');

        // Support distributed rate limiting with Redis
        if (config('cache.default') === 'redis' && config('exception-handling.performance.distributed_rate_limiting', false)) {
            return 'distributed:' . $baseKey;
        }

        return $baseKey;
    }

    private function cleanupOldEntries(): void
    {
        if (count(self::$bootErrorCounts) <= self::$cleanupThreshold) {
            return;
        }

        $cutoffTime = date('Y-m-d-H-i', strtotime('-' . self::CLEANUP_WINDOW_MINUTES . ' minutes'));

        self::$bootErrorCounts = array_filter(
            self::$bootErrorCounts,
            static fn (string $key): bool => $key >= $cutoffTime,
            ARRAY_FILTER_USE_KEY
        );
    }
}
