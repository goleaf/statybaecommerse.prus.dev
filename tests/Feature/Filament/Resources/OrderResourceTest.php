<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Models\Channel;
use App\Models\Order;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\TestCase;

class OrderResourceTest extends TestCase
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

        // Create test data
        $this->channel = Channel::factory()->create();
        $this->partner = Partner::factory()->create();
    }

    public function test_can_list_orders(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'channel_id' => $this->channel->id,
            'partner_id' => $this->partner->id,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderResource\Pages\ListOrders::class)
            ->assertCanSeeTableRecords([$order]);
    }

    public function test_can_create_order(): void
    {
        $this->actingAs($this->user);

        $orderData = [
            'number' => 'ORD-001',
            'user_id' => $this->user->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'credit_card',
            'subtotal' => 100.00,
            'tax_amount' => 21.00,
            'shipping_amount' => 10.00,
            'discount_amount' => 0.00,
            'total' => 131.00,
            'channel_id' => $this->channel->id,
            'partner_id' => $this->partner->id,
        ];

        Livewire::test(\App\Filament\Resources\OrderResource\Pages\CreateOrder::class)
            ->fillForm($orderData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('orders', [
            'number' => 'ORD-001',
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);
    }

    public function test_can_view_order(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'channel_id' => $this->channel->id,
            'partner_id' => $this->partner->id,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderResource\Pages\ViewOrder::class, ['record' => $order->id])
            ->assertCanSeeRecord($order);
    }

    public function test_can_edit_order(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'channel_id' => $this->channel->id,
            'partner_id' => $this->partner->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderResource\Pages\EditOrder::class, ['record' => $order->id])
            ->fillForm([
                'status' => 'processing',
                'payment_status' => 'paid',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
            'payment_status' => 'paid',
        ]);
    }

    public function test_can_delete_order(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'channel_id' => $this->channel->id,
            'partner_id' => $this->partner->id,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderResource\Pages\ListOrders::class)
            ->callTableAction('delete', $order)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_can_filter_orders_by_status(): void
    {
        $pendingOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $processingOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'processing',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderResource\Pages\ListOrders::class)
            ->filterTable('status', 'pending')
            ->assertCanSeeTableRecords([$pendingOrder])
            ->assertCanNotSeeTableRecords([$processingOrder]);
    }

    public function test_can_filter_orders_by_payment_status(): void
    {
        $paidOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_status' => 'paid',
        ]);

        $pendingOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_status' => 'pending',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderResource\Pages\ListOrders::class)
            ->filterTable('payment_status', 'paid')
            ->assertCanSeeTableRecords([$paidOrder])
            ->assertCanNotSeeTableRecords([$pendingOrder]);
    }

    public function test_can_search_orders_by_number(): void
    {
        $order1 = Order::factory()->create([
            'user_id' => $this->user->id,
            'number' => 'ORD-001',
        ]);

        $order2 = Order::factory()->create([
            'user_id' => $this->user->id,
            'number' => 'ORD-002',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderResource\Pages\ListOrders::class)
            ->searchTable('ORD-001')
            ->assertCanSeeTableRecords([$order1])
            ->assertCanNotSeeTableRecords([$order2]);
    }

    public function test_can_bulk_delete_orders(): void
    {
        $orders = Order::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderResource\Pages\ListOrders::class)
            ->callTableBulkAction('delete', $orders)
            ->assertHasNoTableBulkActionErrors();

        foreach ($orders as $order) {
            $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        }
    }

    public function test_can_mark_orders_as_processing(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderResource\Pages\ListOrders::class)
            ->callTableAction('mark_processing', $order)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);
    }

    public function test_can_mark_orders_as_shipped(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'processing',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderResource\Pages\ListOrders::class)
            ->callTableAction('mark_shipped', $order)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
        ]);
    }

    public function test_can_mark_orders_as_delivered(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'shipped',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderResource\Pages\ListOrders::class)
            ->callTableAction('mark_delivered', $order)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'delivered',
        ]);
    }

    public function test_can_cancel_order(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderResource\Pages\ListOrders::class)
            ->callTableAction('cancel_order', $order)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_can_refund_order(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'delivered',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderResource\Pages\ListOrders::class)
            ->callTableAction('refund_order', $order)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'refunded',
        ]);
    }

    public function test_can_bulk_mark_orders_as_processing(): void
    {
        $orders = Order::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderResource\Pages\ListOrders::class)
            ->callTableBulkAction('mark_processing', $orders)
            ->assertHasNoTableBulkActionErrors();

        foreach ($orders as $order) {
            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'status' => 'processing',
            ]);
        }
    }

    public function test_can_bulk_mark_orders_as_shipped(): void
    {
        $orders = Order::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'status' => 'processing',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderResource\Pages\ListOrders::class)
            ->callTableBulkAction('mark_shipped', $orders)
            ->assertHasNoTableBulkActionErrors();

        foreach ($orders as $order) {
            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'status' => 'shipped',
            ]);
        }
    }

    public function test_can_bulk_mark_orders_as_delivered(): void
    {
        $orders = Order::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'status' => 'shipped',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderResource\Pages\ListOrders::class)
            ->callTableBulkAction('mark_delivered', $orders)
            ->assertHasNoTableBulkActionErrors();

        foreach ($orders as $order) {
            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'status' => 'delivered',
            ]);
        }
    }

    public function test_can_bulk_cancel_orders(): void
    {
        $orders = Order::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderResource\Pages\ListOrders::class)
            ->callTableBulkAction('cancel_orders', $orders)
            ->assertHasNoTableBulkActionErrors();

        foreach ($orders as $order) {
            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'status' => 'cancelled',
            ]);
        }
    }

    public function test_can_export_orders(): void
    {
        $orders = Order::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $this->actingAs($this->user);

        Livewire::test(\App\Filament\Resources\OrderResource\Pages\ListOrders::class)
            ->callTableBulkAction('export_orders', $orders)
            ->assertHasNoTableBulkActionErrors();
    }
}
