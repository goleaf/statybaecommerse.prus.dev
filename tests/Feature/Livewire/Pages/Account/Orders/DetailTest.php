<?php

declare(strict_types=1);

use App\Livewire\Pages\Account\Orders\Detail;
use App\Models\Order;
use App\Models\User;
use Livewire\Livewire;

test('order detail component can be rendered', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id, 'number' => 'ORD-123']);

    $this->actingAs($user);

    Livewire::test(Detail::class, ['number' => 'ORD-123'])
        ->assertStatus(200)
        ->assertSee(__('Details of your order'));
});

test('order detail component shows order information', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'number'  => 'ORD-456',
        'total'   => 99.99,
    ]);

    $this->actingAs($user);

    Livewire::test(Detail::class, ['number' => 'ORD-456'])
        ->assertStatus(200)
        ->assertSee('ORD-456')
        ->assertSee('99.99');
});

test('order detail component throws 404 for non-existent order', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    Livewire::test(Detail::class, ['number' => 'NON-EXISTENT']);
});
