<?php

declare(strict_types=1);

namespace Tests\Feature\PartnerApi;

use App\Models\ApiKey;
use App\Models\Order;
use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class OrderSummaryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time to keep assertions deterministic across environments.
        Carbon::setTestNow(now()->setMicro(0));

        // Normalise partner API rate limiting so individual tests do not exhaust the bucket.
        config([
            'services.partner_api.rate_limit.max_attempts'  => 60,
            'services.partner_api.rate_limit.decay_seconds' => 60,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_returns_partner_scoped_summary_metrics(): void
    {
        // Create a partner and bind an API key to scope downstream queries.
        $partner = Partner::factory()->create();

        $apiKey = ApiKey::factory()->create([
            'partner_id'  => $partner->getKey(),
            'scopes'      => ['orders.read'],
            'permissions' => null,
            'rate_limit'  => null,
        ]);

        // Seed two recent orders for the authenticated partner with deterministic totals.
        Order::factory()->create([
            'partner_id' => $partner->getKey(),
            'status'     => 'completed',
            'payment_status' => 'paid',
            'total'      => 150.00,
            'currency'   => 'EUR',
            'created_at' => now()->subDays(2),
        ]);

        Order::factory()->create([
            'partner_id' => $partner->getKey(),
            'status'     => 'pending',
            'payment_status' => 'pending',
            'total'      => 50.00,
            'currency'   => 'USD',
            'created_at' => now()->subDay(),
        ]);

        // Insert a control order that should be excluded because it belongs to another partner.
        Order::factory()->create([
            'partner_id' => Partner::factory()->create()->getKey(),
            'status'     => 'completed',
            'payment_status' => 'paid',
            'total'      => 999.00,
            'currency'   => 'EUR',
            'created_at' => now()->subDay(),
        ]);

        $response = $this->withHeaders([
            'X-Partner-Key' => $apiKey->getAttribute('key'),
        ])->getJson('/api/partner/orders/summary?from=' . now()->subDays(7)->toIso8601String() . '&to=' . now()->toIso8601String());

        // Ensure the summary reflects only the partner's orders.
        $response->assertOk();
        $response->assertJsonPath('data.totals.orders', 2);
        $response->assertJsonPath('data.totals.revenue', 200.0);
        $response->assertJsonPath('data.totals.average_order_value', 100.0);

        // Validate that the breakdowns include the expected currencies and statuses.
        $response->assertJsonFragment(['currency' => 'EUR', 'orders' => 1, 'revenue' => 150.0]);
        $response->assertJsonFragment(['currency' => 'USD', 'orders' => 1, 'revenue' => 50.0]);
        $response->assertJsonFragment(['status' => 'completed', 'count' => 1]);
        $response->assertJsonFragment(['status' => 'pending', 'count' => 1]);

        // Confirm the scopes metadata is surfaced for the integration client.
        $response->assertJsonPath('meta.scopes', ['orders.read']);
    }

    public function test_it_defaults_to_last_thirty_days_when_no_range_is_provided(): void
    {
        $partner = Partner::factory()->create();

        $apiKey = ApiKey::factory()->create([
            'partner_id'  => $partner->getKey(),
            'scopes'      => ['orders.read'],
            'permissions' => null,
            'rate_limit'  => null,
        ]);

        // Recent order should be counted in the summary window.
        Order::factory()->create([
            'partner_id' => $partner->getKey(),
            'status'     => 'processing',
            'payment_status' => 'paid',
            'total'      => 80.00,
            'currency'   => 'EUR',
            'created_at' => now()->subDays(5),
        ]);

        // Historical order outside the 30-day window must be ignored by default.
        Order::factory()->create([
            'partner_id' => $partner->getKey(),
            'status'     => 'completed',
            'payment_status' => 'paid',
            'total'      => 120.00,
            'currency'   => 'EUR',
            'created_at' => now()->subDays(60),
        ]);

        $response = $this->withHeaders([
            'X-Partner-Key' => $apiKey->getAttribute('key'),
        ])->getJson('/api/partner/orders/summary');

        $response->assertOk();

        // Only the recent order should contribute to the totals.
        $response->assertJsonPath('data.totals.orders', 1);
        $response->assertJsonPath('data.totals.revenue', 80.0);
        $response->assertJsonPath('data.totals.average_order_value', 80.0);

        $response->assertJsonFragment(['status' => 'processing', 'count' => 1]);
        $response->assertJsonFragment(['status' => 'completed', 'count' => 0]);
    }
}
