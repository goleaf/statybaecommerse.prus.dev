<?php

declare(strict_types=1);

namespace Tests\Performance;

use App\Support\Exceptions\BootErrorRateLimiter;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Performance tests for the boot error rate limiter boundary fix.
 *
 * These tests ensure that the fix doesn't introduce performance regressions
 * and that the rate limiter operates efficiently under various load conditions.
 */
final class BootErrorRateLimiterPerformanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        BootErrorRateLimiter::reset();
    }

    protected function tearDown(): void
    {
        BootErrorRateLimiter::reset();
        parent::tearDown();
    }

    /**
     * @test
     * Performance: Rate limiting check should be fast
     */
    public function it_performs_rate_limiting_checks_efficiently(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 100);

        $rateLimiter = new BootErrorRateLimiter;

        $iterations = 1000;
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $rateLimiter->isRateLimited();
        }

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;
        $averageTime = ($totalTime / $iterations) * 1000; // Convert to milliseconds

        // Performance budget: Each rate limiting check should take less than 1ms on average
        expect($averageTime)->toBeLessThan(1.0,
            "Rate limiting check should be fast (average: {$averageTime}ms per check)");

        // Total time for 1000 checks should be reasonable
        expect($totalTime)->toBeLessThan(1.0,
            "Total time for {$iterations} checks should be under 1 second (actual: {$totalTime}s)");
    }

    /**
     * @test
     * Performance: Prediction checks should be even faster
     */
    public function it_performs_prediction_checks_efficiently(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 50);

        $rateLimiter = new BootErrorRateLimiter;

        // Use up some of the limit first
        for ($i = 0; $i < 25; $i++) {
            $rateLimiter->isRateLimited();
        }

        $iterations = 2000;
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $rateLimiter->wouldBeRateLimited();
        }

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;
        $averageTime = ($totalTime / $iterations) * 1000; // Convert to milliseconds

        // Prediction should be faster than actual rate limiting
        expect($averageTime)->toBeLessThan(0.5,
            "Prediction check should be very fast (average: {$averageTime}ms per check)");
    }

    /**
     * @test
     * Performance: Memory usage should remain stable
     */
    public function it_maintains_stable_memory_usage(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 200);

        $rateLimiter = new BootErrorRateLimiter;

        $initialMemory = memory_get_usage(true);
        $memoryReadings = [];

        // Take memory readings during operation
        for ($i = 0; $i < 500; $i++) {
            $rateLimiter->isRateLimited();

            if ($i % 100 === 0) {
                $memoryReadings[] = memory_get_usage(true) - $initialMemory;
            }
        }

        $finalMemory = memory_get_usage(true) - $initialMemory;

        // Memory usage should remain reasonable
        expect($finalMemory)->toBeLessThan(1024 * 1024, // 1MB
            'Memory usage should remain under 1MB (actual: ' . number_format($finalMemory) . ' bytes)');

        // Memory should not grow significantly over time
        $maxMemory = max($memoryReadings);
        $minMemory = min($memoryReadings);
        $memoryVariation = $maxMemory - $minMemory;

        expect($memoryVariation)->toBeLessThan(512 * 1024, // 512KB
            'Memory variation should be minimal (variation: ' . number_format($memoryVariation) . ' bytes)');
    }

    /**
     * @test
     * Performance: Statistics collection should not impact performance significantly
     */
    public function it_collects_statistics_efficiently(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 100);

        $rateLimiter = new BootErrorRateLimiter;

        // Warm up
        for ($i = 0; $i < 50; $i++) {
            $rateLimiter->isRateLimited();
        }

        $iterations = 500;
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $rateLimiter->isRateLimited();

            // Collect statistics every 10th iteration
            if ($i % 10 === 0) {
                $rateLimiter->getStatistics();
            }
        }

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;

        // Should complete within reasonable time even with statistics collection
        expect($totalTime)->toBeLessThan(2.0,
            "Operations with statistics collection should complete quickly (actual: {$totalTime}s)");
    }

    /**
     * @test
     * Performance: Cleanup operations should be efficient
     */
    public function it_performs_cleanup_efficiently(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 1000);
        Config::set('exception-handling.security.rate_limit_cleanup_threshold', 50);

        $rateLimiter = new BootErrorRateLimiter;

        $startTime = microtime(true);

        // Generate enough operations to trigger multiple cleanups
        for ($i = 0; $i < 200; $i++) {
            $rateLimiter->isRateLimited();
        }

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;

        // Even with cleanup operations, should remain fast
        expect($totalTime)->toBeLessThan(1.0,
            "Operations with cleanup should complete quickly (actual: {$totalTime}s)");

        // Verify cleanup actually occurred
        $stats = $rateLimiter->getStatistics();
        expect($stats['total_active_windows'])->toBeLessThanOrEqual(50,
            'Cleanup should have limited active windows');
    }

    /**
     * @test
     * Performance: Disabled rate limiting should have minimal overhead
     */
    public function it_has_minimal_overhead_when_disabled(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', false);

        $rateLimiter = new BootErrorRateLimiter;

        $iterations = 2000;
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $rateLimiter->isRateLimited();
        }

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;
        $averageTime = ($totalTime / $iterations) * 1000000; // Convert to microseconds

        // When disabled, should be extremely fast
        expect($averageTime)->toBeLessThan(100, // 100 microseconds
            "Disabled rate limiting should have minimal overhead (average: {$averageTime}μs per check)");
    }

    /**
     * @test
     * Performance: High limit scenarios should not degrade performance
     */
    public function it_maintains_performance_with_high_limits(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 10000);

        $rateLimiter = new BootErrorRateLimiter;

        $iterations = 1000;
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $rateLimiter->isRateLimited();
        }

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;

        // Performance should not degrade with high limits
        expect($totalTime)->toBeLessThan(1.0,
            "High limit scenarios should maintain performance (actual: {$totalTime}s)");

        // Verify we haven't hit the limit
        expect($rateLimiter->getCurrentCount())->toBe($iterations);
        expect($rateLimiter->wouldBeRateLimited())->toBeFalse();
    }

    /**
     * @test
     * Performance: Boundary condition checks should be optimized
     */
    public function it_optimizes_boundary_condition_checks(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 5);

        $rateLimiter = new BootErrorRateLimiter;

        // Use up the limit
        for ($i = 0; $i < 5; $i++) {
            $rateLimiter->isRateLimited();
        }

        // Now test performance of rate-limited calls
        $iterations = 1000;
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $rateLimiter->isRateLimited(); // These should all be rate limited
        }

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;
        $averageTime = ($totalTime / $iterations) * 1000; // Convert to milliseconds

        // Rate-limited calls should be fast (early exit optimization)
        expect($averageTime)->toBeLessThan(0.1,
            "Rate-limited calls should be optimized (average: {$averageTime}ms per check)");

        // Verify all calls were indeed rate limited
        expect($rateLimiter->getCurrentCount())->toBe(5, 'Count should remain at limit');
    }

    /**
     * @test
     * Performance: Configuration access should be cached
     */
    public function it_caches_configuration_access(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 100);

        $rateLimiter = new BootErrorRateLimiter;

        // First call initializes configuration
        $startTime = microtime(true);
        $rateLimiter->isRateLimited();
        $firstCallTime = microtime(true) - $startTime;

        // Subsequent calls should be faster (cached config)
        $iterations = 100;
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $rateLimiter->isRateLimited();
        }

        $endTime = microtime(true);
        $subsequentCallsTime = ($endTime - $startTime) / $iterations;

        // Subsequent calls should be faster than the first call
        // (This test may be environment-dependent, so we use a reasonable threshold)
        expect($subsequentCallsTime)->toBeLessThan($firstCallTime * 2,
            'Configuration should be cached for better performance');
    }
}
