<?php

declare(strict_types=1);

use App\Support\Exceptions\BootErrorRateLimiter;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Test specifically for the rate limiting boundary condition fix.
 *
 * This test verifies that the fix prevents the count from exceeding
 * the configured limit by checking before incrementing rather than after.
 */
class BootErrorRateLimiterFixTest extends TestCase
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

    public function test_rate_limiting_stops_exactly_at_limit(): void
    {
        // Set a low limit to make the test clear
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 3);

        $rateLimiter = new BootErrorRateLimiter;

        // First 3 calls should increment the counter and not be rate limited
        $this->assertFalse($rateLimiter->isRateLimited(), 'Call 1 should not be rate limited');
        $this->assertSame(1, $rateLimiter->getCurrentCount(), 'Count should be 1 after first call');

        $this->assertFalse($rateLimiter->isRateLimited(), 'Call 2 should not be rate limited');
        $this->assertSame(2, $rateLimiter->getCurrentCount(), 'Count should be 2 after second call');

        $this->assertFalse($rateLimiter->isRateLimited(), 'Call 3 should not be rate limited');
        $this->assertSame(3, $rateLimiter->getCurrentCount(), 'Count should be 3 after third call');

        // Fourth call should be rate limited and NOT increment the counter
        $this->assertTrue($rateLimiter->isRateLimited(), 'Call 4 should be rate limited');
        $this->assertSame(3, $rateLimiter->getCurrentCount(), 'Count should remain at 3 when rate limited');

        // Subsequent calls should continue to be rate limited without incrementing
        $this->assertTrue($rateLimiter->isRateLimited(), 'Call 5 should be rate limited');
        $this->assertSame(3, $rateLimiter->getCurrentCount(), 'Count should still be 3');

        $this->assertTrue($rateLimiter->isRateLimited(), 'Call 6 should be rate limited');
        $this->assertSame(3, $rateLimiter->getCurrentCount(), 'Count should still be 3');
    }

    public function test_would_be_rate_limited_predicts_correctly(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 2);

        $rateLimiter = new BootErrorRateLimiter;

        // Initially should not be rate limited
        $this->assertFalse($rateLimiter->wouldBeRateLimited(), 'Should not predict rate limiting initially');

        // After first call, still should not be rate limited
        $rateLimiter->isRateLimited();
        $this->assertFalse($rateLimiter->wouldBeRateLimited(), 'Should not predict rate limiting after 1 call');

        // After second call, should predict rate limiting
        $rateLimiter->isRateLimited();
        $this->assertTrue($rateLimiter->wouldBeRateLimited(), 'Should predict rate limiting after 2 calls');

        // Verify the prediction was correct
        $this->assertTrue($rateLimiter->isRateLimited(), 'Should actually be rate limited');
        $this->assertSame(2, $rateLimiter->getCurrentCount(), 'Count should remain at limit');
    }

    public function test_edge_case_with_limit_of_one(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 1);

        $rateLimiter = new BootErrorRateLimiter;

        // First call should not be rate limited
        $this->assertFalse($rateLimiter->isRateLimited(), 'First call should not be rate limited');
        $this->assertSame(1, $rateLimiter->getCurrentCount(), 'Count should be 1');

        // Second call should be rate limited
        $this->assertTrue($rateLimiter->isRateLimited(), 'Second call should be rate limited');
        $this->assertSame(1, $rateLimiter->getCurrentCount(), 'Count should remain at 1');
    }

    public function test_edge_case_with_limit_of_zero(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 0);

        $rateLimiter = new BootErrorRateLimiter;

        // With zero limit, first call should be rate limited immediately
        $this->assertTrue($rateLimiter->isRateLimited(), 'First call should be rate limited with zero limit');
        $this->assertSame(0, $rateLimiter->getCurrentCount(), 'Count should remain at 0');

        // Subsequent calls should also be rate limited
        $this->assertTrue($rateLimiter->isRateLimited(), 'Second call should be rate limited');
        $this->assertSame(0, $rateLimiter->getCurrentCount(), 'Count should still be 0');
    }

    public function test_boundary_condition_with_high_limit(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 100);

        $rateLimiter = new BootErrorRateLimiter;

        // Make 100 calls - all should succeed
        for ($i = 1; $i <= 100; $i++) {
            $this->assertFalse($rateLimiter->isRateLimited(), "Call {$i} should not be rate limited");
            $this->assertSame($i, $rateLimiter->getCurrentCount(), "Count should be {$i} after call {$i}");
        }

        // 101st call should be rate limited
        $this->assertTrue($rateLimiter->isRateLimited(), 'Call 101 should be rate limited');
        $this->assertSame(100, $rateLimiter->getCurrentCount(), 'Count should remain at 100');
    }

    public function test_statistics_method_provides_monitoring_data(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 5);

        $rateLimiter = new BootErrorRateLimiter;

        // Make a few calls to generate data
        $rateLimiter->isRateLimited();
        $rateLimiter->isRateLimited();

        $stats = $rateLimiter->getStatistics();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('enabled', $stats);
        $this->assertArrayHasKey('max_errors_per_minute', $stats);
        $this->assertArrayHasKey('current_counts', $stats);
        $this->assertArrayHasKey('total_active_windows', $stats);
        $this->assertArrayHasKey('cleanup_threshold', $stats);

        $this->assertTrue($stats['enabled']);
        $this->assertSame(5, $stats['max_errors_per_minute']);
        $this->assertIsArray($stats['current_counts']);
        $this->assertSame(1, $stats['total_active_windows']);
    }
}
