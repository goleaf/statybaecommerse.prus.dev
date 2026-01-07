<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Support\Exceptions\BootErrorRateLimiter;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Comprehensive tests for the boot error rate limiter boundary condition fix.
 *
 * This test suite specifically validates the critical fix that prevents the counter
 * from exceeding the configured limit by checking BEFORE incrementing rather than after.
 *
 * The fix addresses a security vulnerability where the rate limiter could allow
 * one extra error beyond the configured limit, potentially enabling DoS attacks.
 */
final class BootErrorRateLimiterBoundaryFixTest extends TestCase
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
     * Core boundary condition: Verify the fix prevents exceeding the limit
     */
    public function it_prevents_counter_from_exceeding_configured_limit(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 3);

        $rateLimiter = new BootErrorRateLimiter;

        // First 3 calls should succeed and increment counter
        expect($rateLimiter->isRateLimited())->toBeFalse()
            ->and($rateLimiter->getCurrentCount())->toBe(1);

        expect($rateLimiter->isRateLimited())->toBeFalse()
            ->and($rateLimiter->getCurrentCount())->toBe(2);

        expect($rateLimiter->isRateLimited())->toBeFalse()
            ->and($rateLimiter->getCurrentCount())->toBe(3);

        // Fourth call should be rate limited WITHOUT incrementing
        expect($rateLimiter->isRateLimited())->toBeTrue()
            ->and($rateLimiter->getCurrentCount())->toBe(3, 'Counter must not exceed limit');

        // Subsequent calls should remain rate limited with counter unchanged
        expect($rateLimiter->isRateLimited())->toBeTrue()
            ->and($rateLimiter->getCurrentCount())->toBe(3, 'Counter must remain at limit');
    }

    /**
     * @test
     * Edge case: Zero limit should immediately rate limit
     */
    public function it_handles_zero_limit_correctly(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 0);

        $rateLimiter = new BootErrorRateLimiter;

        // With zero limit, first call should be rate limited immediately
        expect($rateLimiter->isRateLimited())->toBeTrue()
            ->and($rateLimiter->getCurrentCount())->toBe(0, 'Counter should remain at zero');

        // Subsequent calls should also be rate limited
        expect($rateLimiter->isRateLimited())->toBeTrue()
            ->and($rateLimiter->getCurrentCount())->toBe(0, 'Counter should still be zero');
    }

    /**
     * @test
     * Edge case: Limit of one should allow exactly one call
     */
    public function it_handles_limit_of_one_correctly(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 1);

        $rateLimiter = new BootErrorRateLimiter;

        // First call should succeed
        expect($rateLimiter->isRateLimited())->toBeFalse()
            ->and($rateLimiter->getCurrentCount())->toBe(1);

        // Second call should be rate limited
        expect($rateLimiter->isRateLimited())->toBeTrue()
            ->and($rateLimiter->getCurrentCount())->toBe(1, 'Counter should not increment when rate limited');
    }

    /**
     * @test
     * Stress test: High volume concurrent-like access
     */
    public function it_maintains_boundary_under_high_volume_access(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 10);

        $rateLimiter = new BootErrorRateLimiter;
        $results = [];

        // Simulate 50 rapid calls
        for ($i = 0; $i < 50; $i++) {
            $results[] = $rateLimiter->isRateLimited();
        }

        // Verify exactly 10 calls succeeded, 40 were rate limited
        $successful = array_filter($results, static fn ($result) => ! $result);
        $rateLimited = array_filter($results, static fn ($result) => $result);

        expect($successful)->toHaveCount(10, 'Exactly 10 calls should succeed')
            ->and($rateLimited)->toHaveCount(40, 'Exactly 40 calls should be rate limited')
            ->and($rateLimiter->getCurrentCount())->toBe(10, 'Final count should equal limit');
    }

    /**
     * @test
     * Prediction accuracy: wouldBeRateLimited should predict correctly
     */
    public function it_predicts_rate_limiting_accurately(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 2);

        $rateLimiter = new BootErrorRateLimiter;

        // Initially should not predict rate limiting
        expect($rateLimiter->wouldBeRateLimited())->toBeFalse();

        // After first call, still should not predict rate limiting
        $rateLimiter->isRateLimited();
        expect($rateLimiter->wouldBeRateLimited())->toBeFalse();

        // After second call (at limit), should predict rate limiting
        $rateLimiter->isRateLimited();
        expect($rateLimiter->wouldBeRateLimited())->toBeTrue();

        // Verify prediction was correct
        expect($rateLimiter->isRateLimited())->toBeTrue()
            ->and($rateLimiter->getCurrentCount())->toBe(2, 'Count should remain at limit');
    }

    /**
     * @test
     * Configuration changes: Dynamic config updates in testing
     */
    public function it_respects_dynamic_configuration_changes(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 2);

        $rateLimiter = new BootErrorRateLimiter;

        // Use up the initial limit
        $rateLimiter->isRateLimited();
        $rateLimiter->isRateLimited();
        expect($rateLimiter->isRateLimited())->toBeTrue();

        // Change configuration to higher limit
        Config::set('exception-handling.security.max_boot_errors_per_minute', 5);

        // Should now allow more calls (in testing environment)
        expect($rateLimiter->isRateLimited())->toBeFalse()
            ->and($rateLimiter->getCurrentCount())->toBe(3);
    }

    /**
     * @test
     * Disabled rate limiting: Should never limit regardless of calls
     */
    public function it_never_limits_when_disabled(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', false);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 1);

        $rateLimiter = new BootErrorRateLimiter;

        // Make many calls - none should be rate limited
        for ($i = 0; $i < 20; $i++) {
            expect($rateLimiter->isRateLimited())->toBeFalse("Call {$i} should not be rate limited when disabled");
            expect($rateLimiter->wouldBeRateLimited())->toBeFalse('wouldBeRateLimited should return false when disabled');
        }
    }

    /**
     * @test
     * Statistics accuracy: Monitoring data should reflect actual state
     */
    public function it_provides_accurate_statistics(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 5);
        Config::set('exception-handling.security.rate_limit_cleanup_threshold', 60);

        $rateLimiter = new BootErrorRateLimiter;

        // Make some calls
        $rateLimiter->isRateLimited();
        $rateLimiter->isRateLimited();
        $rateLimiter->isRateLimited();

        $stats = $rateLimiter->getStatistics();

        expect($stats)
            ->toBeArray()
            ->toHaveKey('enabled', true)
            ->toHaveKey('max_errors_per_minute', 5)
            ->toHaveKey('current_counts')
            ->toHaveKey('total_active_windows', 1)
            ->toHaveKey('cleanup_threshold', 60);

        expect($stats['current_counts'])->toBeArray()
            ->and(array_sum($stats['current_counts']))->toBe(3, 'Total count should match actual calls');
    }

    /**
     * @test
     * Reset functionality: Should completely clear state
     */
    public function it_resets_completely(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 2);

        $rateLimiter = new BootErrorRateLimiter;

        // Use up the limit
        $rateLimiter->isRateLimited();
        $rateLimiter->isRateLimited();
        expect($rateLimiter->isRateLimited())->toBeTrue();

        // Reset should clear everything
        BootErrorRateLimiter::reset();

        // Should work again after reset
        expect($rateLimiter->isRateLimited())->toBeFalse()
            ->and($rateLimiter->getCurrentCount())->toBe(1)
            ->and($rateLimiter->wouldBeRateLimited())->toBeFalse();
    }

    /**
     * @test
     * Memory efficiency: Verify no memory leaks in long-running scenarios
     */
    public function it_manages_memory_efficiently(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 100);
        Config::set('exception-handling.security.rate_limit_cleanup_threshold', 10);

        $rateLimiter = new BootErrorRateLimiter;

        $initialMemory = memory_get_usage();

        // Make many calls to trigger cleanup
        for ($i = 0; $i < 50; $i++) {
            $rateLimiter->isRateLimited();
        }

        $finalMemory = memory_get_usage();
        $memoryIncrease = $finalMemory - $initialMemory;

        // Memory increase should be reasonable (less than 1MB for this test)
        expect($memoryIncrease)->toBeLessThan(1024 * 1024, 'Memory usage should remain reasonable');

        $stats = $rateLimiter->getStatistics();
        expect($stats['total_active_windows'])->toBeLessThanOrEqual(10, 'Cleanup should limit active windows');
    }
}
