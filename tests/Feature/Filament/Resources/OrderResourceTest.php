<?php

declare(strict_types=1);

use App\Enums\PaymentStatus;
use App\Filament\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Filament\Pages\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->resolveAdminPanel();

    $this->user = User::factory()->create([
        'email'    => 'admin@example.com',
        'is_admin' => true,
    ]);

    // Acting as admin
    $this->actingAs($this->user);
});

it('can list orders', function () {
    $orders = Order::factory()->count(10)->create();

    Livewire::test(ListOrders::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords($orders)
        ->assertCountTableRecords(10);
});

it('can render create order page', function () {
    Livewire::test(CreateOrder::class)
        ->assertSuccessful();
});

it('can create an order', function () {
    $newData = Order::factory()->make();

    Livewire::test(CreateOrder::class)
        ->fillForm([
            'number'         => $newData->number,
            'status'         => 'pending',
            'currency'       => 'EUR',
            'payment_status' => PaymentStatus::PENDING->value,
            'total'          => 100.00,
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

    Livewire::test(EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->fillForm([
            'status'         => 'processing',
            'payment_status' => PaymentStatus::PENDING->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->refresh()->status->value)->toBe('processing');
});

it('can delete an order', function () {
    $order = Order::factory()->create();

    Livewire::test(EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertSoftDeleted($order);
});

it('creates an order with products, services, and documents', function () {
    $product = Product::factory()->create([
        'price' => 25.00,
    ]);

    $service = Service::factory()->create([
        'price'     => 40.00,
        'is_active' => true,
    ]);

    $templates = DocumentTemplate::factory()
        ->count(2)
        ->create([
            'content'   => '<p>Order document</p>',
            'is_active' => true,
        ]);

    Livewire::test(CreateOrder::class)
        ->fillForm([
            'number'         => 'ORD-TEST-100',
            'status'         => 'pending',
            'currency'       => 'EUR',
            'payment_status' => PaymentStatus::PENDING->value,
            'total'          => 90.00,
            'items'          => [
                [
                    'product_id' => $product->id,
                    'quantity'   => 2,
                    'unit_price' => 25.00,
                ],
            ],
            'services' => [
                [
                    'service_id' => $service->id,
                    'quantity'   => 1,
                    'price'      => 40.00,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $order = Order::query()->where('number', 'ORD-TEST-100')->firstOrFail();

    $this->assertDatabaseHas('order_items', [
        'order_id'   => $order->id,
        'product_id' => $product->id,
        'quantity'   => 2,
    ]);

    $this->assertDatabaseHas('order_service', [
        'order_id'   => $order->id,
        'service_id' => $service->id,
        'quantity'   => 1,
        'price'      => 40.00,
    ]);

    expect(Document::query()->where('documentable_id', $order->id)->count())
        ->toBe($templates->count());

    expect(Document::query()->where('documentable_id', $order->id)->where('format', 'pdf')->count())
        ->toBe($templates->count());
});
