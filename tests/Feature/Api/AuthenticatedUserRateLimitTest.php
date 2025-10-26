<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\PreparesRateLimitTestDatabase;
use Tests\TestCase;

final class AuthenticatedUserRateLimitTest extends TestCase
{
    use PreparesRateLimitTestDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootRateLimitTestEnvironment();
    }

    public function test_authenticated_user_endpoint_is_rate_limited(): void
    {
        $user = User::factory()->create();

        $originalLimit = config('api.rate_limits.default');
        config(['api.rate_limits.default' => 1]);
        RateLimiter::clear('user:' . $user->getKey());

        Sanctum::actingAs($user, ['profile.read']);

        try {
            $this->getJson(route('api.v1.user.show'))->assertOk();
            $this->getJson(route('api.v1.user.show'))->assertStatus(429);
        } finally {
            config(['api.rate_limits.default' => $originalLimit]);
            RateLimiter::clear('user:' . $user->getKey());
        }
    }
}
