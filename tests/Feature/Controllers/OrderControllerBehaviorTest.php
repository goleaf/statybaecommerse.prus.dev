<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Http\Controllers\OrderController as BaseOrderController;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class OrderControllerBehaviorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Register a lightweight route that exercises the shared base controller
        // helpers. Using an inline controller keeps the test focused and avoids
        // touching production routing tables.
        Route::middleware('web')->get('/testing/orders/{orderIdentifier}', TestOrderController::class)
            ->name('testing.orders.show');

        // Refresh the router caches so the route helper immediately resolves
        // the dynamically registered test route.
        $router = app('router');
        $router->getRoutes()->refreshNameLookups();
        $router->getRoutes()->refreshActionLookups();
    }

    public function test_authenticated_customer_can_view_own_order(): void
    {
        // Create a customer with an order that should remain visible.
        $customer = User::factory()->create();
        $order = Order::factory()->confirmed()->create([
            'user_id' => $customer->getKey(),
        ]);

        // Acting as the owning customer confirms the authorization bridge allows
        // legitimate access.
        $this->actingAs($customer);

        $response = $this->getJson(route('testing.orders.show', ['orderIdentifier' => $order->number]));

        $response->assertOk()
            ->assertJsonPath('data.order.number', $order->number);
    }

    public function test_numeric_identifier_resolves_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->processing()->create([
            'user_id' => $user->getKey(),
        ]);

        $this->actingAs($user);

        // Pass the primary key to ensure legacy ID based links continue working.
        $response = $this->getJson(route('testing.orders.show', ['orderIdentifier' => (string) $order->getKey()]));

        $response->assertOk()
            ->assertJsonPath('data.order.id', $order->getKey());
    }

    public function test_non_viewable_status_returns_not_found(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->cancelled()->create([
            'user_id' => $user->getKey(),
        ]);

        $this->actingAs($user);

        // Cancelled orders should surface as 404 to prevent leaking sensitive
        // details or revived sessions.
        $response = $this->getJson(route('testing.orders.show', ['orderIdentifier' => $order->number]));

        $response->assertNotFound();
    }

    public function test_foreign_order_without_permission_is_forbidden(): void
    {
        // Disable the testing bypass so the authorization matrix mirrors
        // production behaviour for this assertion.
        $originalSkipSetting = config('authorization.testing.skip_checks');
        config()->set('authorization.testing.skip_checks', false);

        try {
            $owner = User::factory()->create();
            $foreignOrder = Order::factory()->confirmed()->create([
                'user_id' => $owner->getKey(),
            ]);

            $otherUser = User::factory()->create();
            $this->actingAs($otherUser);

            $response = $this->getJson(route('testing.orders.show', ['orderIdentifier' => $foreignOrder->number]));

            $response->assertForbidden();
        } finally {
            // Restore the default bypass configuration for any following tests.
            config()->set('authorization.testing.skip_checks', $originalSkipSetting);
        }
    }

    public function test_guest_requests_require_authentication(): void
    {
        $order = Order::factory()->confirmed()->create();

        $response = $this->getJson(route('testing.orders.show', ['orderIdentifier' => $order->number]));

        $response->assertUnauthorized();
    }
}

/**
 * Test-specific controller exercising the shared order helpers.
 */
final class TestOrderController extends BaseOrderController
{
    /**
     * Handle the incoming request and return a compact JSON payload.
     */
    public function __invoke(Request $request, string $orderIdentifier): JsonResponse
    {
        // Resolve the order using the shared helper so number/ID lookups remain
        // production-accurate within the test.
        $order = $this->resolveOrderForRequest($request, $orderIdentifier);

        // Apply the consolidated authorization guard rails.
        $this->authorizeOrderView($request, $order);

        return response()->json([
            'data' => [
                'order' => [
                    'id'     => $order->getKey(),
                    'number' => $order->number,
                ],
            ],
        ]);
    }
}
