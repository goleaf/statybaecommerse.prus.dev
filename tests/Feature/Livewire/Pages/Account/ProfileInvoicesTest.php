<?php

declare(strict_types=1);

use App\Livewire\Pages\Account\Profile;
use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows recent order invoices on account profile page', function (): void {
    $user = User::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->getKey(),
        'number'  => 'ORD-PROFILE-INV-1',
    ]);

    OrderInvoice::factory()->create([
        'order_id'    => $order->getKey(),
        'full_number' => 'SER-9001',
        'status'      => OrderInvoice::STATUS_READY,
        'is_current'  => true,
    ]);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->assertSuccessful()
        ->assertSee('Documents')
        ->assertSee('SER-9001')
        ->assertSee('ORD-PROFILE-INV-1');
});
