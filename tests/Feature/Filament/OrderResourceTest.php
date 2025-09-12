<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\Order;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Zone;
use App\Models\Channel;
use App\Models\Partner;
use App\Filament\Resources\OrderResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->actingAs(User::factory()->create());
    }

    public function test_can_list_orders(): void
    {
        $orders = Order::factory()->count(3)->create();

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->assertCanSeeTableRecords($orders);
    }

    public function test_can_create_order(): void
    {
        $user = User::factory()->create();
        $zone = Zone::factory()->create();
        $channel = Channel::factory()->create();
        $partner = Partner::factory()->create();

        Livewire::test(OrderResource\Pages\CreateOrder::class)
            ->fillForm([
                'number' => 'ORD-TEST-001',
                'user_id' => $user->id,
                'status' => 'pending',
                'subtotal' => 100.00,
                'tax_amount' => 21.00,
                'shipping_amount' => 5.00,
                'discount_amount' => 0.00,
                'total' => 126.00,
                'currency' => 'EUR',
                'billing_address' => ['name' => 'John Doe', 'email' => 'john@example.com'],
                'shipping_address' => ['name' => 'John Doe', 'address' => '123 Main St'],
                'notes' => 'Test order',
                'zone_id' => $zone->id,
                'channel_id' => $channel->id,
                'partner_id' => $partner->id,
                'payment_status' => 'pending',
                'payment_method' => 'credit_card',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('orders', [
            'number' => 'ORD-TEST-001',
            'user_id' => $user->id,
            'status' => 'pending',
            'total' => 126.00,
        ]);
    }

    public function test_can_edit_order(): void
    {
        $order = Order::factory()->create();
        $newUser = User::factory()->create();

        Livewire::test(OrderResource\Pages\EditOrder::class, ['record' => $order->getRouteKey()])
            ->fillForm([
                'user_id' => $newUser->id,
                'status' => 'processing',
                'notes' => 'Updated notes',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $order->refresh();
        $this->assertEquals($newUser->id, $order->user_id);
        $this->assertEquals('processing', $order->status);
        $this->assertEquals('Updated notes', $order->notes);
    }

    public function test_can_view_order(): void
    {
        $order = Order::factory()->create();

        Livewire::test(OrderResource\Pages\ViewOrder::class, ['record' => $order->getRouteKey()])
            ->assertFormSet([
                'number' => $order->number,
                'user_id' => $order->user_id,
                'status' => $order->status,
                'total' => $order->total,
            ]);
    }

    public function test_can_delete_order(): void
    {
        $order = Order::factory()->create();

        Livewire::test(OrderResource\Pages\ViewOrder::class, ['record' => $order->getRouteKey()])
            ->callAction('delete');

        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    }

    public function test_can_filter_orders_by_status(): void
    {
        Order::factory()->create(['status' => 'pending']);
        Order::factory()->create(['status' => 'processing']);
        Order::factory()->create(['status' => 'pending']);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->filterTable('status', 'pending')
            ->assertCanSeeTableRecords(Order::where('status', 'pending')->get())
            ->assertCanNotSeeTableRecords(Order::where('status', 'processing')->get());
    }

    public function test_can_filter_orders_by_payment_status(): void
    {
        Order::factory()->create(['payment_status' => 'pending']);
        Order::factory()->create(['payment_status' => 'paid']);
        Order::factory()->create(['payment_status' => 'pending']);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->filterTable('payment_status', 'paid')
            ->assertCanSeeTableRecords(Order::where('payment_status', 'paid')->get())
            ->assertCanNotSeeTableRecords(Order::where('payment_status', 'pending')->get());
    }

    public function test_can_filter_orders_by_zone(): void
    {
        $zone1 = Zone::factory()->create();
        $zone2 = Zone::factory()->create();
        
        Order::factory()->create(['zone_id' => $zone1->id]);
        Order::factory()->create(['zone_id' => $zone2->id]);
        Order::factory()->create(['zone_id' => $zone1->id]);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->filterTable('zone_id', $zone1->id)
            ->assertCanSeeTableRecords(Order::where('zone_id', $zone1->id)->get())
            ->assertCanNotSeeTableRecords(Order::where('zone_id', $zone2->id)->get());
    }

    public function test_can_filter_orders_by_channel(): void
    {
        $channel1 = Channel::factory()->create();
        $channel2 = Channel::factory()->create();
        
        Order::factory()->create(['channel_id' => $channel1->id]);
        Order::factory()->create(['channel_id' => $channel2->id]);
        Order::factory()->create(['channel_id' => $channel1->id]);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->filterTable('channel_id', $channel1->id)
            ->assertCanSeeTableRecords(Order::where('channel_id', $channel1->id)->get())
            ->assertCanNotSeeTableRecords(Order::where('channel_id', $channel2->id)->get());
    }

    public function test_can_filter_orders_by_partner(): void
    {
        $partner1 = Partner::factory()->create();
        $partner2 = Partner::factory()->create();
        
        Order::factory()->create(['partner_id' => $partner1->id]);
        Order::factory()->create(['partner_id' => $partner2->id]);
        Order::factory()->create(['partner_id' => $partner1->id]);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->filterTable('partner_id', $partner1->id)
            ->assertCanSeeTableRecords(Order::where('partner_id', $partner1->id)->get())
            ->assertCanNotSeeTableRecords(Order::where('partner_id', $partner2->id)->get());
    }

    public function test_can_filter_orders_by_created_date(): void
    {
        $oldOrder = Order::factory()->create(['created_at' => now()->subDays(5)]);
        $newOrder = Order::factory()->create(['created_at' => now()]);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->filterTable('created_at', [
                'created_from' => now()->subDay()->format('Y-m-d'),
                'created_until' => now()->addDay()->format('Y-m-d'),
            ])
            ->assertCanSeeTableRecords([$newOrder])
            ->assertCanNotSeeTableRecords([$oldOrder]);
    }

    public function test_can_filter_orders_by_payment_status_ternary(): void
    {
        Order::factory()->create(['payment_status' => 'pending', 'status' => 'pending']);
        Order::factory()->create(['payment_status' => 'paid', 'status' => 'pending']);
        Order::factory()->create(['payment_status' => 'pending', 'status' => 'processing']);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->filterTable('is_paid', true)
            ->assertCanSeeTableRecords(Order::where('payment_status', 'paid')->orWhere('status', 'processing')->get())
            ->assertCanNotSeeTableRecords(Order::where('payment_status', 'pending')->where('status', 'pending')->get());
    }

    public function test_can_mark_order_as_shipped(): void
    {
        $order = Order::factory()->create(['status' => 'processing']);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->callTableAction('mark_shipped', $order)
            ->assertHasNoTableActionErrors();

        $order->refresh();
        $this->assertEquals('shipped', $order->status);
        $this->assertNotNull($order->shipped_at);
    }

    public function test_can_mark_order_as_delivered(): void
    {
        $order = Order::factory()->create(['status' => 'shipped']);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->callTableAction('mark_delivered', $order)
            ->assertHasNoTableActionErrors();

        $order->refresh();
        $this->assertEquals('delivered', $order->status);
        $this->assertNotNull($order->delivered_at);
    }

    public function test_can_cancel_order(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->callTableAction('cancel', $order)
            ->assertHasNoTableActionErrors();

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
    }

    public function test_can_bulk_mark_orders_as_shipped(): void
    {
        $orders = Order::factory()->count(3)->create(['status' => 'processing']);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->callTableBulkAction('bulk_mark_shipped', $orders)
            ->assertHasNoTableBulkActionErrors();

        foreach ($orders as $order) {
            $order->refresh();
            $this->assertEquals('shipped', $order->status);
            $this->assertNotNull($order->shipped_at);
        }
    }

    public function test_can_bulk_delete_orders(): void
    {
        $orders = Order::factory()->count(3)->create();

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->callTableBulkAction('delete', $orders)
            ->assertHasNoTableBulkActionErrors();

        foreach ($orders as $order) {
            $this->assertSoftDeleted('orders', ['id' => $order->id]);
        }
    }

    public function test_can_search_orders(): void
    {
        $order1 = Order::factory()->create(['number' => 'ORD-123456']);
        $order2 = Order::factory()->create(['number' => 'ORD-789012']);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->searchTable('123456')
            ->assertCanSeeTableRecords([$order1])
            ->assertCanNotSeeTableRecords([$order2]);
    }

    public function test_can_sort_orders_by_created_at(): void
    {
        $oldOrder = Order::factory()->create(['created_at' => now()->subDays(5)]);
        $newOrder = Order::factory()->create(['created_at' => now()]);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords([$newOrder, $oldOrder]);
    }

    public function test_can_sort_orders_by_total(): void
    {
        $lowOrder = Order::factory()->create(['total' => 50.00]);
        $highOrder = Order::factory()->create(['total' => 150.00]);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->sortTable('total', 'desc')
            ->assertCanSeeTableRecords([$highOrder, $lowOrder]);
    }

    public function test_order_tabs_work_correctly(): void
    {
        Order::factory()->create(['status' => 'pending']);
        Order::factory()->create(['status' => 'processing']);
        Order::factory()->create(['status' => 'shipped']);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->assertCanSeeTableRecords(Order::all())
            ->assertTableTabExists('all')
            ->assertTableTabExists('pending')
            ->assertTableTabExists('processing')
            ->assertTableTabExists('shipped')
            ->assertTableTabExists('delivered')
            ->assertTableTabExists('completed')
            ->assertTableTabExists('cancelled');
    }

    public function test_order_auto_generates_number_on_create(): void
    {
        $user = User::factory()->create();

        Livewire::test(OrderResource\Pages\CreateOrder::class)
            ->fillForm([
                'user_id' => $user->id,
                'status' => 'pending',
                'total' => 100.00,
                'currency' => 'EUR',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $order = Order::latest()->first();
        $this->assertStringStartsWith('ORD-', $order->number);
        $this->assertEquals(10, strlen($order->number)); // ORD- + 6 chars
    }

    public function test_order_sets_default_currency(): void
    {
        $user = User::factory()->create();

        Livewire::test(OrderResource\Pages\CreateOrder::class)
            ->fillForm([
                'user_id' => $user->id,
                'status' => 'pending',
                'total' => 100.00,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $order = Order::latest()->first();
        $this->assertEquals('EUR', $order->currency);
    }
}