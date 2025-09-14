<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\LazyCollection;
use Illuminate\Support\Carbon;

final class TimeoutService
{
    /**
     * Create a LazyCollection with timeout protection for database operations
     */
    public static function withTimeout(
        LazyCollection $collection,
        int $seconds = 30,
        ?Carbon $startTime = null
    ): LazyCollection {
        $timeout = ($startTime ?? now())->addSeconds($seconds);
        
        return $collection->takeUntilTimeout($timeout);
    }

    /**
     * Create a LazyCollection with timeout protection for scheduled tasks
     * Ensures tasks complete within their allocated time window
     */
    public static function forScheduledTask(
        LazyCollection $collection,
        int $bufferSeconds = 60
    ): LazyCollection {
        // Use LARAVEL_START constant if available, otherwise use current time
        $startTime = defined('LARAVEL_START') 
            ? Carbon::createFromTimestamp(LARAVEL_START)
            : now();
            
        // Calculate remaining time with buffer
        $remainingTime = $startTime->addMinutes(14)->subSeconds($bufferSeconds);
        
        return $collection->takeUntilTimeout($remainingTime);
    }

    /**
     * Create a LazyCollection with timeout protection for import operations
     */
    public static function forImport(
        LazyCollection $collection,
        int $minutes = 10
    ): LazyCollection {
        $timeout = now()->addMinutes($minutes);
        
        return $collection->takeUntilTimeout($timeout);
    }

    /**
     * Create a LazyCollection with timeout protection for search operations
     */
    public static function forSearch(
        LazyCollection $collection,
        int $seconds = 10
    ): LazyCollection {
        $timeout = now()->addSeconds($seconds);
        
        return $collection->takeUntilTimeout($timeout);
    }

    /**
     * Create a LazyCollection with timeout protection for recommendation generation
     */
    public static function forRecommendations(
        LazyCollection $collection,
        int $seconds = 30
    ): LazyCollection {
        $timeout = now()->addSeconds($seconds);
        
        return $collection->takeUntilTimeout($timeout);
    }

    /**
     * Create a LazyCollection with timeout protection for background jobs
     */
    public static function forBackgroundJob(
        LazyCollection $collection,
        int $minutes = 5
    ): LazyCollection {
        $timeout = now()->addMinutes($minutes);
        
        return $collection->takeUntilTimeout($timeout);
    }

    /**
     * Check if a timeout has been reached
     */
    public static function isTimeoutReached(Carbon $timeout): bool
    {
        return now()->greaterThan($timeout);
    }

    /**
     * Get remaining time until timeout
     */
    public static function getRemainingTime(Carbon $timeout): int
    {
        $remaining = $timeout->diffInSeconds(now());
        return max(0, $remaining);
    }

    /**
     * Log timeout information
     */
    public static function logTimeoutInfo(
        string $operation,
        int $processedCount,
        Carbon $timeout,
        ?int $totalCount = null
    ): void {
        $remainingTime = self::getRemainingTime($timeout);
        $isTimeoutReached = self::isTimeoutReached($timeout);
        
        \Log::info("Timeout Service - {$operation}", [
            'processed_count' => $processedCount,
            'total_count' => $totalCount,
            'remaining_seconds' => $remainingTime,
            'timeout_reached' => $isTimeoutReached,
            'completion_percentage' => $totalCount ? round(($processedCount / $totalCount) * 100, 2) : null,
        ]);
    }
}
