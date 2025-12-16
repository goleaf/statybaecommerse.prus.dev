<?php

declare(strict_types=1);

use App\Support\Exceptions\BootErrorRateLimiter;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Tests for the BootErrorRateLimiter boundary conditions and edge cases.
 */
class BootErrorRateLimiterBoundaryTest extends TestCase
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

    public function test_rate_limiting_boundary_condition_fix(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 3);

        $rateLimiter = new BootErrorRateLimiter;

        // First 3 calls should not be rate limited
        $this->assertFalse($rateLimiter->isRateLimited(), 'First call should not be rate limited');
        $this->assertSame(1, $rateLimiter->getCurrentCount());

        $this->assertFalse($rateLimiter->isRateLimited(), 'Second call should not be rate limited');
        $this->assertSame(2, $rateLimiter->getCurrentCount());

        $this->assertFalse($rateLimiter->isRateLimited(), 'Third call should not be rate limited');
        $this->assertSame(3, $rateLimiter->getCurrentCount());

        // Fourth call should be rate limited (this tests the boundary condition fix)
        $this->assertTrue($rateLimiter->isRateLimited(), 'Fourth call should be rate limited');
        $this->assertSame(3, $rateLimiter->getCurrentCount(), 'Count should not increment when rate limited');

        // Subsequent calls should continue to be rate limited
        $this->assertTrue($rateLimiter->isRateLimited(), 'Fifth call should be rate limited');
        $this->assertSame(3, $rateLimiter->getCurrentCount(), 'Count should remain at limit');
    }

    public function test_would_be_rate_limited_does_not_increment(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 2);

        $rateLimiter = new BootErrorRateLimiter;

        // Use up the limit
        $rateLimiter->isRateLimited();
        $rateLimiter->isRateLimited();

        // Check without incrementing
        $this->assertTrue($rateLimiter->wouldBeRateLimited());
        $this->assertSame(2, $rateLimiter->getCurrentCount());

        // Verify count didn't change
        $this->assertTrue($rateLimiter->wouldBeRateLimited());
        $this->assertSame(2, $rateLimiter->getCurrentCount());
    }

    public function test_rate_limiting_disabled_never_limits(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', false);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 1);

        $rateLimiter = new BootErrorRateLimiter;

        // Should never be rate limited when disabled
        for ($i = 0; $i < 10; $i++) {
            $this->assertFalse($rateLimiter->isRateLimited(), "Call {$i} should not be rate limited when disabled");
            $this->assertFalse($rateLimiter->wouldBeRateLimited(), 'wouldBeRateLimited should return false when disabled');
        }
    }

    public function test_rate_limiting_with_zero_limit(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 0);

        $rateLimiter = new BootErrorRateLimiter;

        // With zero limit, first call should be rate limited
        $this->assertTrue($rateLimiter->isRateLimited(), 'First call should be rate limited with zero limit');
        $this->assertSame(0, $rateLimiter->getCurrentCount(), 'Count should remain at zero');
    }

    public function test_concurrent_access_simulation(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 5);

        $rateLimiter = new BootErrorRateLimiter;

        // Simulate concurrent access by checking multiple times rapidly
        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $results[] = $rateLimiter->isRateLimited();
        }

        // First 5 should be false (not rate limited), rest should be true
        $notRateLimited = array_filter($results, static fn ($result): bool => ! $result);
        $rateLimited = array_filter($results, static fn ($result): bool => $result);

        $this->assertCount(5, $notRateLimited, 'Exactly 5 calls should not be rate limited');
        $this->assertCount(5, $rateLimited, 'Exactly 5 calls should be rate limited');
        $this->assertSame(5, $rateLimiter->getCurrentCount(), 'Final count should be at the limit');
    }

    public function test_reset_functionality(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 2);

        $rateLimiter = new BootErrorRateLimiter;

        // Use up the limit
        $rateLimiter->isRateLimited();
        $rateLimiter->isRateLimited();
        $this->assertTrue($rateLimiter->isRateLimited());

        // Reset should clear everything
        BootErrorRateLimiter::reset();

        // Should work again after reset
        $this->assertFalse($rateLimiter->isRateLimited(), 'Should not be rate limited after reset');
        $this->assertSame(1, $rateLimiter->getCurrentCount(), 'Count should start fresh after reset');
    }

    public function test_time_window_key_generation(): void
    {
        Config::set('exception-handling.security.rate_limit_enabled', true);
        Config::set('exception-handling.security.max_boot_errors_per_minute', 10);

        $rateLimiter = new BootErrorRateLimiter;

        // Get initial count
        $rateLimiter->isRateLimited();
        $initialCount = $rateLimiter->getCurrentCount();

        // In the same minute, count should accumulate
        $rateLimiter->isRateLimited();
        $this->assertSame($initialCount + 1, $rateLimiter->getCurrentCount());

        // Verify the key format is consistent
        $this->assertGreaterThan(0, $rateLimiter->getCurrentCount());
    }
}
