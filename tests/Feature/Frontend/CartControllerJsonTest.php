<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Http\Middleware\TestingLegalResourceStub;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CartControllerJsonTest extends TestCase
{
    use RefreshDatabase;

    private string $sessionCookieName;

    private string $sessionId;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable the testing legal stub middleware to allow JSON responses in the suite.
        $this->withoutMiddleware(TestingLegalResourceStub::class);

        // Bootstrap a deterministic session identifier to share between requests.
        $session = $this->app['session'];
        $session->start();
        $this->sessionCookieName = $session->getName();
        $this->sessionId = $session->getId();
    }

    public function test_guest_can_add_item_via_json(): void
    {
        // Arrange a product with deterministic pricing and stock to assert totals.
        $product = Product::factory()->create([
            'price'            => 25.0,
            'sale_price'       => null,
            'manage_stock'     => true,
            'stock_quantity'   => 10,
            'minimum_quantity' => 1,
        ]);

        // Act by sending a JSON add-to-cart request as a guest user.
        $response = $this->withCookie($this->sessionCookieName, $this->sessionId)->postJson(route('frontend.cart.add'), [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        // Assert the cart resource reflects the server-side totals.
        $response->assertCreated();
        $response->assertJsonPath('cart.item_count', 1);
        $response->assertJsonPath('cart.items.0.product_id', $product->id);
        $response->assertJsonPath('cart.totals.subtotal', 25);

        // Verify the persisted cart item belongs to the guest session (no user ID).
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity'   => 1,
            'user_id'    => null,
        ]);
    }

    public function test_authenticated_user_persists_user_id_on_cart_items(): void
    {
        // Arrange an authenticated user and an in-stock product.
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price'          => 19.99,
            'sale_price'     => null,
            'manage_stock'   => true,
            'stock_quantity' => 5,
        ]);

        // Act by adding an item while authenticated.
        $response = $this->actingAs($user)
            ->withCookie($this->sessionCookieName, $this->sessionId)
            ->postJson(route('frontend.cart.add'), [
                'product_id' => $product->id,
                'quantity'   => 2,
            ]);

        // Assert the totals account for the requested quantity and user association.
        $response->assertStatus(201);
        $response->assertJsonPath('cart.item_count', 2);
        $response->assertJsonPath('cart.totals.subtotal', 39.98);

        // Confirm the persisted record is tied to the authenticated user.
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity'   => 2,
            'user_id'    => $user->id,
        ]);
    }

    public function test_update_quantity_via_json_uses_server_pricing(): void
    {
        // Arrange an existing cart item created via the API.
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price'          => 15.5,
            'sale_price'     => null,
            'manage_stock'   => true,
            'stock_quantity' => 8,
        ]);

        $cartItemId = $this->actingAs($user)
            ->withCookie($this->sessionCookieName, $this->sessionId)
            ->postJson(route('frontend.cart.add'), [
                'product_id' => $product->id,
                'quantity'   => 1,
            ])->json('cart.items.0.id');

        $cartItem = CartItem::query()->findOrFail($cartItemId);

        // Act by increasing the quantity through the update endpoint.
        $response = $this->actingAs($user)
            ->withCookie($this->sessionCookieName, $this->sessionId)
            ->patchJson(route('frontend.cart.update', ['cartItem' => $cartItem->getKey()]), [
                'quantity' => 3,
            ]);

        // Assert the totals and unit price are recalculated server-side.
        $response->assertOk();
        $response->assertJsonPath('cart.items.0.quantity', 3);
        $response->assertJsonPath('cart.totals.subtotal', 46.5);

        // Ensure the persisted record reflects the updated quantity and total price.
        $this->assertDatabaseHas('cart_items', [
            'id'          => $cartItemId,
            'quantity'    => 3,
            'total_price' => 46.5,
        ]);
    }

    public function test_remove_item_via_json_returns_empty_cart(): void
    {
        // Arrange a cart item that will be removed using the API.
        $product = Product::factory()->create([
            'price'          => 12.0,
            'sale_price'     => null,
            'manage_stock'   => true,
            'stock_quantity' => 4,
        ]);

        $cartItemId = $this->withCookie($this->sessionCookieName, $this->sessionId)->postJson(route('frontend.cart.add'), [
            'product_id' => $product->id,
            'quantity'   => 1,
        ])->json('cart.items.0.id');

        // Act by removing the item.
        $response = $this->withCookie($this->sessionCookieName, $this->sessionId)
            ->deleteJson(route('frontend.cart.remove', ['cartItem' => $cartItemId]));

        // Assert the cart resource reflects an empty state.
        $response->assertOk();
        $response->assertJsonPath('cart.item_count', 0);
        $response->assertJsonPath('cart.totals.total', 0);

        // Confirm the database no longer stores the cart item.
        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItemId,
        ]);
    }

    public function test_oversell_attempt_returns_conflict(): void
    {
        // Arrange a product with limited stock to trigger the oversell guard.
        $product = Product::factory()->create([
            'price'          => 18.0,
            'sale_price'     => null,
            'manage_stock'   => true,
            'stock_quantity' => 2,
        ]);

        // Act by requesting a quantity greater than available inventory.
        $response = $this->withCookie($this->sessionCookieName, $this->sessionId)->postJson(route('frontend.cart.add'), [
            'product_id' => $product->id,
            'quantity'   => 5,
        ]);

        // Assert a conflict response that exposes the available stock and clamps the quantity.
        $response->assertStatus(409);
        $response->assertJsonPath('available_quantity', 2);
        $response->assertJsonPath('cart.items.0.quantity', 2);

        // Verify the persisted cart item quantity never exceeds available stock.
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);
    }
}
