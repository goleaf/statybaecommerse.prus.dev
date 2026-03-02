<?php

declare(strict_types=1);

use App\Livewire\Pages\Account\Orders\Detail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

test('order detail renders when line item unit_price_amount is unavailable', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id'  => $user->id,
        'number'   => 'ORD-NULL-AMOUNT',
        'currency' => 'EUR',
        'total'    => 14.97,
    ]);

    $product = Product::factory()->create();

    OrderItem::factory()->create([
        'order_id'   => $order->id,
        'product_id' => $product->id,
        'name'       => 'Fallback amount test item',
        'quantity'   => 3,
        'unit_price' => 4.99,
        'price'      => 4.99,
        'total'      => 14.97,
    ]);

    $this->actingAs($user);

    Livewire::test(Detail::class, ['number' => 'ORD-NULL-AMOUNT'])
        ->assertStatus(200);
});
