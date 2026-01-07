<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Support\Exceptions\BootErrorRateLimiter;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Property-based tests for boot error rate limiter invariants.
 *
 * These tests verify that the rate limiter maintains its core invariants
 * across a wide range of inputs and scenarios, ensuring the boundary fix
 * works correctly under all conditions.
 */
final class BootErrorRateLimiterPropertyTest extends TestCase
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
     * Property: Counter never exceeds configured limit
     */
    public function property_counter_never_exceeds_limit(): void
    {
        // Test with various limit values
        $limits = [0, 1, 2, 5, 10, 50, 100];

        foreach ($limits as $limit) {
            BootErrorRateLimiter::reset();

            Config::set('exception-handling.security.rate_limit_enabled', true);
            Config::set('exception-handling.security.max_boot_errors_per_minute', $limit);

            $rateLimiter = new BootErrorRateLimiter;

            // Make many more calls than the limit
            $callCount = max($limit * 3, 20);

            for ($i = 0; $i < $callCount; $i++) {
                $rateLimiter->isRateLimited();

                // INVARIANT: Counter must never exceed limit
                expect($rateLimiter->getCurrentCount())
                    ->toBeLessThanOrEqual($limit, "Counter exceeded limit {$limit} on iteration {$i}");
            }

            // INVARIANT: Final count should equal limit (unless limit is 0)
            if ($limit > 0) {
                expect($rateLimiter->getCurrentCount())->toBe($limit, "Final count should equal limit {$limit}");
            } else {
                expect($rateLimiter->getCurrentCount())->toBe(0, 'Final count should be 0 when limit is 0');
            }
        }
    }

    /**
     * @test
     * Property: Rate limiting behavior is consistent with predictions
     */
    public function property_predictions_are_consistent_with_actual_behavior(): void
    {
        $testCases = [
            ['limit' => 1, 'calls' => 5],
            ['limit' => 3, 'calls' => 10],
            ['limit' => 10, 'calls' => 25],
        ];

        foreach ($testCases as $case) {
            BootErrorRateLimiter::reset();

            Config::set('exception-handling.security.rate_limit_enabled', true);
            Config::set('exception-handling.security.max_boot_errors_per_minute', $case['limit']);

            $rateLimiter = new BootErrorRateLimiter;

            for ($i = 0; $i < $case['calls']; $i++) {
                $prediction = $rateLimiter->wouldBeRateLimited();
                $actual = $rateLimiter->isRateLimited();

                // INVARIANT: If prediction says it would be rate limited, actual should be rate limited
                if ($prediction) {
                    expect($actual)->toBeTrue("Prediction mismatch on call {$i} with limit {$case['limit']}");
                }

                // INVARIANT: Counter should not change when rate limited
                if ($actual) {
                    $countBefore = $rateLimiter->getCurrentCount();
                    $rateLimiter->isRateLimited(); // Additional call
                    $countAfter = $rateLimiter->getCurrentCount();

                    expect($countAfter)->toBe($countBefore, 'Counter changed when rate limited');
                }
            }
        }
    }

    /**
     * @test
     * Property: Disabled rate limiting never limits
     */
    public function property_disabled_rate_limiting_never_limits(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', false);

        // Test with various limits (should be ignored when disabled)
        $limits = [0, 1, 5, 100];

        foreach ($limits as $limit) {
            Config::set('exception-handling.security.max_boot_errors_per_minute', $limit);

            $rateLimiter = new BootErrorRateLimiter;

            // Make many calls
            for ($i = 0; $i < 50; $i++) {
                // INVARIANT: Should never be rate limited when disabled
                expect($rateLimiter->isRateLimited())->toBeFalse(
                    "Rate limited when disabled (limit: {$limit}, call: {$i})"
                );

                expect($rateLimiter->wouldBeRateLimited())->toBeFalse(
                    "Predicted rate limiting when disabled (limit: {$limit}, call: {$i})"
                );
            }
        }
    }

    /**
     * @test
     * Property: Statistics accurately reflect internal state
     */
    public function property_statistics_reflect_accurate_state(): void
    {
        $limits = [1, 5, 10];

        foreach ($limits as $limit) {
            BootErrorRateLimiter::reset();

            Config::set('exception-handling.security.rate_limit_enabled', true);
            Config::set('exception-handling.security.max_boot_errors_per_minute', $limit);

            $rateLimiter = new BootErrorRateLimiter;

            // Make calls up to and beyond the limit
            $callsToMake = $limit + 5;

            for ($i = 0; $i < $callsToMake; $i++) {
                $rateLimiter->isRateLimited();

                $stats = $rateLimiter->getStatistics();
                $currentCount = $rateLimiter->getCurrentCount();

                // INVARIANT: Statistics should be consistent with current state
                expect($stats['enabled'])->toBeTrue('Statistics should show enabled');
                expect($stats['max_errors_per_minute'])->toBe($limit, 'Statistics should show correct limit');
                expect(array_sum($stats['current_counts']))->toBe($currentCount, 'Statistics count should match current count');
                expect($stats['total_active_windows'])->toBeGreaterThan(0, 'Should have active windows');
            }
        }
    }

    /**
     * @test
     * Property: Reset completely clears all state
     */
    public function property_reset_clears_all_state(): void
    {
        $limits = [1, 5, 10];

        foreach ($limits as $limit) {
            Config::set('exception-handling.security.rate_limit_enabled', true);
            Config::set('exception-handling.security.max_boot_errors_per_minute', $limit);

            $rateLimiter = new BootErrorRateLimiter;

            // Use up the limit
            for ($i = 0; $i < $limit + 5; $i++) {
                $rateLimiter->isRateLimited();
            }

            // Verify we're at the limit
            expect($rateLimiter->wouldBeRateLimited())->toBeTrue('Should be at limit before reset');

            // Reset
            BootErrorRateLimiter::reset();

            // INVARIANT: After reset, should behave as if fresh
            expect($rateLimiter->getCurrentCount())->toBe(0, 'Count should be 0 after reset');
            expect($rateLimiter->wouldBeRateLimited())->toBeFalse('Should not predict rate limiting after reset');

            // First call after reset should succeed
            expect($rateLimiter->isRateLimited())->toBeFalse('First call after reset should succeed');
            expect($rateLimiter->getCurrentCount())->toBe(1, 'Count should be 1 after first call post-reset');
        }
    }

    /**
     * @test
     * Property: Time window behavior is consistent
     */
    public function property_time_window_behavior_is_consistent(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 5);

        $rateLimiter = new BootErrorRateLimiter;

        // Make calls and verify time window consistency
        for ($i = 0; $i < 10; $i++) {
            $statsBefore = $rateLimiter->getStatistics();
            $rateLimiter->isRateLimited();
            $statsAfter = $rateLimiter->getStatistics();

            // INVARIANT: Active windows should not decrease during normal operation
            expect($statsAfter['total_active_windows'])
                ->toBeGreaterThanOrEqual($statsBefore['total_active_windows'],
                    'Active windows should not decrease during normal operation');

            // INVARIANT: Current counts should only increase or stay same
            $beforeSum = array_sum($statsBefore['current_counts']);
            $afterSum = array_sum($statsAfter['current_counts']);

            expect($afterSum)->toBeGreaterThanOrEqual($beforeSum,
                'Total count should not decrease during normal operation');
        }
    }

    /**
     * @test
     * Property: Boundary conditions are handled correctly
     */
    public function property_boundary_conditions_are_handled_correctly(): void
    {
        $boundaryLimits = [0, 1, 2]; // Edge cases

        foreach ($boundaryLimits as $limit) {
            BootErrorRateLimiter::reset();

            Config::set('exception-handling.security.rate_limit_enabled', true);
            Config::set('exception-handling.security.max_boot_errors_per_minute', $limit);

            $rateLimiter = new BootErrorRateLimiter;

            if ($limit === 0) {
                // INVARIANT: With zero limit, should be rate limited immediately
                expect($rateLimiter->isRateLimited())->toBeTrue('Should be rate limited immediately with zero limit');
                expect($rateLimiter->getCurrentCount())->toBe(0, 'Count should remain 0 with zero limit');
            } else {
                // INVARIANT: Should allow exactly 'limit' number of calls
                $successfulCalls = 0;

                for ($i = 0; $i < $limit + 5; $i++) {
                    if (! $rateLimiter->isRateLimited()) {
                        $successfulCalls++;
                    }
                }

                expect($successfulCalls)->toBe($limit, "Should allow exactly {$limit} successful calls");
                expect($rateLimiter->getCurrentCount())->toBe($limit, 'Final count should equal limit');
            }
        }
    }

    /**
     * @test
     * Property: Memory usage remains bounded
     */
    public function property_memory_usage_remains_bounded(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 100);
        Config::set('exception-handling.security.rate_limit_cleanup_threshold', 10);

        $rateLimiter = new BootErrorRateLimiter;

        $initialMemory = memory_get_usage();

        // Make many calls to test memory management
        for ($i = 0; $i < 200; $i++) {
            $rateLimiter->isRateLimited();

            // Check memory periodically
            if ($i % 50 === 0) {
                $currentMemory = memory_get_usage();
                $memoryIncrease = $currentMemory - $initialMemory;

                // INVARIANT: Memory usage should remain bounded
                expect($memoryIncrease)->toBeLessThan(5 * 1024 * 1024, // 5MB
                    "Memory usage should remain bounded (increase: {$memoryIncrease} bytes at iteration {$i})");
            }
        }

        // INVARIANT: Cleanup should have occurred
        $stats = $rateLimiter->getStatistics();
        expect($stats['total_active_windows'])->toBeLessThanOrEqual(10,
            'Cleanup should limit active windows');
    }
}
