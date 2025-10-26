<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\OrderStatus;
use App\Http\Middleware\TestingLegalResourceStub;
use App\Models\Country;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * OrderLifecycleTest
 *
 * Feature coverage for the dedicated order lifecycle API endpoints.
 */
final class OrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private bool $originalAuthorizationBypass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalAuthorizationBypass = (bool) config('authorization.testing.skip_checks', true);

        $this->withoutMiddleware([
            TestingLegalResourceStub::class,
        ]);
    }

    protected function tearDown(): void
    {
        config()->set('authorization.testing.skip_checks', $this->originalAuthorizationBypass);

        parent::tearDown();
    }

    public function test_index_requires_authorization(): void
    {
        config()->set('authorization.testing.skip_checks', false);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/orders');

        $response->assertForbidden();
    }

    public function test_cancel_only_allowed_before_fulfilment(): void
    {
        config()->set('authorization.testing.skip_checks', false);

        $user = User::factory()->create();
        $this->seedOrderPermissions($user, ['orders.viewAny', 'orders.view', 'orders.update', 'orders.cancel']);
        Sanctum::actingAs($user);

        $country = Country::factory()->create();

        $processingOrder = Order::factory()->processing()->for($user)->state(['country_id' => $country->getKey()])->create();
        OrderItem::factory()->forOrder($processingOrder)->create();

        // Attempting to cancel a processing order should fail with an Unprocessable Entity response.
        $blockedResponse = $this->postJson("/api/v1/orders/{$processingOrder->getKey()}/cancel");
        $blockedResponse->assertStatus(422);
        $processingOrder->refresh();
        $this->assertSame(
            OrderStatus::PROCESSING->value,
            $processingOrder->status instanceof OrderStatus ? $processingOrder->status->value : (string) $processingOrder->status,
        );

        $pendingOrder = Order::factory()->pending()->for($user)->state(['country_id' => $country->getKey()])->create();
        OrderItem::factory()->forOrder($pendingOrder)->create();

        $response = $this->postJson("/api/v1/orders/{$pendingOrder->getKey()}/cancel");
        $response->assertNoContent();

        $pendingOrder->refresh();
        $this->assertSame(
            OrderStatus::CANCELLED->value,
            $pendingOrder->status instanceof OrderStatus ? $pendingOrder->status->value : (string) $pendingOrder->status,
        );
    }

    public function test_index_pagination_is_capped(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->seedOrderPermissions($user, ['orders.viewAny', 'orders.view']);
        Sanctum::actingAs($user);

        $country = Country::factory()->create();

        Order::factory()
            ->count(60)
            ->for($user)
            ->state(['country_id' => $country->getKey()])
            ->create();

        $response = $this->getJson('/api/v1/orders?per_page=200');
        $response->assertOk();

        $payload = $response->json();
        $this->assertSame(50, (int) data_get($payload, 'meta.per_page'));
        $this->assertLessThanOrEqual(50, count($payload['data'] ?? []));
    }

    /**
     * Helper to seed the minimal set of permissions required for each scenario.
     *
     * @param array<int, string> $permissions
     */
    private function seedOrderPermissions(User $user, array $permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $user->givePermissionTo($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
