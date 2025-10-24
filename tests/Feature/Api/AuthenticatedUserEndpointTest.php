<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use App\Support\ErrorCodes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class AuthenticatedUserEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_authentication(): void
    {
        $response = $this->getJson(route('api.v1.user.show'));

        $response->assertUnauthorized()
            ->assertJsonPath('error.code', ErrorCodes::UNAUTHORIZED);
    }

    public function test_requires_profile_read_ability(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['notifications.read']);

        $response = $this->getJson(route('api.v1.user.show'));

        $response->assertForbidden()
            ->assertJsonPath('error.code', ErrorCodes::FORBIDDEN)
            ->assertJsonPath('error.context.reason', 'This action requires the [profile.read] ability.');
    }

    public function test_returns_contract_payload_for_authorized_user(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['profile.read']);

        $response = $this->getJson(route('api.v1.user.show'));

        $response->assertOk()
            ->assertJsonPath('contract', 'user')
            ->assertJsonPath('version', 'v1')
            ->assertJsonPath('data.id', $user->getKey());
    }

    public function test_endpoint_is_rate_limited(): void
    {
        $user = User::factory()->create();

        config(['security.rate_limiting.api.profile' => 1]);
        RateLimiter::clear('user:' . $user->id . '|profile');

        Sanctum::actingAs($user, ['profile.read']);

        $first = $this->getJson(route('api.v1.user.show'));
        $first->assertOk();

        $second = $this->getJson(route('api.v1.user.show'));
        $second->assertStatus(429)
            ->assertJsonPath('status', 429);

        RateLimiter::clear('user:' . $user->id . '|profile');
    }
}
