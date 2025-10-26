<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\ApiKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

final class PartnerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_request_succeeds_with_valid_key_and_scope(): void
    {
        $credentials = ApiKey::generateCredentials();

        $apiKey = ApiKey::factory()->create([
            'key'        => $credentials['hashed'],
            'scopes'     => ['orders.read', 'analytics.read'],
            'rate_limit' => 5,
        ]);

        $response = $this
            ->withHeader(config('services.partner_api.header'), $credentials['plain_text'])
            ->getJson(route('api.partner.orders.index'));

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'orders' => [],
                ],
            ]);

        $this->assertNotNull($apiKey->fresh()->last_used_at);
    }

    public function test_missing_api_key_returns_unauthorized(): void
    {
        $response = $this->getJson(route('api.partner.orders.index'));

        $response->assertUnauthorized()
            ->assertJson([
                'message' => 'Missing partner API key.',
            ]);
    }

    public function test_insufficient_scope_returns_forbidden(): void
    {
        $credentials = ApiKey::generateCredentials();

        ApiKey::factory()->create([
            'key'        => $credentials['hashed'],
            'scopes'     => ['analytics.read'],
            'rate_limit' => 5,
        ]);

        $response = $this
            ->withHeader(config('services.partner_api.header'), $credentials['plain_text'])
            ->getJson(route('api.partner.orders.index'));

        $response->assertForbidden()
            ->assertJson([
                'message' => 'Insufficient partner API permissions.',
            ]);
    }

    public function test_partner_api_calls_are_rate_limited(): void
    {
        $credentials = ApiKey::generateCredentials();

        $apiKey = ApiKey::factory()->create([
            'key'        => $credentials['hashed'],
            'scopes'     => ['orders.read'],
            'rate_limit' => 1,
        ]);

        RateLimiter::clear($apiKey->rateLimiterKey());

        $headerName = config('services.partner_api.header');

        $firstResponse = $this
            ->withHeader($headerName, $credentials['plain_text'])
            ->getJson(route('api.partner.orders.index'));

        $firstResponse->assertOk()
            // Ensure we expose rate limit metadata for successful partner requests.
            ->assertHeader('X-RateLimit-Reset');

        $secondResponse = $this
            ->withHeader($headerName, $credentials['plain_text'])
            ->getJson(route('api.partner.orders.index'));

        $secondResponse->assertTooManyRequests()
            ->assertJson([
                'message' => 'Partner API rate limit exceeded.',
            ])
            // Confirm rate limit error responses surface both retry guidance and reset timing.
            ->assertHeader('Retry-After')
            ->assertHeader('X-RateLimit-Reset');
    }
}
