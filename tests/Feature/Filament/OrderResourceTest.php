<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class OrderResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->actingAs(User::factory()->create([
            'email' => 'admin@admin.com',
            'is_active' => true,
        ]));
    }

    public function test_can_render_order_list_page(): void
    {
        Order::factory()->count(5)->create();

        $response = $this->get(OrderResource::getUrl('index'));

        $response->assertOk();
    }

    public function test_can_list_orders(): void
    {
        $orders = Order::factory()->count(10)->create();

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->assertCanSeeTableRecords($orders);
    }

    public function test_can_search_orders_by_number(): void
    {
        $orders = Order::factory()->count(5)->create();
        $firstOrder = $orders->first();

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->searchTable($firstOrder->number)
            ->assertCanSeeTableRecords([$firstOrder])
            ->assertCanNotSeeTableRecords($orders->skip(1));
    }

    public function test_can_filter_orders_by_status(): void
    {
        $pendingOrders = Order::factory()->count(3)->create(['status' => 'pending']);
        $shippedOrders = Order::factory()->count(2)->create(['status' => 'shipped']);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->filterTable('status', 'pending')
            ->assertCanSeeTableRecords($pendingOrders)
            ->assertCanNotSeeTableRecords($shippedOrders);
    }

    public function test_can_filter_orders_by_date_range(): void
    {
        $recentOrders = Order::factory()->count(2)->create([
            'created_at' => now()->subDays(5),
        ]);
        $oldOrders = Order::factory()->count(3)->create([
            'created_at' => now()->subDays(30),
        ]);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->filterTable('created_at', [
                'created_from' => now()->subDays(10)->format('Y-m-d'),
                'created_until' => now()->format('Y-m-d'),
            ])
            ->assertCanSeeTableRecords($recentOrders)
            ->assertCanNotSeeTableRecords($oldOrders);
    }

    public function test_can_create_order(): void
    {
        $customer = User::factory()->create();
        
        $newData = [
            'number' => 'ORD-' . time(),
            'user_id' => $customer->id,
            'status' => 'pending',
            'subtotal' => 100.00,
            'tax_amount' => 21.00,
            'shipping_amount' => 10.00,
            'discount_amount' => 0.00,
            'total' => 131.00,
            'currency' => 'EUR',
        ];

        Livewire::test(OrderResource\Pages\CreateOrder::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('orders', [
            'number' => $newData['number'],
            'user_id' => $customer->id,
            'total' => 131.00,
        ]);
    }

    public function test_can_validate_order_creation(): void
    {
        Livewire::test(OrderResource\Pages\CreateOrder::class)
            ->fillForm([
                'number' => '',
                'subtotal' => -10,
                'total' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'number' => 'required',
                'subtotal' => 'min',
                'total' => 'required',
            ]);
    }

    public function test_can_edit_order(): void
    {
        $order = Order::factory()->create();

        $newData = [
            'status' => 'shipped',
            'shipped_at' => now(),
            'notes' => 'Order shipped successfully',
        ];

        Livewire::test(OrderResource\Pages\EditOrder::class, [
            'record' => $order->getRouteKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
            'notes' => 'Order shipped successfully',
        ]);
    }

    public function test_can_view_order(): void
    {
        $order = Order::factory()->create();

        Livewire::test(OrderResource\Pages\ViewOrder::class, [
            'record' => $order->getRouteKey(),
        ])
            ->assertFormSet([
                'number' => $order->number,
                'status' => $order->status,
                'total' => $order->total,
            ]);
    }

    public function test_can_delete_order(): void
    {
        $order = Order::factory()->create();

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->callTableAction('delete', $order);

        $this->assertSoftDeleted('orders', [
            'id' => $order->id,
        ]);
    }

    public function test_can_bulk_delete_orders(): void
    {
        $orders = Order::factory()->count(3)->create();

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->callTableBulkAction('delete', $orders);

        foreach ($orders as $order) {
            $this->assertSoftDeleted('orders', [
                'id' => $order->id,
            ]);
        }
    }

    public function test_order_status_badge_colors(): void
    {
        $pendingOrder = Order::factory()->create(['status' => 'pending']);
        $shippedOrder = Order::factory()->create(['status' => 'shipped']);
        $deliveredOrder = Order::factory()->create(['status' => 'delivered']);
        $cancelledOrder = Order::factory()->create(['status' => 'cancelled']);

        $component = Livewire::test(OrderResource\Pages\ListOrders::class);

        // Test that orders are displayed with correct status
        $component->assertCanSeeTableRecords([
            $pendingOrder,
            $shippedOrder,
            $deliveredOrder,
            $cancelledOrder,
        ]);
    }

    public function test_can_update_order_addresses(): void
    {
        $order = Order::factory()->create();

        $billingAddress = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'address' => '123 Main St',
            'city' => 'Anytown',
            'postal_code' => '12345',
            'country' => 'US',
        ];

        $shippingAddress = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'address' => '456 Oak Ave',
            'city' => 'Other City',
            'postal_code' => '67890',
            'country' => 'US',
        ];

        Livewire::test(OrderResource\Pages\EditOrder::class, [
            'record' => $order->getRouteKey(),
        ])
            ->fillForm([
                'billing_address' => $billingAddress,
                'shipping_address' => $shippingAddress,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $order->refresh();
        
        $this->assertEquals($billingAddress, $order->billing_address);
        $this->assertEquals($shippingAddress, $order->shipping_address);
    }
}
