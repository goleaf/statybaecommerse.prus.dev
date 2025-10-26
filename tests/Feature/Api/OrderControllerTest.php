<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

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
        $this->actingAs($supportUser);

        $response = $this->getJson(route('api.orders.show', ['order' => $foreignOrder->number]));

        $response->assertOk()
            ->assertJsonPath('data.order.number', $foreignOrder->number);
    }

    public function test_user_without_permission_cannot_view_foreign_order(): void
    {
        // Disable the testing bypass so the authorization matrix behaves as production.
        config(['authorization.testing.skip_checks' => false]);

        try {
            $orderOwner = User::factory()->create();
            $restrictedOrder = Order::factory()->confirmed()->create([
                'user_id' => $orderOwner->getKey(),
            ]);

            // Authenticate as a different user who lacks the required permission.
            $anotherUser = User::factory()->create();
            $this->actingAs($anotherUser);

            $response = $this->getJson(route('api.orders.show', ['order' => $restrictedOrder->number]));

            $response->assertForbidden();
        } finally {
            // Restore the default configuration to avoid bleeding into later tests.
            config(['authorization.testing.skip_checks' => true]);
        }
    }
}
