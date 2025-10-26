<?php

declare(strict_types=1);

namespace Tests\Feature\PartnerApi;

use App\Http\Controllers\Api\Partner\InventoryController;
use App\Http\Controllers\Api\Partner\OrderSummaryController;
use App\Models\ApiKey;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class PartnerApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.partner_api.rate_limit.max_attempts'  => 3,
            'services.partner_api.rate_limit.decay_seconds' => 60,
        ]);

        Route::middleware('api')
            ->prefix('partner/api')
            ->group(function (): void {
                Route::get('/inventory', InventoryController::class)
                    ->middleware(['partner.api', 'partner.api.rate_limit', 'partner.api.scope:inventory.read']);

                Route::get('/orders', OrderSummaryController::class)
                    ->middleware(['partner.api', 'partner.api.rate_limit', 'partner.api.scope:orders.read']);
            });
    }

    public function test_inventory_endpoint_returns_summary_payload(): void
    {
        $apiKey = ApiKey::factory()->create([
            'permissions' => ['inventory.read', 'orders.read'],
        ]);

        // Seed products with a variety of inventory states to verify aggregation output.
        $inStock = Product::factory()->create([
            'manage_stock'        => true,
            'stock_quantity'      => 12,
            'low_stock_threshold' => 5,
        ]);

        $lowStock = Product::factory()->create([
            'manage_stock'        => true,
            'stock_quantity'      => 3,
            'low_stock_threshold' => 5,
        ]);

        $outOfStock = Product::factory()->create([
            'manage_stock'        => true,
            'stock_quantity'      => 0,
            'low_stock_threshold' => 2,
        ]);

        $notTracked = Product::factory()->create([
            'manage_stock' => false,
        ]);

        $response = $this->withHeaders([
            'X-Partner-Key' => $apiKey->key,
        ])->getJson('/partner/api/inventory');

        $response->assertOk();

        $response->assertJsonPath('data.inventory.summary.total_products', 4);
        $response->assertJsonPath('data.inventory.summary.tracked_products', 3);
        $response->assertJsonPath('data.inventory.summary.in_stock', 1);
        $response->assertJsonPath('data.inventory.summary.low_stock', 1);
        $response->assertJsonPath('data.inventory.summary.out_of_stock', 1);
        $response->assertJsonPath('data.inventory.summary.not_tracked', 1);

        $lowStockPayload = $response->json('data.inventory.low_stock');
        $this->assertCount(1, $lowStockPayload);
        $this->assertSame($lowStock->getKey(), $lowStockPayload[0]['id']);
        $this->assertSame(3, $lowStockPayload[0]['inventory']['stock_quantity']);
        $this->assertTrue($lowStockPayload[0]['inventory']['is_low_stock']);

        $outOfStockPayload = $response->json('data.inventory.out_of_stock');
        $this->assertCount(1, $outOfStockPayload);
        $this->assertSame($outOfStock->getKey(), $outOfStockPayload[0]['id']);
        $this->assertTrue($outOfStockPayload[0]['inventory']['is_out_of_stock']);

        $response->assertHeader('X-RateLimit-Limit', '3');
        $response->assertHeader('X-RateLimit-Remaining', '2');
    }

    public function test_inventory_endpoint_respects_limit_parameter(): void
    {
        $apiKey = ApiKey::factory()->create([
            'permissions' => ['inventory.read'],
        ]);

        // Generate multiple low stock products to exercise the limiter behaviour.
        Product::factory()->count(3)->create([
            'manage_stock'        => true,
            'stock_quantity'      => 2,
            'low_stock_threshold' => 5,
        ]);

        $response = $this->withHeaders([
            'X-Partner-Key' => $apiKey->key,
        ])->getJson('/partner/api/inventory?limit=1');

        $response->assertOk();
        $this->assertCount(1, $response->json('data.inventory.low_stock'));
        $this->assertCount(0, $response->json('data.inventory.out_of_stock'));
    }

    public function test_partner_api_rejects_missing_or_invalid_keys(): void
    {
        $this->getJson('/partner/api/inventory')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthorized.']);

        $apiKey = ApiKey::factory()->create([
            'permissions' => ['inventory.read'],
        ]);

        $this->withHeaders([
            'X-Partner-Key' => 'invalid-' . $apiKey->key,
        ])->getJson('/partner/api/inventory')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthorized.']);
    }

    public function test_partner_api_rate_limit_exhaustion_returns_429(): void
    {
        RateLimiter::fake();

        config([
            'services.partner_api.rate_limit.max_attempts'  => 1,
            'services.partner_api.rate_limit.decay_seconds' => 60,
        ]);

        $apiKey = ApiKey::factory()->create([
            'permissions' => ['inventory.read'],
        ]);

        $firstResponse = $this->withHeaders([
            'X-Partner-Key' => $apiKey->key,
        ])->getJson('/partner/api/inventory');

        $firstResponse->assertOk();
        $firstResponse->assertHeader('X-RateLimit-Remaining', '0');

        $secondResponse = $this->withHeaders([
            'X-Partner-Key' => $apiKey->key,
        ])->getJson('/partner/api/inventory');

        $secondResponse->assertStatus(429);
        $secondResponse->assertHeader('X-RateLimit-Limit', '1');
        $secondResponse->assertHeader('X-RateLimit-Remaining', '0');
    }

    public function test_partner_api_requires_expected_scope(): void
    {
        $apiKey = ApiKey::factory()->create([
            'permissions' => ['inventory.read'],
        ]);

        $response = $this->withHeaders([
            'X-Partner-Key' => $apiKey->key,
        ])->getJson('/partner/api/orders');

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Forbidden.']);
        $response->assertHeader('X-RateLimit-Limit', '3');
        $response->assertHeader('X-RateLimit-Remaining', '2');
    }
}
