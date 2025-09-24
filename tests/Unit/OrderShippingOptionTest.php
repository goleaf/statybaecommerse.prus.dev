<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\ShippingOption;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->zone = Zone::factory()->create();
    $this->shippingOption = ShippingOption::factory()->create([
        'zone_id' => $this->zone->id,
    ]);
});

test('order can have a shipping option', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'zone_id' => $this->zone->id,
        'shipping_option_id' => $this->shippingOption->id,
    ]);

    expect($order->shippingOption)->toBeInstanceOf(ShippingOption::class);
    expect($order->shippingOption->id)->toBe($this->shippingOption->id);
    expect($order->shippingOption->name)->toBe($this->shippingOption->name);
});

test('order can be created without shipping option', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'zone_id' => $this->zone->id,
        'shipping_option_id' => null,
    ]);

    expect($order->shippingOption)->toBeNull();
});

test('shipping option can have multiple orders', function () {
    $order1 = Order::factory()->create([
        'user_id' => $this->user->id,
        'zone_id' => $this->zone->id,
        'shipping_option_id' => $this->shippingOption->id,
    ]);

    $order2 = Order::factory()->create([
        'user_id' => $this->user->id,
        'zone_id' => $this->zone->id,
        'shipping_option_id' => $this->shippingOption->id,
    ]);

    expect($this->shippingOption->orders)->toHaveCount(2);
    expect($this->shippingOption->orders->pluck('id')->toArray())->toContain($order1->id, $order2->id);
});

test('order can access shipping option details', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'zone_id' => $this->zone->id,
        'shipping_option_id' => $this->shippingOption->id,
    ]);

    $order->load('shippingOption');

    expect($order->shippingOption->carrier_name)->toBe($this->shippingOption->carrier_name);
    expect($order->shippingOption->service_type)->toBe($this->shippingOption->service_type);
    expect($order->shippingOption->price)->toBe($this->shippingOption->price);
});

test('zone can have multiple shipping options', function () {
    $shippingOption2 = ShippingOption::factory()->create([
        'zone_id' => $this->zone->id,
    ]);

    expect($this->zone->shippingOptions)->toHaveCount(2);
    expect($this->zone->shippingOptions->pluck('id')->toArray())->toContain($this->shippingOption->id, $shippingOption2->id);
});
