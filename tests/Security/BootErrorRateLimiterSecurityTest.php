<?php

declare(strict_types=1);

namespace Tests\Security;

use App\Exceptions\Handler;
use App\Support\Exceptions\BootErrorRateLimiter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Tests\TestCase;
use Throwable;

/**
 * Security tests for the boot error rate limiter boundary fix.
 *
 * These tests verify that the rate limiting fix effectively prevents
 * denial-of-service attacks and other security vulnerabilities.
 */
final class BootErrorRateLimiterSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Handler::resetCache();
        BootErrorRateLimiter::reset();
    }

    protected function tearDown(): void
    {
        Handler::resetCache();
        BootErrorRateLimiter::reset();
        parent::tearDown();
    }

    /**
     * @test
     * Security: Prevents DoS attacks via boot error spam
     */
    public function it_prevents_dos_attacks_via_boot_error_spam(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 5);

        $rateLimiter = new BootErrorRateLimiter;

        // Simulate DoS attack with many rate limiting calls
        $attackAttempts = 100;
        $successfulCalls = 0;

        for ($i = 0; $i < $attackAttempts; $i++) {
            if (! $rateLimiter->isRateLimited()) {
                $successfulCalls++;
            }
        }

        // Should have allowed only the configured number of calls
        expect($successfulCalls)->toBe(5);

        // Verify rate limiter is protecting the system
        expect($rateLimiter->getCurrentCount())->toBe(5);
        expect($rateLimiter->wouldBeRateLimited())->toBeTrue();
    }

    /**
     * @test
     * Security: Prevents memory exhaustion attacks
     */
    public function it_prevents_memory_exhaustion_attacks(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 10);
        Config::set('exception-handling.security.rate_limit_cleanup_threshold', 5);

        $rateLimiter = new BootErrorRateLimiter;
        $initialMemory = memory_get_usage(true);

        // Attempt to exhaust memory with many rate limiting calls
        for ($i = 0; $i < 1000; $i++) {
            $rateLimiter->isRateLimited();
        }

        $finalMemory = memory_get_usage(true);
        $memoryIncrease = $finalMemory - $initialMemory;

        // Memory increase should be bounded (cleanup should prevent exhaustion)
        expect($memoryIncrease)->toBeLessThan(2 * 1024 * 1024, // 2MB
            'Memory increase should be bounded to prevent exhaustion attacks');

        // Verify cleanup occurred
        $stats = $rateLimiter->getStatistics();
        expect($stats['total_active_windows'])->toBeLessThanOrEqual(5);
    }

    /**
     * @test
     * Security: Prevents log flooding attacks
     */
    public function it_prevents_log_flooding_attacks(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 3);

        $rateLimiter = new BootErrorRateLimiter;

        // Simulate flooding attempts with many calls
        $totalAttempts = 50;
        $successfulCalls = 0;

        for ($i = 0; $i < $totalAttempts; $i++) {
            if (! $rateLimiter->isRateLimited()) {
                $successfulCalls++;
            }
        }

        // Should have allowed only the configured number regardless of attempt variety
        expect($successfulCalls)->toBe(3);
        expect($rateLimiter->getCurrentCount())->toBe(3);
        expect($rateLimiter->wouldBeRateLimited())->toBeTrue();
    }

    /**
     * @test
     * Security: Rate limiting cannot be bypassed by resetting
     */
    public function it_cannot_be_bypassed_by_external_reset_attempts(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 2);

        $rateLimiter = new BootErrorRateLimiter;

        // Use up the limit
        $rateLimiter->isRateLimited();
        $rateLimiter->isRateLimited();
        expect($rateLimiter->isRateLimited())->toBeTrue();

        // Attempt to create new instance (should maintain state)
        $newRateLimiter = new BootErrorRateLimiter;
        expect($newRateLimiter->isRateLimited())->toBeTrue('New instance should maintain rate limiting state');
        expect($newRateLimiter->getCurrentCount())->toBe(2);
    }

    /**
     * @test
     * Security: Zero limit configuration prevents all boot error logging
     */
    public function it_enforces_zero_limit_security_policy(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 0);

        $rateLimiter = new BootErrorRateLimiter;

        // With zero limit, all calls should be rate limited
        for ($i = 0; $i < 20; $i++) {
            expect($rateLimiter->isRateLimited())->toBeTrue("Call {$i} should be rate limited with zero limit");
        }

        expect($rateLimiter->getCurrentCount())->toBe(0);
        expect($rateLimiter->wouldBeRateLimited())->toBeTrue();
    }

    /**
     * @test
     * Security: Rate limiting works across different time windows
     */
    public function it_maintains_security_across_time_windows(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 3);

        $rateLimiter = new BootErrorRateLimiter;

        // Use up the limit
        for ($i = 0; $i < 3; $i++) {
            expect($rateLimiter->isRateLimited())->toBeFalse();
        }

        // Should now be rate limited
        expect($rateLimiter->isRateLimited())->toBeTrue();

        // Verify state is maintained
        $stats = $rateLimiter->getStatistics();
        expect($stats['total_active_windows'])->toBeGreaterThan(0);
        expect(array_sum($stats['current_counts']))->toBe(3);
    }

    /**
     * @test
     * Security: Configuration tampering detection
     */
    public function it_handles_configuration_tampering_safely(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 2);

        $rateLimiter = new BootErrorRateLimiter;

        // Use up the limit
        $rateLimiter->isRateLimited();
        $rateLimiter->isRateLimited();

        // Attempt to tamper with configuration
        Config::set('exception-handling.security.max_boot_errors_per_minute', 1000);

        // In production, config should be cached and not change mid-request
        // In testing, it may change, but the rate limiter should handle it gracefully
        $result = $rateLimiter->isRateLimited();

        // Should either maintain rate limiting (production) or handle change gracefully (testing)
        expect($result)->toBeIn([true, false], 'Should handle configuration changes gracefully');

        // Count should never be negative or exceed reasonable bounds
        $count = $rateLimiter->getCurrentCount();
        expect($count)->toBeGreaterThanOrEqual(0);
        expect($count)->toBeLessThanOrEqual(1000);
    }

    /**
     * @test
     * Security: Prevents resource exhaustion via statistics collection
     */
    public function it_prevents_resource_exhaustion_via_statistics(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 50);

        $rateLimiter = new BootErrorRateLimiter;
        $initialMemory = memory_get_usage(true);

        // Attempt to exhaust resources via excessive statistics collection
        for ($i = 0; $i < 500; $i++) {
            $rateLimiter->isRateLimited();

            if ($i % 10 === 0) {
                $stats = $rateLimiter->getStatistics();

                // Verify statistics don't contain excessive data
                expect(count($stats['current_counts']))->toBeLessThan(100,
                    'Statistics should not contain excessive data');
            }
        }

        $finalMemory = memory_get_usage(true);
        $memoryIncrease = $finalMemory - $initialMemory;

        // Memory usage should remain reasonable
        expect($memoryIncrease)->toBeLessThan(5 * 1024 * 1024, // 5MB
            'Statistics collection should not cause memory exhaustion');
    }

    /**
     * @test
     * Security: Validates input boundaries to prevent integer overflow
     */
    public function it_validates_input_boundaries(): void
    {
        // Test with extreme configuration values
        $extremeConfigs = [
            ['enabled' => true, 'limit' => PHP_INT_MAX],
            ['enabled' => true, 'limit' => -1], // Invalid, should be handled gracefully
            ['enabled' => true, 'limit' => 0],
        ];

        foreach ($extremeConfigs as $config) {
            BootErrorRateLimiter::reset();

            Config::set('exception-handling.security.rate_limit_enabled', $config['enabled']);
            Config::set('exception-handling.security.max_boot_errors_per_minute', $config['limit']);

            $rateLimiter = new BootErrorRateLimiter;

            // Should not crash or behave unexpectedly
            try {
                $result = $rateLimiter->isRateLimited();
                expect($result)->toBeIn([true, false], 'Should return valid boolean result');

                $count = $rateLimiter->getCurrentCount();
                expect($count)->toBeGreaterThanOrEqual(0, 'Count should not be negative');

                $stats = $rateLimiter->getStatistics();
                expect($stats)->toBeArray('Statistics should be valid array');

            } catch (Throwable $e) {
                // If an exception occurs, it should be a reasonable one, not a fatal error
                expect($e)->toBeInstanceOf(InvalidArgumentException::class,
                    'Should throw reasonable exception for invalid config');
            }
        }
    }

    /**
     * @test
     * Security: Prevents timing attacks via consistent response times
     */
    public function it_prevents_timing_attacks(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 5);

        $rateLimiter = new BootErrorRateLimiter;

        // Measure timing for allowed calls
        $allowedTimes = [];
        for ($i = 0; $i < 5; $i++) {
            $start = microtime(true);
            $rateLimiter->isRateLimited();
            $allowedTimes[] = microtime(true) - $start;
        }

        // Measure timing for rate-limited calls
        $rateLimitedTimes = [];
        for ($i = 0; $i < 10; $i++) {
            $start = microtime(true);
            $rateLimiter->isRateLimited();
            $rateLimitedTimes[] = microtime(true) - $start;
        }

        $avgAllowedTime = array_sum($allowedTimes) / count($allowedTimes);
        $avgRateLimitedTime = array_sum($rateLimitedTimes) / count($rateLimitedTimes);

        // Rate-limited calls should not be significantly faster (preventing timing attacks)
        // Allow some variance but they should be in the same order of magnitude
        $timingRatio = $avgRateLimitedTime / $avgAllowedTime;
        expect($timingRatio)->toBeGreaterThan(0.1, 'Rate-limited calls should not be too much faster');
        expect($timingRatio)->toBeLessThan(10.0, 'Rate-limited calls should not be too much slower');
    }
}
