<?php declare(strict_types=1);

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use Filament\Tables\Actions\DeleteAction;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

it('can render order resource index page', function () {
    $this->get(OrderResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list orders', function () {
    $orders = Order::factory()->count(10)->create();

    Livewire::test(OrderResource\Pages\ListOrders::class)
        ->assertCanSeeTableRecords($orders);
});

it('can render order resource create page', function () {
    $this->get(OrderResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create order', function () {
    $customer = User::factory()->create();
    
    $newData = [
        'number' => 'ORD-' . now()->format('YmdHis'),
        'user_id' => $customer->id,
        'status' => 'pending',
        'currency' => 'EUR',
        'subtotal' => 100.00,
        'tax_amount' => 21.00,
        'shipping_amount' => 5.00,
        'discount_amount' => 0.00,
        'total' => 126.00,
    ];

    Livewire::test(OrderResource\Pages\CreateOrder::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Order::class, [
        'number' => $newData['number'],
        'user_id' => $customer->id,
        'total' => 126.00,
    ]);
});

it('can validate order creation', function () {
    Livewire::test(OrderResource\Pages\CreateOrder::class)
        ->fillForm([
            'number' => null,
            'status' => null,
            'total' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['number', 'status', 'total']);
});

it('can render order resource view page', function () {
    $order = Order::factory()->create();

    $this->get(OrderResource::getUrl('view', ['record' => $order]))
        ->assertSuccessful();
});

it('can retrieve order data', function () {
    $order = Order::factory()->create();

    Livewire::test(OrderResource\Pages\ViewOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->assertFormSet([
            'number' => $order->number,
            'status' => $order->status,
            'total' => $order->total,
        ]);
});

it('can render order resource edit page', function () {
    $order = Order::factory()->create();

    $this->get(OrderResource::getUrl('edit', ['record' => $order]))
        ->assertSuccessful();
});

it('can update order', function () {
    $order = Order::factory()->create(['status' => 'pending']);

    Livewire::test(OrderResource\Pages\EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->fillForm([
            'status' => 'processing',
            'notes' => 'Order is being processed',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->refresh())
        ->status->toBe('processing')
        ->notes->toBe('Order is being processed');
});

it('can delete order', function () {
    $order = Order::factory()->create();

    Livewire::test(OrderResource\Pages\EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($order);
});

it('can search orders', function () {
    $orders = Order::factory()->count(10)->create();
    $searchOrder = $orders->first();

    Livewire::test(OrderResource\Pages\ListOrders::class)
        ->searchTable($searchOrder->number)
        ->assertCanSeeTableRecords([$searchOrder])
        ->assertCanNotSeeTableRecords($orders->skip(1));
});

it('can filter orders by status', function () {
    $pendingOrders = Order::factory()->count(3)->create(['status' => 'pending']);
    $shippedOrders = Order::factory()->count(2)->create(['status' => 'shipped']);

    Livewire::test(OrderResource\Pages\ListOrders::class)
        ->filterTable('status', 'pending')
        ->assertCanSeeTableRecords($pendingOrders)
        ->assertCanNotSeeTableRecords($shippedOrders);
});

it('can filter orders by date range', function () {
    $todayOrders = Order::factory()->count(3)->create(['created_at' => today()]);
    $oldOrders = Order::factory()->count(2)->create(['created_at' => now()->subDays(10)]);

    Livewire::test(OrderResource\Pages\ListOrders::class)
        ->filterTable('created_at', [
            'created_from' => today()->format('Y-m-d'),
            'created_until' => today()->format('Y-m-d'),
        ])
        ->assertCanSeeTableRecords($todayOrders)
        ->assertCanNotSeeTableRecords($oldOrders);
});

it('can sort orders', function () {
    $orders = Order::factory()->count(3)->create();

    Livewire::test(OrderResource\Pages\ListOrders::class)
        ->sortTable('created_at')
        ->assertCanSeeTableRecords($orders->sortBy('created_at'), inOrder: true)
        ->sortTable('created_at', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('created_at'), inOrder: true);
});

it('can bulk delete orders', function () {
    $orders = Order::factory()->count(5)->create();

    Livewire::test(OrderResource\Pages\ListOrders::class)
        ->callTableBulkAction('delete', $orders);

    foreach ($orders as $order) {
        $this->assertModelMissing($order);
    }
});

it('displays order totals correctly', function () {
    $order = Order::factory()->create([
        'subtotal' => 100.00,
        'tax_amount' => 21.00,
        'shipping_amount' => 5.00,
        'discount_amount' => 10.00,
        'total' => 116.00,
    ]);

    Livewire::test(OrderResource\Pages\ViewOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->assertSee('€100.00') // subtotal
        ->assertSee('€21.00')  // tax
        ->assertSee('€5.00')   // shipping
        ->assertSee('€10.00')  // discount
        ->assertSee('€116.00'); // total
});

it('can update order status with timestamps', function () {
    $order = Order::factory()->create(['status' => 'pending']);

    Livewire::test(OrderResource\Pages\EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->fillForm([
            'status' => 'shipped',
            'shipped_at' => now(),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->refresh())
        ->status->toBe('shipped')
        ->shipped_at->not->toBeNull();
});