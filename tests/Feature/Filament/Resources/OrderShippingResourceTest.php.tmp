<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Models\Order;
use App\Models\OrderShipping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderShippingResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user with admin role
        $this->user = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // Create test order
        $this->order = Order::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_list_order_shippings(): void
    {
        $orderShipping = OrderShipping::factory()->create([
            'order_id' => $this->order->id,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderShippingResource\Pages\ListOrderShippings::class)
            ->assertCanSeeTableRecords([$orderShipping]);
    }

    public function test_can_create_order_shipping(): void
    {
        $this->actingAs($this->user);

        $orderShippingData = [
            'order_id' => $this->order->id,
            'carrier_name' => 'DHL',
            'service' => 'Express',
            'tracking_number' => 'TRK123456789',
            'tracking_url' => 'https://www.dhl.com/track/TRK123456789',
            'shipped_at' => now(),
            'estimated_delivery' => now()->addDays(3),
            'weight' => 1.5,
            'cost' => 15.99,
            'dimensions' => '30x20x10',
        ];

        Livewire::test(\App\Filament\Resources\OrderShippingResource\Pages\CreateOrderShipping::class)
            ->fillForm($orderShippingData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('order_shippings', [
            'order_id' => $this->order->id,
            'carrier_name' => 'DHL',
            'service' => 'Express',
            'tracking_number' => 'TRK123456789',
        ]);
    }

    public function test_can_view_order_shipping(): void
    {
        $orderShipping = OrderShipping::factory()->create([
            'order_id' => $this->order->id,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderShippingResource\Pages\ViewOrderShipping::class, ['record' => $orderShipping->id])
            ->assertCanSeeRecord($orderShipping);
    }

    public function test_can_edit_order_shipping(): void
    {
        $orderShipping = OrderShipping::factory()->create([
            'order_id' => $this->order->id,
            'carrier_name' => 'DHL',
            'service' => 'Express',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderShippingResource\Pages\EditOrderShipping::class, ['record' => $orderShipping->id])
            ->fillForm([
                'carrier_name' => 'FedEx',
                'service' => 'Standard',
                'tracking_number' => 'TRK987654321',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('order_shippings', [
            'id' => $orderShipping->id,
            'carrier_name' => 'FedEx',
            'service' => 'Standard',
            'tracking_number' => 'TRK987654321',
        ]);
    }

    public function test_can_delete_order_shipping(): void
    {
        $orderShipping = OrderShipping::factory()->create([
            'order_id' => $this->order->id,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderShippingResource\Pages\ListOrderShippings::class)
            ->callTableAction('delete', $orderShipping)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('order_shippings', ['id' => $orderShipping->id]);
    }

    public function test_can_filter_order_shippings_by_order(): void
    {
        $order1 = Order::factory()->create(['user_id' => $this->user->id]);
        $order2 = Order::factory()->create(['user_id' => $this->user->id]);

        $orderShipping1 = OrderShipping::factory()->create(['order_id' => $order1->id]);
        $orderShipping2 = OrderShipping::factory()->create(['order_id' => $order2->id]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderShippingResource\Pages\ListOrderShippings::class)
            ->filterTable('order_id', $order1->id)
            ->assertCanSeeTableRecords([$orderShipping1])
            ->assertCanNotSeeTableRecords([$orderShipping2]);
    }

    public function test_can_filter_order_shippings_by_carrier(): void
    {
        $orderShipping1 = OrderShipping::factory()->create([
            'order_id' => $this->order->id,
            'carrier_name' => 'DHL',
        ]);

        $orderShipping2 = OrderShipping::factory()->create([
            'order_id' => $this->order->id,
            'carrier_name' => 'FedEx',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderShippingResource\Pages\ListOrderShippings::class)
            ->filterTable('carrier_name', 'DHL')
            ->assertCanSeeTableRecords([$orderShipping1])
            ->assertCanNotSeeTableRecords([$orderShipping2]);
    }

    public function test_can_search_order_shippings_by_tracking_number(): void
    {
        $orderShipping1 = OrderShipping::factory()->create([
            'order_id' => $this->order->id,
            'tracking_number' => 'TRK123456789',
        ]);

        $orderShipping2 = OrderShipping::factory()->create([
            'order_id' => $this->order->id,
            'tracking_number' => 'TRK987654321',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderShippingResource\Pages\ListOrderShippings::class)
            ->searchTable('TRK123456789')
            ->assertCanSeeTableRecords([$orderShipping1])
            ->assertCanNotSeeTableRecords([$orderShipping2]);
    }

    public function test_can_bulk_delete_order_shippings(): void
    {
        $orderShippings = OrderShipping::factory()->count(3)->create([
            'order_id' => $this->order->id,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderShippingResource\Pages\ListOrderShippings::class)
            ->callTableBulkAction('delete', $orderShippings)
            ->assertHasNoTableBulkActionErrors();

        foreach ($orderShippings as $orderShipping) {
            $this->assertDatabaseMissing('order_shippings', ['id' => $orderShipping->id]);
        }
    }

    public function test_can_bulk_mark_order_shippings_as_shipped(): void
    {
        $orderShippings = OrderShipping::factory()->count(3)->create([
            'order_id' => $this->order->id,
            'shipped_at' => null,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderShippingResource\Pages\ListOrderShippings::class)
            ->callTableBulkAction('mark_shipped', $orderShippings)
            ->assertHasNoTableBulkActionErrors();

        foreach ($orderShippings as $orderShipping) {
            $this->assertDatabaseHas('order_shippings', [
                'id' => $orderShipping->id,
            ]);

            // Verify shipped_at was set
            $updatedShipping = OrderShipping::find($orderShipping->id);
            $this->assertNotNull($updatedShipping->shipped_at);
        }
    }

    public function test_can_bulk_mark_order_shippings_as_delivered(): void
    {
        $orderShippings = OrderShipping::factory()->count(3)->create([
            'order_id' => $this->order->id,
            'delivered_at' => null,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderShippingResource\Pages\ListOrderShippings::class)
            ->callTableBulkAction('mark_delivered', $orderShippings)
            ->assertHasNoTableBulkActionErrors();

        foreach ($orderShippings as $orderShipping) {
            $this->assertDatabaseHas('order_shippings', [
                'id' => $orderShipping->id,
            ]);

            // Verify delivered_at was set
            $updatedShipping = OrderShipping::find($orderShipping->id);
            $this->assertNotNull($updatedShipping->delivered_at);
        }
    }

    public function test_can_filter_order_shippings_by_shipped_date(): void
    {
        $shippedShipping = OrderShipping::factory()->create([
            'order_id' => $this->order->id,
            'shipped_at' => now()->subDays(5),
        ]);

        $notShippedShipping = OrderShipping::factory()->create([
            'order_id' => $this->order->id,
            'shipped_at' => null,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderShippingResource\Pages\ListOrderShippings::class)
            ->filterTable('shipped_at', [
                'shipped_from' => now()->subDays(10)->format('Y-m-d H:i:s'),
                'shipped_until' => now()->subDays(1)->format('Y-m-d H:i:s'),
            ])
            ->assertCanSeeTableRecords([$shippedShipping])
            ->assertCanNotSeeTableRecords([$notShippedShipping]);
    }

    public function test_can_sort_order_shippings_by_created_at(): void
    {
        $orderShipping1 = OrderShipping::factory()->create([
            'order_id' => $this->order->id,
            'created_at' => now()->subDays(2),
        ]);

        $orderShipping2 = OrderShipping::factory()->create([
            'order_id' => $this->order->id,
            'created_at' => now()->subDays(1),
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderShippingResource\Pages\ListOrderShippings::class)
            ->sortTable('created_at', 'asc')
            ->assertCanSeeTableRecords([$orderShipping1, $orderShipping2]);
    }

    public function test_can_toggle_order_shipping_columns(): void
    {
        $orderShipping = OrderShipping::factory()->create([
            'order_id' => $this->order->id,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderShippingResource\Pages\ListOrderShippings::class)
            ->assertCanSeeTableRecords([$orderShipping])
            ->assertTableColumnExists('tracking_number')
            ->assertTableColumnExists('shipped_at')
            ->assertTableColumnExists('estimated_delivery')
            ->assertTableColumnExists('delivered_at')
            ->assertTableColumnExists('cost')
            ->assertTableColumnExists('created_at');
    }
}
