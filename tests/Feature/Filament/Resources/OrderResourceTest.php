<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Resources\OrderResource\RelationManagers\InvoicesRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\PaymentsRelationManager;
use App\Models\AdminUser;
use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Support\PdfFixture;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->resolveAdminPanel();

    $this->admin = AdminUser::factory()->create([
        'email' => 'info@egisstatyba.lt',
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

it('registers invoices relation manager', function (): void {
    expect(OrderResource::getRelations())->toContain(InvoicesRelationManager::class);
});

it('uses documents label for the invoices relation tab', function (): void {
    $order = Order::factory()->create();

    expect(InvoicesRelationManager::getTitle($order, ViewOrder::class))
        ->toBe(__('messages.documents'));
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

it('creates an order with products and services', function () {
    Notification::fake();

    $product = Product::factory()->create([
        'price' => 25.00,
    ]);

    $service = Service::factory()->create([
        'price'     => 40.00,
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
});

it('can generate an invoice pdf from order view header action', function (): void {
    Config::set('invoices.enabled', false);

    $customer = User::factory()->create();
    $order = Order::factory()->create([
        'user_id'        => $customer->getKey(),
        'number'         => 'ORD-VIEW-INV-0001',
        'payment_status' => PaymentStatus::PAID->value,
        'total'          => 121.00,
    ]);

    $order->items()->create([
        'name'       => 'Header action invoice item',
        'sku'        => 'HDR-INV-ITEM-1',
        'quantity'   => 1,
        'unit_price' => 100.00,
        'price'      => 100.00,
        'total'      => 100.00,
    ]);

    Config::set('invoices.enabled', true);
    Config::set('invoices.base_url', 'https://saskaita.vercel.app');
    Config::set('invoices.api_token', 'test-api-token');
    Config::set('invoices.auth_bearer', '');
    Config::set('invoices.timeout_seconds', 5);
    Config::set('invoices.retry_times', 1);
    Config::set('invoices.retry_sleep_ms', 50);

    Storage::fake('secure-media');
    $orderMarker = "order_number:{$order->number};order_id:{$order->getKey()}";

    Http::fake([
        'https://saskaita.vercel.app/api/initiate' => Http::response(PdfFixture::binary('Order view header action'), 200, [
            'Content-Type' => 'application/pdf',
        ]),
        'https://saskaita.vercel.app/api/actions/list-invoices' => Http::response([
            'invoices' => [
                [
                    'id'           => 'ext-view-1001',
                    'series'       => 'VIEW',
                    'number'       => 1001,
                    'full_number'  => 'VIEW-1001',
                    'type'         => 'sf',
                    'notes'        => $orderMarker,
                    'total_amount' => 121.00,
                    'payer_email'  => (string) $customer->email,
                ],
            ],
        ], 200),
    ]);

    Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->callAction('generateInvoicePdf', data: [
            'invoice_type' => 'sf',
        ])
        ->assertHasNoActionErrors();

    $invoice = OrderInvoice::query()
        ->where('order_id', $order->getKey())
        ->latest('id')
        ->first();

    expect($invoice)->toBeInstanceOf(OrderInvoice::class)
        ->and($invoice?->status)->toBe(OrderInvoice::STATUS_READY)
        ->and($invoice?->downloadUrl())->not->toBeNull();

    Storage::disk('secure-media')->assertExists((string) $invoice?->file?->path);

    Http::assertSent(function (Request $request): bool {
        return str_ends_with($request->url(), '/api/initiate')
            || str_ends_with($request->url(), '/api/actions/list-invoices');
    });
});
