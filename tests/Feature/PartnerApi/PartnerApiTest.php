<?php

declare(strict_types=1);

namespace Tests\Feature\PartnerApi;

use App\Http\Controllers\Api\Partner\InventoryController;
use App\Http\Controllers\Api\Partner\OrderSummaryController;
use App\Models\ApiKey;
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

    public function test_partner_api_allows_requests_with_valid_key(): void
    {
        $apiKey = ApiKey::factory()->create([
            'permissions' => ['inventory.read', 'orders.read'],
        ]);

        $response = $this->withHeaders([
            'X-Partner-Key' => $apiKey->key,
        ])->getJson('/partner/api/inventory');

        $response
            ->assertOk()
            ->assertJsonPath('data.inventory', []);

        $response->assertHeader('X-RateLimit-Limit', '3');
        $response->assertHeader('X-RateLimit-Remaining', '2');
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
