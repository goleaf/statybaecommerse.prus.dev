<?php

declare (strict_types=1);
namespace App\Services;

use Illuminate\Support\LazyCollection;
use Illuminate\Support\Carbon;
/**
 * TimeoutService
 * 
 * Service class containing TimeoutService business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 */
final class TimeoutService
{
    /**
     * Handle withTimeout functionality with proper error handling.
     * @param LazyCollection $collection
     * @param int $seconds
     * @param Carbon|null $startTime
     * @return LazyCollection
     */
    public static function withTimeout(LazyCollection $collection, int $seconds = 30, ?Carbon $startTime = null): LazyCollection
    {
        $timeout = ($startTime ?? now())->addSeconds($seconds);
        return $collection->takeUntilTimeout($timeout);
    }
    /**
     * Handle forScheduledTask functionality with proper error handling.
     * @param LazyCollection $collection
     * @param int $bufferSeconds
     * @return LazyCollection
     */
    public static function forScheduledTask(LazyCollection $collection, int $bufferSeconds = 60): LazyCollection
    {
        // Use LARAVEL_START constant if available, otherwise use current time
        $startTime = defined('LARAVEL_START') ? Carbon::createFromTimestamp(LARAVEL_START) : now();
        // Calculate remaining time with buffer
        $remainingTime = $startTime->addMinutes(14)->subSeconds($bufferSeconds);
        return $collection->takeUntilTimeout($remainingTime);
    }
    /**
     * Handle forImport functionality with proper error handling.
     * @param LazyCollection $collection
     * @param int $minutes
     * @return LazyCollection
     */
    public static function forImport(LazyCollection $collection, int $minutes = 10): LazyCollection
    {
        $timeout = now()->addMinutes($minutes);
        return $collection->takeUntilTimeout($timeout);
    }
    /**
     * Handle forSearch functionality with proper error handling.
     * @param LazyCollection $collection
     * @param int $seconds
     * @return LazyCollection
     */
    public static function forSearch(LazyCollection $collection, int $seconds = 10): LazyCollection
    {
        $timeout = now()->addSeconds($seconds);
        return $collection->takeUntilTimeout($timeout);
    }
    /**
     * Handle forRecommendations functionality with proper error handling.
     * @param LazyCollection $collection
     * @param int $seconds
     * @return LazyCollection
     */
    public static function forRecommendations(LazyCollection $collection, int $seconds = 30): LazyCollection
    {
        $timeout = now()->addSeconds($seconds);
        return $collection->takeUntilTimeout($timeout);
    }
    /**
     * Handle forBackgroundJob functionality with proper error handling.
     * @param LazyCollection $collection
     * @param int $minutes
     * @return LazyCollection
     */
    public static function forBackgroundJob(LazyCollection $collection, int $minutes = 5): LazyCollection
    {
        $timeout = now()->addMinutes($minutes);
        return $collection->takeUntilTimeout($timeout);
    }
    /**
     * Handle isTimeoutReached functionality with proper error handling.
     * @param Carbon $timeout
     * @return bool
     */
    public static function isTimeoutReached(Carbon $timeout): bool
    {
        return now()->greaterThan($timeout);
    }
    /**
     * Handle getRemainingTime functionality with proper error handling.
     * @param Carbon $timeout
     * @return int
     */
    public static function getRemainingTime(Carbon $timeout): int
    {
        $remaining = $timeout->timestamp - now()->timestamp;
        return max(0, $remaining);
    }
    /**
     * Handle logTimeoutInfo functionality with proper error handling.
     * @param string $operation
     * @param int $processedCount
     * @param Carbon $timeout
     * @param int|null $totalCount
     * @return void
     */
    public static function logTimeoutInfo(string $operation, int $processedCount, Carbon $timeout, ?int $totalCount = null): void
    {
        $remainingTime = self::getRemainingTime($timeout);
        $isTimeoutReached = self::isTimeoutReached($timeout);
        \Log::info("Timeout Service - {$operation}", ['processed_count' => $processedCount, 'total_count' => $totalCount, 'remaining_seconds' => $remainingTime, 'timeout_reached' => $isTimeoutReached, 'completion_percentage' => $totalCount ? round($processedCount / $totalCount * 100, 2) : null]);
    }
}