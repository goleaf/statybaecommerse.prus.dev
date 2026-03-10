<?php

declare(strict_types=1);

use App\Livewire\Pages\Account\Orders;
use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\OrderItem;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('renders account orders page without calling order total as a method', function (): void {
    $user = User::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->getKey(),
        'number'  => 'ORD-LW-INDEX-1',
        'total'   => 121.00,
    ]);

    OrderItem::factory()->forOrder($order)->create();

    Livewire::actingAs($user)
        ->test(Orders::class)
        ->assertStatus(200)
        ->assertSee('ORD-LW-INDEX-1')
        ->assertDontSee('Download invoice');
});

it('does not show invoice download action in account orders list', function (): void {
    $user = User::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->getKey(),
        'number'  => 'ORD-LW-INDEX-2',
        'total'   => 99.99,
    ]);

    OrderItem::factory()->forOrder($order)->create();

    OrderInvoice::factory()->create([
        'order_id'   => $order->getKey(),
        'status'     => OrderInvoice::STATUS_READY,
        'is_current' => true,
    ]);

    Livewire::actingAs($user)
        ->test(Orders::class)
        ->assertStatus(200)
        ->assertSee('ORD-LW-INDEX-2')
        ->assertDontSee('Download invoice');
});
