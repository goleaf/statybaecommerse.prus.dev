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
        RateLimiter::clear($this->hashedKey($limiter, $this->rateLimitKeyForUser($user, $suffix)));
        RateLimiter::clear($this->hashedKey($limiter, $this->rateLimitKeyForIp($suffix)));
        RateLimiter::clear($this->hashedKey($limiter, $this->rateLimitKeyForLoopbackIpv6($suffix)));
    }

    protected function rateLimitKeyForUser(User $user, string $suffix = ''): string
    {
        $baseKey = 'user:'.$user->getAuthIdentifier();

        return $suffix === '' ? $baseKey : $baseKey.'|'.$suffix;
    }

    protected function rateLimitKeyForIp(string $suffix = ''): string
    {
        $baseKey = 'ip:127.0.0.1';

        return $suffix === '' ? $baseKey : $baseKey.'|'.$suffix;
    }

    protected function saturateRateLimit(User $user, string $suffix = '', string $limiter = 'api.default'): void
    {
        $userKey = $this->hashedKey($limiter, $this->rateLimitKeyForUser($user, $suffix));
        $ipKey = $this->hashedKey($limiter, $this->rateLimitKeyForIp($suffix));
        $fallbackIpKey = $this->hashedKey($limiter, $this->rateLimitKeyForLoopbackIpv6($suffix));

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

    private function rateLimitKeyForLoopbackIpv6(string $suffix = ''): string
    {
        $baseKey = 'ip:::1';

        return $suffix === '' ? $baseKey : $baseKey.'|'.$suffix;
    }
}
