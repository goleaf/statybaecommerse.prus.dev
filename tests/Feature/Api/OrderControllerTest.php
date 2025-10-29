<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\AdminAuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    private bool $previousSkipChecks = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousSkipChecks = (bool) config('authorization.testing.skip_checks', true);
        config(['authorization.testing.skip_checks' => false]);

        $this->seed(AdminAuthorizationSeeder::class);
    }

    protected function tearDown(): void
    {
        config(['authorization.testing.skip_checks' => $this->previousSkipChecks]);

        parent::tearDown();
    }

    public function test_customer_can_view_their_own_order(): void
    {
        // Create a customer and a related order to verify the happy path.
        $customer = User::factory()->create();
        $order = Order::factory()->confirmed()->create([
            'user_id' => $customer->getKey(),
        ]);

        // Authenticate as the owning customer before calling the endpoint.
        $this->actingAs($customer);

        $response = $this->getJson(route('api.orders.show', ['order' => $order->number]));

        $response->assertOk()
            ->assertJsonPath('data.order.number', $order->number);
    }

    public function test_authorized_support_user_can_view_foreign_order(): void
    {
        // Confirm that users with the explicit permission can view any order.
        $orderOwner = User::factory()->create();
        $foreignOrder = Order::factory()->processing()->create([
            'user_id' => $orderOwner->getKey(),
        ]);

        // Acting as a second user ensures we exercise the authorization policy.
        $supportUser = User::factory()->create();
        $supportUser->assignRole('support');
        $this->actingAs($supportUser);

        $response = $this->getJson(route('api.orders.show', ['order' => $foreignOrder->number]));

        $response->assertOk()
            ->assertJsonPath('data.order.number', $foreignOrder->number);
    }

    public function test_user_without_permission_cannot_view_foreign_order(): void
    {
        $orderOwner = User::factory()->create();
        $restrictedOrder = Order::factory()->confirmed()->create([
            'user_id' => $orderOwner->getKey(),
        ]);

        // Authenticate as a different user who lacks the required permission.
        $anotherUser = User::factory()->create();
        $this->actingAs($anotherUser);

        $response = $this->getJson(route('api.orders.show', ['order' => $restrictedOrder->number]));

        $response->assertForbidden();
    }

    public function test_customer_can_view_completed_order_history(): void
    {
        // Create a customer and a completed order so we can verify legacy
        // lifecycle states remain accessible through the public API.
        $customer = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $customer->getKey(),
            'status'  => OrderStatus::COMPLETED->value,
        ]);

        // Authenticate as the owning customer before requesting the resource.
        $this->actingAs($customer);

        $response = $this->getJson(route('api.orders.show', ['order' => $order->number]));

        $response->assertOk()
            ->assertJsonPath('data.order.status.state', OrderStatus::COMPLETED->value);
    }
}
