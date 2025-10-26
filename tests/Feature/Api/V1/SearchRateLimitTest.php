<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

final class SearchRateLimitTest extends TestCase
{
    public function test_search_endpoint_is_rate_limited(): void
    {
        $originalSecurityConfig = config('security.rate_limiting.api.search');
        $originalSimpleConfig = config('api.rate_limits.search');

        // Force a very small window so the test triggers the limiter quickly.
        config(['security.rate_limiting.api.search' => ['per_user' => null, 'per_ip' => 1]]);
        config(['api.rate_limits.search' => 1]);

        $limiter = 'api.search';
        $ipKey = 'ip:127.0.0.1|api.search';
        $hashedKey = md5($limiter . $ipKey);

        RateLimiter::clear($hashedKey);

        try {
            $this->getJson(route('api.v1.search', ['query' => 'hammer']))->assertOk();
            $this->getJson(route('api.v1.search', ['query' => 'hammer']))->assertStatus(429);
        } finally {
            config(['security.rate_limiting.api.search' => $originalSecurityConfig]);
            config(['api.rate_limits.search' => $originalSimpleConfig]);
            RateLimiter::clear($hashedKey);
        }
    }
}
