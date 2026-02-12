<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\OrderResource\RelationManagers\PaymentsRelationManager;
use App\Models\AdminUser;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->resolveAdminPanel();

    $this->admin = AdminUser::factory()->create([
        'email' => 'admin@example.com',
    ]);

    // Acting as admin
    $this->actingAs($this->admin, 'admin');
});

it('can list orders', function () {
    $orders = Order::factory()->count(10)->create();

    Livewire::test(ListOrders::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords($orders)
        ->assertCountTableRecords(10);
});

it('does not register legacy payments relation manager', function (): void {
    expect(OrderResource::getRelations())->not->toContain(PaymentsRelationManager::class);
});

it('can render create order page', function () {
    Livewire::test(CreateOrder::class)
        ->assertSuccessful();
});

it('can create an order', function () {
    $newData = Order::factory()->make();

    Livewire::test(CreateOrder::class)
        ->fillForm([
            'number'          => $newData->number,
            'status'          => 'pending',
            'currency'        => 'EUR',
            'payment_status'  => PaymentStatus::PENDING->value,
            'subtotal'        => 0,
            'shipping_amount' => 0,
            'tax_amount'      => 0,
            'discount_amount' => 0,
            'total'           => 100.00,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Order::class, [
        'number' => $newData->number,
        'status' => 'pending',
    ]);
});

it('renders orders list when status and payment status are enum instances', function (): void {
    $order = Order::factory()->create([
        'status'         => OrderStatus::PROCESSING,
        'payment_status' => PaymentStatus::PAID,
    ]);

    Livewire::test(ListOrders::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords([$order])
        ->assertSuccessful();
});

it('does not return server error for legacy relation query index on edit page', function (): void {
    $order = Order::factory()->create();

    $response = $this->get("/admin/orders/{$order->getRouteKey()}/edit?relation=5");

    expect($response->status())->toBeLessThan(500);
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

    $this->assertDatabaseMissing('orders', [
        'id' => $order->getKey(),
    ]);
});

it('creates an order with products, services, and documents', function () {
    Notification::fake();

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
            'number'          => 'ORD-TEST-100',
            'status'          => 'pending',
            'currency'        => 'EUR',
            'payment_status'  => PaymentStatus::PENDING->value,
            'subtotal'        => 0,
            'shipping_amount' => 0,
            'tax_amount'      => 0,
            'discount_amount' => 0,
            'total'           => 90.00,
            'items'           => [
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
