<?php declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Feature\Api\Concerns\UsesApiRateLimitSchema;
use Tests\Feature\TestCase as FeatureTestCase;

abstract class RateLimitTestCase extends FeatureTestCase
{
    use UsesApiRateLimitSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRateLimitSchema();
    }

    protected function tearDown(): void
    {
        $this->tearDownRateLimitSchema();

        parent::tearDown();
    }

    protected function clearRateLimit(User $user, string $suffix = '', string $limiter = 'api.default'): void
    {
        RateLimiter::clear($this->hashedKey($limiter, $this->rateLimitKeyForUser($user, $suffix, $limiter)));
        RateLimiter::clear($this->hashedKey($limiter, $this->rateLimitKeyForIp($suffix, $limiter)));
        RateLimiter::clear($this->hashedKey($limiter, $this->rateLimitKeyForLoopbackIpv6($suffix, $limiter)));
    }

    protected function rateLimitKeyForUser(User $user, string $suffix = '', ?string $bucket = null): string
    {
        $keySuffix = $suffix !== '' ? $suffix : ($bucket ?? 'api.default');

        return sprintf('user:%s|%s', $user->getAuthIdentifier(), $keySuffix);
    }

    protected function rateLimitKeyForIp(string $suffix = '', ?string $bucket = null): string
    {
        $keySuffix = $suffix !== '' ? $suffix : ($bucket ?? 'api.default');

        return sprintf('ip:127.0.0.1|%s', $keySuffix);
    }

    protected function saturateRateLimit(User $user, string $suffix = '', string $limiter = 'api.default'): void
    {
        $userKey = $this->hashedKey($limiter, $this->rateLimitKeyForUser($user, $suffix, $limiter));
        $ipKey = $this->hashedKey($limiter, $this->rateLimitKeyForIp($suffix, $limiter));
        $fallbackIpKey = $this->hashedKey($limiter, $this->rateLimitKeyForLoopbackIpv6($suffix, $limiter));

        RateLimiter::hit($userKey);
        RateLimiter::hit($userKey);
        RateLimiter::hit($ipKey);
        RateLimiter::hit($ipKey);
        RateLimiter::hit($fallbackIpKey);
        RateLimiter::hit($fallbackIpKey);
    }

    private function hashedKey(string $limiter, string $key): string
    {
        return md5($limiter.$key);
    }

    private function rateLimitKeyForLoopbackIpv6(string $suffix = '', ?string $bucket = null): string
    {
        $keySuffix = $suffix !== '' ? $suffix : ($bucket ?? 'api.default');

        return sprintf('ip:::1|%s', $keySuffix);
    }
}
