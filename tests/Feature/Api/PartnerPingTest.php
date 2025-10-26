<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\ApiKey;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

final class PartnerPingTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_ping_surfaces_runtime_metadata(): void
    {
        // Provision a partner key so we can authenticate against the ping endpoint.
        $credentials = ApiKey::generateCredentials();

        ApiKey::factory()->create([
            'key'        => $credentials['hashed'],
            'scopes'     => ['orders.read'],
            'rate_limit' => 10,
        ]);

        // Execute the ping request to confirm the integration contract.
        $response = $this
            ->withHeader(config('services.partner_api.header'), $credentials['plain_text'])
            ->getJson(route('api.partner.ping'));

        $response->assertOk()
            // Validate we propagate rate limiting metadata for observability dashboards.
            ->assertHeader('X-RateLimit-Reset')
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('data.status', 'ok')
                ->where('data.message', 'Partner API is available.')
                ->where('data.environment', app()->environment())
                ->where('data.version', config('contracts.version', 'v1'))
                ->has('data.timestamp')
                ->where('meta.scopes', ['orders.read'])
                ->where('meta.required_scopes', [])
                ->has('meta.correlation_id')
            );

        // Parse the timestamp to ensure the value is a valid ISO-8601 string.
        $timestamp = $response->json('data.timestamp');
        $this->assertIsString($timestamp);
        $this->assertInstanceOf(CarbonImmutable::class, CarbonImmutable::parse($timestamp));

        // Correlation identifiers must be present so support can trace partner incidents.
        $this->assertIsString($response->json('meta.correlation_id'));
        $this->assertNotSame('', $response->json('meta.correlation_id'));
    }
}
