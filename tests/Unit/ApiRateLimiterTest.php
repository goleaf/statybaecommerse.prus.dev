<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Providers\ApiServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use PHPUnit\Framework\TestCase;

final class ApiRateLimiterTest extends TestCase
{
    public function testResolveLimitReturnsExpectedDecayAndMaxAttempts(): void
    {
        $provider = (new \ReflectionClass(ApiServiceProvider::class))->newInstanceWithoutConstructor();

        // Reflecting over the provider allows us to exercise the internal limit resolution logic without altering visibility.
        $method = new \ReflectionMethod(ApiServiceProvider::class, 'resolveLimit');
        $method->setAccessible(true);

        /** @var array<int, Limit> $limits */
        $limits = $method->invoke($provider, ['minute' => 10, 'hour' => 100], 'test-key');

        $this->assertIsArray($limits);
        $this->assertCount(2, $limits);

        $minuteLimit = $limits[0];
        $this->assertInstanceOf(Limit::class, $minuteLimit);
        $this->assertSame(60, $minuteLimit->decaySeconds);
        $this->assertSame(10, $minuteLimit->maxAttempts);
        $this->assertSame('test-key', $minuteLimit->key);

        $hourLimit = $limits[1];
        $this->assertInstanceOf(Limit::class, $hourLimit);
        $this->assertSame(3600, $hourLimit->decaySeconds);
        $this->assertSame(100, $hourLimit->maxAttempts);
        $this->assertSame('test-key', $hourLimit->key);
    }
}
