<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\ApiKey;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

final class PartnerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_request_returns_orders_for_associated_partner(): void
    {
        // Arrange a partner with two orders so we can assert the payload is populated.
        $partner = Partner::factory()->create();
        $credentials = ApiKey::generateCredentials();

        $apiKey = ApiKey::factory()->create([
            'key'        => $credentials['hashed'],
            'scopes'     => ['orders.read', 'analytics.read'],
            'rate_limit' => 5,
            'partner_id' => $partner->getKey(),
        ]);

        $recentOrder = Order::factory()->create([
            'number'         => 'ORD-1002',
            'partner_id'     => $partner->getKey(),
            'status'         => 'confirmed',
            'payment_status' => 'paid',
            'payment_state'  => 'paid',
        ]);
        OrderItem::factory()->forOrder($recentOrder)->count(2)->create([
            'unit_price' => 25.00,
            'price'      => 25.00,
            'total'      => 25.00,
        ]);

        $olderOrder = Order::factory()->create([
            'number'         => 'ORD-1001',
            'partner_id'     => $partner->getKey(),
            'status'         => 'processing',
            'payment_status' => 'pending',
            'payment_state'  => 'created',
            'created_at'     => now()->subDay(),
        ]);
        OrderItem::factory()->forOrder($olderOrder)->create([
            'unit_price' => 15.00,
            'price'      => 15.00,
            'total'      => 15.00,
        ]);

        // Act by calling the endpoint with the generated partner API key.
        $headerConfig = config('services.partner_api.header', 'X-Partner-Key');
        $headerName = is_string($headerConfig) && $headerConfig !== '' ? $headerConfig : 'X-Partner-Key';

        $response = $this
            ->withHeader($headerName, $credentials['plain_text'])
            ->getJson(route('api.partner.orders.index'));

        // Assert we receive an order contract populated with the partner's orders.
        $response->assertOk()
            ->assertJsonPath('contract', 'order')
            ->assertJsonPath('version', 'v1')
            ->assertJsonPath('meta.partner.id', $partner->getKey())
            ->assertJsonPath('meta.scopes', ['orders.read', 'analytics.read'])
            ->assertJsonPath('meta.pagination.total', 2)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.orders.0.number', 'ORD-1002')
            ->assertJsonPath('data.orders.0.items.0.unit_price', 25)
            ->assertJsonPath('data.orders.1.number', 'ORD-1001');

        $apiKey->refresh();
        $this->assertNotNull($apiKey->last_used_at);
    }

    public function test_missing_api_key_returns_unauthorized(): void
    {
        // Act without providing a partner header so the middleware rejects the request.
        $response = $this->getJson(route('api.partner.orders.index'));

        $response->assertUnauthorized()
            ->assertJson([
                'message' => 'Missing partner API key.',
            ]);
    }

    public function test_insufficient_scope_returns_forbidden(): void
    {
        // Arrange a key that does not have the orders.read scope.
        $credentials = ApiKey::generateCredentials();

        ApiKey::factory()->create([
            'key'        => $credentials['hashed'],
            'scopes'     => ['analytics.read'],
            'rate_limit' => 5,
        ]);

        // Act with the under-scoped key to verify the guard message.
        $headerConfig = config('services.partner_api.header', 'X-Partner-Key');
        $headerName = is_string($headerConfig) && $headerConfig !== '' ? $headerConfig : 'X-Partner-Key';

        $response = $this
            ->withHeader($headerName, $credentials['plain_text'])
            ->getJson(route('api.partner.orders.index'));

        $response->assertForbidden()
            ->assertJson([
                'message' => 'Insufficient partner API permissions.',
            ]);
    }

    public function test_partner_api_calls_are_rate_limited(): void
    {
        // Arrange an API key with a strict limit to trigger the throttle guard.
        $credentials = ApiKey::generateCredentials();

        $partner = Partner::factory()->create();

        $apiKey = ApiKey::factory()->create([
            'key'        => $credentials['hashed'],
            'scopes'     => ['orders.read'],
            'rate_limit' => 1,
            'partner_id' => $partner->getKey(),
        ]);

        RateLimiter::clear($apiKey->rateLimiterKey());

        $headerConfig = config('services.partner_api.header', 'X-Partner-Key');
        $headerName = is_string($headerConfig) && $headerConfig !== '' ? $headerConfig : 'X-Partner-Key';

        // First request should succeed.
        $firstResponse = $this
            ->withHeader($headerName, $credentials['plain_text'])
            ->getJson(route('api.partner.orders.index'));

        $firstResponse->assertOk();

        // Second request should fail with rate limit headers.
        $secondResponse = $this
            ->withHeader($headerName, $credentials['plain_text'])
            ->getJson(route('api.partner.orders.index'));

        $secondResponse->assertTooManyRequests()
            ->assertJson([
                'message' => 'Partner API rate limit exceeded.',
            ])
            ->assertHeader('Retry-After');
    }

    public function test_partner_request_rejects_keys_without_partner(): void
    {
        // Arrange a key that is not associated with a partner record.
        $credentials = ApiKey::generateCredentials();

        ApiKey::factory()->create([
            'key'        => $credentials['hashed'],
            'scopes'     => ['orders.read'],
            'rate_limit' => 5,
            'partner_id' => null,
        ]);

        // Act and assert the controller communicates the configuration issue.
        $headerConfig = config('services.partner_api.header', 'X-Partner-Key');
        $headerName = is_string($headerConfig) && $headerConfig !== '' ? $headerConfig : 'X-Partner-Key';

        $response = $this
            ->withHeader($headerName, $credentials['plain_text'])
            ->getJson(route('api.partner.orders.index'));

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Partner configuration is missing for this API key.',
            ]);
    }

    public function test_partner_request_supports_status_filtering(): void
    {
        // Arrange two partner orders with different lifecycle states.
        $partner = Partner::factory()->create();
        $credentials = ApiKey::generateCredentials();

        ApiKey::factory()->create([
            'key'        => $credentials['hashed'],
            'scopes'     => ['orders.read'],
            'rate_limit' => 5,
            'partner_id' => $partner->getKey(),
        ]);

        $shipped = Order::factory()->create([
            'number'         => 'ORD-2001',
            'partner_id'     => $partner->getKey(),
            'status'         => 'shipped',
            'payment_status' => 'paid',
        ]);
        OrderItem::factory()->forOrder($shipped)->create([
            'unit_price' => 30.00,
            'price'      => 30.00,
            'total'      => 30.00,
        ]);

        Order::factory()->create([
            'number'         => 'ORD-2002',
            'partner_id'     => $partner->getKey(),
            'status'         => 'pending',
            'payment_status' => 'pending',
        ]);

        // Act with a status filter to narrow the response payload.
        $headerConfig = config('services.partner_api.header', 'X-Partner-Key');
        $headerName = is_string($headerConfig) && $headerConfig !== '' ? $headerConfig : 'X-Partner-Key';

        $response = $this
            ->withHeader($headerName, $credentials['plain_text'])
            ->getJson(route('api.partner.orders.index', ['status' => 'shipped']));

        // Assert only the shipped order is returned alongside the recorded filter metadata.
        $response->assertOk()
            ->assertJsonCount(1, 'data.orders')
            ->assertJsonPath('data.orders.0.number', 'ORD-2001')
            ->assertJsonPath('data.orders.0.status.state', 'shipped')
            ->assertJsonPath('meta.filters.status', 'shipped');
    }
}
