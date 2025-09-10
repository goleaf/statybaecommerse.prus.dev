<?php declare(strict_types=1);

use App\Models\Channel;
use App\Models\Currency;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Zone;
use Database\Seeders\OrderSeeder;

it('seeds at least one order with items', function () {
    // Minimal prerequisites for OrderSeeder
    $user = User::factory()->create();
    $channel = Channel::factory()->create();
    $currency = Currency::factory()->create(['code' => 'EUR', 'is_default' => true]);
    $zone = Zone::factory()->create(['currency_id' => $currency->id, 'is_default' => true]);

    // A few visible, published products
    Product::factory()->count(3)->create([
        'is_visible' => true,
        'published_at' => now(),
    ]);

    // Sanity: no orders yet
    expect(Order::count())->toBe(0);

    // Run the seeder
    $this->seed(OrderSeeder::class);

    // Assertions
    $order = Order::query()->with('items', 'shipping')->latest()->first();
    expect($order)->not->toBeNull();
    expect($order->items)->not->toBeEmpty();
    expect($order->total)->toBeGreaterThan(0.0);
});

