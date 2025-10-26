<?php

declare(strict_types=1);

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

it('manages cart items through http endpoints', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 25.50]);

    $this->actingAs($user)
        ->postJson(route('frontend.cart.add'), [
            'product_id' => $product->id,
            'quantity'   => 2,
        ])
        ->assertCreated()
        ->assertJsonPath('cart_item.quantity', 2);

    $cartItem = CartItem::query()->first();
    expect($cartItem)->not->toBeNull();

    $this->actingAs($user)
        ->patchJson(route('frontend.cart.update', $cartItem), [
            'quantity' => 5,
        ])
        ->assertOk()
        ->assertJsonPath('cart_item.quantity', 5);

    expect($cartItem->refresh()->quantity)->toBe(5);

    $this->actingAs($user)
        ->deleteJson(route('frontend.cart.remove', $cartItem))
        ->assertOk()
        ->assertJsonPath('summary.item_count', 0);

    expect(CartItem::query()->count())->toBe(0);
});

it('converts a cart into an order during checkout', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 19.99]);

    $this->actingAs($user)
        ->postJson(route('frontend.cart.add'), [
            'product_id' => $product->id,
            'quantity'   => 3,
        ])
        ->assertCreated();

    $sessionId = session()->getId();

    CartItem::factory()
        ->guest()
        ->forProduct($product)
        ->forSession($sessionId)
        ->create();

    expect(CartItem::query()->where('user_id', $user->id)->exists())->toBeTrue();
    expect(CartItem::query()->where('session_id', $sessionId)->exists())->toBeTrue();

    $response = $this->actingAs($user)->post(route('frontend.checkout.process'), [
        'payment_method' => 'card',
        'confirm'        => true,
    ]);

    $response->assertRedirect(route('frontend.checkout.success'));

    $this->actingAs($user)
        ->get(route('frontend.checkout.success'))
        ->assertOk()
        ->assertSee(__('Order confirmed'));

    expect(Order::withoutGlobalScopes()->count())->toBe(1);
    $order = Order::withoutGlobalScopes()->with('items')->first();
    expect($order)->not->toBeNull();
    expect($order->items)->toHaveCount(1);
    expect($order->items->first()->quantity)->toBe(3);
    expect($order->total)->toEqualWithDelta(59.97, 0.01);
    expect(CartItem::query()->where('user_id', $user->id)->exists())->toBeFalse();
    expect(CartItem::query()->where('session_id', $sessionId)->exists())->toBeFalse();
    expect(CartItem::query()->count())->toBe(0);
});
