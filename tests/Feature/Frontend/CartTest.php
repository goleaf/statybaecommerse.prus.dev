<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\CartItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;

final class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_clear_cart(): void
    {
        session()->start();
        $sessionId = session()->getId();
        $token = (string) csrf_token();

        session()->put('cart', [
            1 => [
                'id'         => 1,
                'product_id' => 1,
                'name'       => 'Guest product',
                'price'      => 19.99,
                'quantity'   => 2,
            ],
        ]);
        session()->put('cart_discount', 5);

        CartItem::factory()->guest()->create(['session_id' => $sessionId]);

        $response = $this
            ->withHeader('X-CSRF-TOKEN', $token)
            ->withSession([
                'cart'            => session('cart', []),
                'cart_discount'   => session('cart_discount'),
                'cart_session_id' => $sessionId,
            ])
            ->withCookie(session()->getName(), $sessionId)
            ->postJson(route('frontend.cart.clear'));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Cart cleared successfully.',
            ])
            ->assertJsonPath('cart.count', 0)
            ->assertJsonPath('cart.items', []);

        $this->assertSame([], session('cart', []));
        $this->assertFalse(session()->has('cart_discount'));
        $this->assertSame(0, \App\Models\CartItem::withoutGlobalScopes()->where('session_id', $sessionId)->count());
    }

    public function test_authenticated_user_can_clear_cart(): void
    {
        $user = User::factory()->create();

        session()->start();
        $sessionId = session()->getId();
        $token = (string) csrf_token();

        session()->put('cart', [
            1 => [
                'id'         => 1,
                'product_id' => 1,
                'name'       => 'User product',
                'price'      => 29.5,
                'quantity'   => 1,
            ],
        ]);

        CartItem::factory()->forUser($user)->forSession($sessionId)->create();

        $response = $this
            ->actingAs($user)
            ->withHeader('X-CSRF-TOKEN', $token)
            ->withSession([
                'cart'          => session('cart', []),
                'cart_discount' => session('cart_discount'),
            ])
            ->withCookie(session()->getName(), $sessionId)
            ->postJson(route('frontend.cart.clear'));

        $response
            ->assertOk()
            ->assertJsonPath('cart.count', 0)
            ->assertJsonPath('cart.total', 0);

        $this->assertSame([], session('cart', []));
        $this->assertDatabaseMissing('cart_items', ['session_id' => $sessionId]);
    }
}
