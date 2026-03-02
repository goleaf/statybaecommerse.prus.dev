<?php

declare(strict_types=1);

use App\Livewire\Pages\Account\Orders\Detail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShipping;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

test('order detail shows order number, payment, shipping and address data when available', function () {
    app()->setLocale('lt');

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id'         => $user->id,
        'number'          => 'LT-DATA-CHECK',
        'currency'        => 'EUR',
        'subtotal'        => 14.97,
        'tax_amount'      => 3.14,
        'shipping_amount' => 0,
        'total'           => 18.11,
        'payment_method'  => 'montonio',
        'payment_status'  => 'pending',
        'payment_state'   => 'created',
        'billing_address' => [
            'first_name'  => 'Adena',
            'last_name'   => 'Blair',
            'address'     => 'Jonavos g. 254A',
            'city'        => 'Kaunas',
            'postal_code' => '44110',
            'country'     => 'Lietuva',
        ],
        'shipping_address' => [
            'first_name'          => 'Adena',
            'last_name'           => 'Blair',
            'address'             => 'Jonavos g. 254A',
            'city'                => 'Kaunas',
            'postal_code'         => '44110',
            'country'             => 'Lietuva',
            'delivery_place_name' => 'Venipak Pickup Point',
        ],
    ]);

    $product = Product::factory()->create();

    OrderItem::factory()->create([
        'order_id'   => $order->id,
        'product_id' => $product->id,
        'name'       => 'Order data test item',
        'quantity'   => 3,
        'unit_price' => 4.99,
        'price'      => 4.99,
        'total'      => 14.97,
    ]);

    OrderShipping::factory()->create([
        'order_id'        => $order->id,
        'status'          => 'pending',
        'carrier_name'    => 'Venipak',
        'shipping_method' => 'Pickup',
        'tracking_number' => null,
        'tracking_url'    => null,
    ]);

    $this->actingAs($user);

    Livewire::test(Detail::class, ['number' => 'LT-DATA-CHECK'])
        ->assertStatus(200)
        ->assertSee('LT-DATA-CHECK')
        ->assertSee('Montonio')
        ->assertSee(__('enums.order_payment_state.created'))
        ->assertSee('Pickup')
        ->assertSee('Jonavos g. 254A');
});
