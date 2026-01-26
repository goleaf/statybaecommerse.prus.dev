<?php

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use Filament\Pages\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'admin@example.com',
        'is_admin' => true,
    ]);
    
    // Acting as admin
    $this->actingAs($this->user);
});

it('can list orders', function () {
    $orders = Order::factory()->count(10)->create();

    livewire(OrderResource\Pages\ListOrders::class)
        ->assertCanSeeTableRecords($orders)
        ->assertCountTableRecords(10);
});

it('can render create order page', function () {
    livewire(OrderResource\Pages\CreateOrder::class)
        ->assertSuccessful();
});

it('can create an order', function () {
    $newData = Order::factory()->make();

    livewire(OrderResource\Pages\CreateOrder::class)
        ->fillForm([
            'number' => $newData->number,
            'status' => 'pending',
            'currency' => 'EUR',
            'total' => 100.00,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
    
    $this->assertDatabaseHas(Order::class, [
        'number' => $newData->number,
        'status' => 'pending',
    ]);
});

it('can edit an order', function () {
    $order = Order::factory()->create();

    livewire(OrderResource\Pages\EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->fillForm([
            'status' => 'processing',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->refresh()->status->value)->toBe('processing');
});

it('can delete an order', function () {
    $order = Order::factory()->create();

    livewire(OrderResource\Pages\EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertSoftDeleted($order);
});
