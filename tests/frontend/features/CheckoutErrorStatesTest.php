<?php

declare(strict_types=1);

use App\Models\CartItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;

use function Pest\Laravel\get;
use function Pest\Laravel\postJson;

it('requires authentication to process checkout', function (): void {
    postJson(route('frontend.checkout.process'))
        ->assertUnauthorized();
});

it('validates checkout payload before processing orders', function (): void {
    $user = User::factory()->create();
    test()->actingAs($user);

    Session::start();

    CartItem::factory()->create([
        'session_id' => session()->getId(),
        'user_id' => $user->id,
    ]);

    $response = postJson(route('frontend.checkout.process'), []);

    $response->assertStatus(422);

    expect($response->json('errors.payment_method'))->toBeArray()->not()->toBeEmpty();
    expect($response->json('errors.confirm'))->toBeArray()->not()->toBeEmpty();
});

it('forbids displaying the checkout success page for a different users order', function (): void {
    $viewer = User::factory()->create();
    $owner = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $owner->id]);

    test()->actingAs($viewer);
    Session::start();
    session(['checkout.last_order_id' => $order->getKey()]);

    get(route('frontend.checkout.success'))->assertForbidden();
});

it('returns 404 when the checkout success order is missing', function (): void {
    $user = User::factory()->create();
    test()->actingAs($user);

    Session::start();
    session(['checkout.last_order_id' => 999999]);

    get(route('frontend.checkout.success'))->assertNotFound();
});

it('throttles repeated checkout attempts', function (): void {
    $user = User::factory()->create();
    test()->actingAs($user);

    Session::start();

    Config::set('checkout.rate_limit.attempts', 1);
    Config::set('checkout.rate_limit.decay_seconds', 60);

    $limiterKey = 'checkout:user:'.$user->id;
    RateLimiter::clear($limiterKey);

    postJson(route('frontend.checkout.process'), [])
        ->assertStatus(422);

    postJson(route('frontend.checkout.process'), [])
        ->assertStatus(429)
        ->assertJson(['success' => false]);

    RateLimiter::clear($limiterKey);
});

it('returns 500 when the checkout transaction fails', function (): void {
    $user = User::factory()->create();
    test()->actingAs($user);

    Session::start();

    $cartItem = CartItem::factory()->create([
        'session_id' => session()->getId(),
        'user_id' => $user->id,
    ]);

    DB::shouldReceive('transaction')
        ->once()
        ->andThrow(new \RuntimeException('database error'));

    postJson(route('frontend.checkout.process'), [
        'payment_method' => 'card',
        'confirm' => 'yes',
    ])
        ->assertStatus(500)
        ->assertJson(['success' => false]);

    $cartItem->delete();
    \Mockery::close();
});
