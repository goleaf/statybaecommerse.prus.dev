<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\NavigationGroup;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

final class OrderItemResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_can_list_order_items(): void
    {
        $this->actingAs($this->adminUser);

        $order = Order::factory()->create();
        $product = Product::factory()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name' => 'Test Product',
            'quantity' => 2,
            'unit_price' => 10.00,
            'total' => 20.00,
        ]);

        Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\ListOrderItems::class)
            ->assertCanSeeTableRecords(OrderItem::all());
    }

    public function test_can_create_order_item(): void
    {
        $this->actingAs($this->adminUser);

        $order = Order::factory()->create();
        $product = Product::factory()->create();

        Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\CreateOrderItem::class)
            ->fillForm([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'name' => 'Test Product',
                'sku' => 'TEST-SKU',
                'quantity' => 2,
                'unit_price' => 10.00,
                'total' => 20.00,
                'notes' => 'Test notes',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name' => 'Test Product',
            'quantity' => 2,
            'unit_price' => 10.00,
            'total' => 20.00,
        ]);
    }

    public function test_can_edit_order_item(): void
    {
        $this->actingAs($this->adminUser);

        $order = Order::factory()->create();
        $product = Product::factory()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name' => 'Original Product',
            'quantity' => 1,
            'unit_price' => 5.00,
            'total' => 5.00,
        ]);

        Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\EditOrderItem::class, [
            'record' => $orderItem->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Product',
                'quantity' => 3,
                'unit_price' => 15.00,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'name' => 'Updated Product',
            'quantity' => 3,
            'unit_price' => 15.00,
            'total' => 45.00,
        ]);
    }

    public function test_can_view_order_item(): void
    {
        $this->actingAs($this->adminUser);

        $order = Order::factory()->create();
        $product = Product::factory()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name' => 'Test Product',
            'quantity' => 2,
            'unit_price' => 10.00,
            'total' => 20.00,
        ]);

        Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\ViewOrderItem::class, [
            'record' => $orderItem->getRouteKey(),
        ])
            ->assertCanSeeFormData([
                'name' => 'Test Product',
                'quantity' => 2,
                'unit_price' => 10.00,
            ]);
    }

    public function test_can_delete_order_item(): void
    {
        $this->actingAs($this->adminUser);

        $order = Order::factory()->create();
        $product = Product::factory()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);

        Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\ListOrderItems::class)
            ->callTableAction('delete', $orderItem);

        $this->assertDatabaseMissing('order_items', [
            'id' => $orderItem->id,
        ]);
    }

    public function test_can_filter_by_order(): void
    {
        $this->actingAs($this->adminUser);

        $order1 = Order::factory()->create();
        $order2 = Order::factory()->create();
        $product = Product::factory()->create();

        $orderItem1 = OrderItem::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $product->id,
        ]);
        $orderItem2 = OrderItem::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $product->id,
        ]);

        Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\ListOrderItems::class)
            ->filterTable('order_id', $order1->id)
            ->assertCanSeeTableRecords([$orderItem1])
            ->assertCanNotSeeTableRecords([$orderItem2]);
    }

    public function test_can_filter_by_product(): void
    {
        $this->actingAs($this->adminUser);

        $order = Order::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $orderItem1 = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
        ]);
        $orderItem2 = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
        ]);

        Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\ListOrderItems::class)
            ->filterTable('product_id', $product1->id)
            ->assertCanSeeTableRecords([$orderItem1])
            ->assertCanNotSeeTableRecords([$orderItem2]);
    }

    public function test_can_filter_by_created_date(): void
    {
        $this->actingAs($this->adminUser);

        $order = Order::factory()->create();
        $product = Product::factory()->create();

        $recentOrderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'created_at' => now(),
        ]);
        $oldOrderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'created_at' => now()->subDays(10),
        ]);

        Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\ListOrderItems::class)
            ->filterTable('created_at', [
                'created_from' => now()->subDay()->format('Y-m-d'),
                'created_until' => now()->addDay()->format('Y-m-d'),
            ])
            ->assertCanSeeTableRecords([$recentOrderItem])
            ->assertCanNotSeeTableRecords([$oldOrderItem]);
    }

    public function test_auto_calculates_total_when_quantity_changes(): void
    {
        $this->actingAs($this->adminUser);

        $order = Order::factory()->create();
        $product = Product::factory()->create();

        Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\CreateOrderItem::class)
            ->fillForm([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'unit_price' => 10.00,
                'quantity' => 3,
            ])
            ->assertFormSet('total', '30.00');
    }

    public function test_auto_calculates_total_when_unit_price_changes(): void
    {
        $this->actingAs($this->adminUser);

        $order = Order::factory()->create();
        $product = Product::factory()->create();

        Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\CreateOrderItem::class)
            ->fillForm([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => 15.00,
            ])
            ->assertFormSet('total', '30.00');
    }

    public function test_auto_calculates_total_with_discount(): void
    {
        $this->actingAs($this->adminUser);

        $order = Order::factory()->create();
        $product = Product::factory()->create();

        Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\CreateOrderItem::class)
            ->fillForm([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => 10.00,
                'discount_amount' => 5.00,
            ])
            ->assertFormSet('total', '15.00');
    }

    public function test_auto_fills_product_data_when_product_selected(): void
    {
        $this->actingAs($this->adminUser);

        $order = Order::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-SKU',
            'price' => 25.00,
        ]);

        Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\CreateOrderItem::class)
            ->fillForm([
                'order_id' => $order->id,
                'product_id' => $product->id,
            ])
            ->assertFormSet('name', 'Test Product')
            ->assertFormSet('sku', 'TEST-SKU')
            ->assertFormSet('unit_price', 25.00);
    }

    public function test_auto_fills_variant_data_when_variant_selected(): void
    {
        $this->actingAs($this->adminUser);

        $order = Order::factory()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Test Variant',
            'sku' => 'VARIANT-SKU',
            'price' => 30.00,
        ]);

        Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\CreateOrderItem::class)
            ->fillForm([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
            ])
            ->assertFormSet('name', 'Test Variant')
            ->assertFormSet('sku', 'VARIANT-SKU')
            ->assertFormSet('unit_price', 30.00);
    }

    public function test_validation_requires_order(): void
    {
        $this->actingAs($this->adminUser);

        $product = Product::factory()->create();

        Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\CreateOrderItem::class)
            ->fillForm([
                'product_id' => $product->id,
                'name' => 'Test Product',
                'quantity' => 1,
                'unit_price' => 10.00,
            ])
            ->call('create')
            ->assertHasFormErrors(['order_id']);
    }

    public function test_validation_requires_product(): void
    {
        $this->actingAs($this->adminUser);

        $order = Order::factory()->create();

        Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\CreateOrderItem::class)
            ->fillForm([
                'order_id' => $order->id,
                'name' => 'Test Product',
                'quantity' => 1,
                'unit_price' => 10.00,
            ])
            ->call('create')
            ->assertHasFormErrors(['product_id']);
    }

    public function test_validation_quantity_must_be_numeric(): void
    {
        $this->actingAs($this->adminUser);

        $order = Order::factory()->create();
        $product = Product::factory()->create();

        Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\CreateOrderItem::class)
            ->fillForm([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'name' => 'Test Product',
                'quantity' => 'invalid',
                'unit_price' => 10.00,
            ])
            ->call('create')
            ->assertHasFormErrors(['quantity']);
    }

    public function test_validation_quantity_must_be_minimum_one(): void
    {
        $this->actingAs($this->adminUser);

        $order = Order::factory()->create();
        $product = Product::factory()->create();

        Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\CreateOrderItem::class)
            ->fillForm([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'name' => 'Test Product',
                'quantity' => 0,
                'unit_price' => 10.00,
            ])
            ->call('create')
            ->assertHasFormErrors(['quantity']);
    }

    public function test_navigation_group_is_orders(): void
    {
        $this->assertEquals(NavigationGroup::Orders, \App\Filament\Resources\OrderItemResource::getNavigationGroup());
    }

    public function test_has_correct_navigation_sort(): void
    {
        $this->assertEquals(2, \App\Filament\Resources\OrderItemResource::getNavigationSort());
    }

    public function test_has_correct_record_title_attribute(): void
    {
        $this->assertEquals('product_name', \App\Filament\Resources\OrderItemResource::getRecordTitleAttribute());
    }
}
