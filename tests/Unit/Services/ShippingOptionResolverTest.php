<?php

declare(strict_types=1);

use App\Models\CartItem;
use App\Models\Country;
use App\Models\Product;
use App\Models\ShippingOption;
use App\Services\Shipping\ShippingOptionResolver;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

it('filters shipping options by geography and weight constraints', function (): void {
    // Bootstrap a predictable session identifier for the resolver to locate cart items.
    $sessionId = 'resolver-session-' . Str::uuid();
    Session::setId($sessionId);
    Session::start();

    $resolver = app(ShippingOptionResolver::class);

    $lithuania = Country::factory()->create([
        'cca2'          => 'LT',
        'code'          => 'LT',
        'currency_code' => 'EUR',
        'is_active'     => true,
        'is_enabled'    => true,
    ]);

    $estonia = Country::factory()->create([
        'cca2'          => 'EE',
        'code'          => 'EE',
        'currency_code' => 'EUR',
        'is_active'     => true,
        'is_enabled'    => true,
    ]);

    $product = Product::factory()->create([
        'weight' => 4.0,
        'price'  => 80,
    ]);

    $cartItem = CartItem::factory()->guest()->forSession($sessionId)->create([
        'product_id'       => $product->getKey(),
        'price'            => 80,
        'unit_price'       => 80,
        'quantity'         => 1,
        'total_price'      => 80,
        'product_snapshot' => [
            'name'   => $product->name,
            'sku'    => $product->sku,
            'price'  => 80,
            'weight' => 4.0,
        ],
    ]);

    $eligible = ShippingOption::factory()->create([
        'country_id'       => $lithuania->getKey(),
        'min_weight'       => 0,
        'max_weight'       => 10,
        'min_order_amount' => 0,
        'max_order_amount' => null,
        'price'            => 7.50,
        'is_enabled'       => true,
    ]);

    $ineligibleByWeight = ShippingOption::factory()->create([
        'country_id'       => $lithuania->getKey(),
        'min_weight'       => 5,
        'max_weight'       => 15,
        'min_order_amount' => 0,
        'max_order_amount' => null,
        'price'            => 5.00,
        'is_enabled'       => true,
    ]);

    $foreign = ShippingOption::factory()->create([
        'country_id'       => $estonia->getKey(),
        'min_weight'       => 0,
        'max_weight'       => 10,
        'min_order_amount' => 0,
        'max_order_amount' => null,
        'price'            => 6.00,
        'is_enabled'       => true,
    ]);

    $options = $resolver->resolve(collect([$cartItem]), 'LT');

    expect($options->pluck('id'))->toContain($eligible->getKey());
    expect($options->pluck('id'))->not->toContain($ineligibleByWeight->getKey());
    expect($options->pluck('id'))->not->toContain($foreign->getKey());
    expect($options->pluck('price')->map(fn ($price) => (float) $price)->first())->toBe((float) $eligible->price);
});
