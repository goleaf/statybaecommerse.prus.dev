<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\CartItem;
use App\Models\User;
use App\Services\Cart\CartLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CartLifecycleServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_clears_all_carts_for_successful_checkout(): void
    {
        $user = User::factory()->create();
        $primarySession = 'session-primary';
        $secondarySession = 'session-secondary';

        CartItem::factory()->forUser($user)->forSession($primarySession)->create();
        CartItem::factory()->forUser($user)->forSession($secondarySession)->create();

        app(CartLifecycleService::class)->clearAfterCheckout($user->id, $primarySession, 'paid');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_does_not_clear_cart_for_partial_payment(): void
    {
        $user = User::factory()->create();
        $sessionId = 'session-partial';

        $cartItem = CartItem::factory()->forUser($user)->forSession($sessionId)->create();

        app(CartLifecycleService::class)->clearAfterCheckout($user->id, $sessionId, 'partial');

        $this->assertDatabaseHas('cart_items', ['id' => $cartItem->id]);
    }

    public function test_clears_cart_when_session_expired_but_user_present(): void
    {
        $user = User::factory()->create();

        CartItem::factory()->forUser($user)->forSession('expired-session')->create();

        app(CartLifecycleService::class)->clearAfterCheckout($user->id, null, 'paid');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_clears_guest_cart_on_abandoned_checkout(): void
    {
        $sessionId = 'guest-session';
        $cartItem = CartItem::factory()->guest()->forSession($sessionId)->create();

        app(CartLifecycleService::class)->clearForAbandonedCheckout(null, $sessionId);

        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }
}
