<?php

declare(strict_types=1);

use App\Models\CartItem;
use App\Models\User;
use App\Services\Cart\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;

it('clears both primary and fallback session carts and cached/session state', function (): void {
    $user = User::factory()->create();
    $userId = $user->id;
    $primary = 'sess-primary-abc';
    $fallback = 'sess-fallback-xyz';

    // Seed cart rows matching both sessions and the user
    CartItem::query()->insert([
        ['session_id' => $primary, 'user_id' => null, 'product_id' => null, 'quantity' => 1],
        ['session_id' => $fallback, 'user_id' => null, 'product_id' => null, 'quantity' => 2],
        ['session_id' => null, 'user_id' => $userId, 'product_id' => null, 'quantity' => 3],
    ]);

    session()->put('cart', [['id' => 1, 'name' => 'x', 'price' => 1.23, 'quantity' => 1]]);
    session()->put('cart_session_id', $fallback);
    cache()->put('cart.summary.' . md5($primary . '|' . $userId), ['count' => 1], 60);
    cache()->put('cart.summary.' . md5($fallback . '|' . $userId), ['count' => 2], 60);

    $service = app(CartService::class);
    $service->clear($userId, $primary, $fallback);

    expect(DB::table('cart_items')->where('session_id', $primary)->count())->toBe(0)
        ->and(DB::table('cart_items')->where('session_id', $fallback)->count())->toBe(0)
        ->and(DB::table('cart_items')->where('user_id', $userId)->count())->toBe(0);

    expect(session()->has('cart'))->toBeFalse()
        ->and(session()->has('cart_session_id'))->toBeFalse();
});

it('uses stored fallback session id when explicit fallback not provided', function (): void {
    $user = User::factory()->create();
    $userId = $user->id;
    $primary = 'sess-alpha';
    $storedFallback = 'sess-beta';

    CartItem::query()->insert([
        ['session_id' => $primary, 'user_id' => null, 'product_id' => null, 'quantity' => 1],
        ['session_id' => $storedFallback, 'user_id' => null, 'product_id' => null, 'quantity' => 1],
        ['session_id' => null, 'user_id' => $userId, 'product_id' => null, 'quantity' => 1],
    ]);

    session()->put('cart_session_id', $storedFallback);

    $service = app(CartService::class);
    $service->clear($userId, $primary);

    expect(DB::table('cart_items')->whereIn('session_id', [$primary, $storedFallback])->count())->toBe(0)
        ->and(DB::table('cart_items')->where('user_id', $userId)->count())->toBe(0);
});
