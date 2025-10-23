<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

final class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_read_requests_are_limited_per_ip_and_logged(): void
    {
        $originalConfig = config('security.rate_limiting.api.read');

        config([
            // Allow plenty of room for authenticated requests while clamping IP traffic tightly.
            'security.rate_limiting.api.read.per_ip'   => 1,
            'security.rate_limiting.api.read.per_user' => 100,
        ]);

        RateLimiter::clear('ip:127.0.0.1|api.read');

        Log::spy();

        $this->getJson(route('api.v1.health'))->assertOk();

        $secondResponse = $this->getJson(route('api.v1.health'));
        $secondResponse->assertTooManyRequests();

        $correlationId = $secondResponse->headers->get('X-Correlation-ID');

        Log::shouldHaveReceived('warning')->once()->withArgs(function (string $message, array $context) use ($correlationId): bool {
            return $message === 'API request throttled.'
                && ($context['correlation_id'] ?? null) === $correlationId
                && ($context['rate_limiter'] ?? null) === 'api.read.ip'
                && ($context['rate_limit_key'] ?? null) === 'ip:127.0.0.1|api.read';
        });

        RateLimiter::clear('ip:127.0.0.1|api.read');
        config(['security.rate_limiting.api.read' => $originalConfig]);
    }

    public function test_read_requests_are_limited_per_authenticated_user(): void
    {
        $originalConfig = config('security.rate_limiting.api.read');

        config([
            // Force the per-user limiter to a single request so the follow-up call is throttled.
            'security.rate_limiting.api.read.per_ip'   => 100,
            'security.rate_limiting.api.read.per_user' => 1,
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        RateLimiter::clear('user:' . $user->id . '|api.read');
        RateLimiter::clear('ip:127.0.0.1|api.read');

        $this->getJson(route('api.v1.health'))->assertOk();
        $secondResponse = $this->getJson(route('api.v1.health'));
        $secondResponse->assertTooManyRequests();

        RateLimiter::clear('user:' . $user->id . '|api.read');
        RateLimiter::clear('ip:127.0.0.1|api.read');
        config(['security.rate_limiting.api.read' => $originalConfig]);
    }
}
