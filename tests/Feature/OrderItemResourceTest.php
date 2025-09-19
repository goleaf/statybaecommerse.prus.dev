<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class OrderItemResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Order $order;
    private Product $product;
    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->order = Order::factory()->create([
            'user_id' => $this->adminUser->id,
            'number' => 'ORD-001',
            'status' => 'pending',
            'total' => 100.0,
        ]);

        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 25.0,
        ]);

        $this->variant = ProductVariant::factory()->create([
            'product_id' => $this->product->id,
            'name' => 'Test Variant',
            'sku' => 'TEST-001-VAR',
            'price' => 25.0,
        ]);
    }

    public function test_can_list_order_items(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'name' => 'Test Order Item',
            'sku' => 'TEST-001',
            'quantity' => 2,
            'unit_price' => 25.0,
            'total' => 50.0,
        ]);

        $this->actingAs($this->adminUser);

        $response = $this->get(route('filament.admin.resources.order-items.index'));

        $response->assertOk();
        $response->assertSee('Test Order Item');
        $response->assertSee('ORD-001');
        $response->assertSee('50.00');
    }

    public function test_can_create_order_item(): void
    {
        $this->actingAs($this->adminUser);

        $response = Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\CreateOrderItem::class)
            ->fillForm([
                'order_id' => $this->order->id,
                'product_id' => $this->product->id,
                'product_variant_id' => $this->variant->id,
                'name' => 'New Order Item',
                'sku' => 'NEW-001',
                'quantity' => 3,
                'unit_price' => 30.0,
                'total' => 90.0,
            ])
            ->call('create');

        $response->assertHasNoFormErrors();

        $this->assertDatabaseHas('order_items', [
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'name' => 'New Order Item',
            'quantity' => 3,
            'unit_price' => 30.0,
            'total' => 90.0,
        ]);
    }

    public function test_can_view_order_item(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'name' => 'View Test Item',
            'quantity' => 1,
            'unit_price' => 20.0,
            'total' => 20.0,
        ]);

        $this->actingAs($this->adminUser);

        $response = $this->get(route('filament.admin.resources.order-items.view', $orderItem));

        $response->assertOk();
        $response->assertSee('View Test Item');
        $response->assertSee('ORD-001');
    }

    public function test_can_edit_order_item(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'name' => 'Edit Test Item',
            'quantity' => 1,
            'unit_price' => 20.0,
            'total' => 20.0,
        ]);

        $this->actingAs($this->adminUser);

        $response = Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\EditOrderItem::class, [
            'record' => $orderItem->getKey(),
        ])
            ->fillForm([
                'quantity' => 2,
                'unit_price' => 25.0,
                'total' => 50.0,
            ])
            ->call('save');

        $response->assertHasNoFormErrors();

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'quantity' => 2,
            'unit_price' => 25.0,
            'total' => 50.0,
        ]);
    }

    public function test_can_delete_order_item(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'name' => 'Delete Test Item',
            'quantity' => 1,
            'unit_price' => 20.0,
            'total' => 20.0,
        ]);

        $this->actingAs($this->adminUser);

        $response = Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\EditOrderItem::class, [
            'record' => $orderItem->getKey(),
        ])
            ->callAction('delete');

        $this->assertDatabaseMissing('order_items', [
            'id' => $orderItem->id,
        ]);
    }

    public function test_form_validation_requires_order(): void
    {
        $this->actingAs($this->adminUser);

        $response = Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\CreateOrderItem::class)
            ->fillForm([
                'product_id' => $this->product->id,
                'quantity' => 1,
                'unit_price' => 20.0,
            ])
            ->call('create');

        $response->assertHasFormErrors(['order_id']);
    }

    public function test_form_validation_requires_product(): void
    {
        $this->actingAs($this->adminUser);

        $response = Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\CreateOrderItem::class)
            ->fillForm([
                'order_id' => $this->order->id,
                'quantity' => 1,
                'unit_price' => 20.0,
            ])
            ->call('create');

        $response->assertHasFormErrors(['product_id']);
    }

    public function test_form_validation_requires_positive_quantity(): void
    {
        $this->actingAs($this->adminUser);

        $response = Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\CreateOrderItem::class)
            ->fillForm([
                'order_id' => $this->order->id,
                'product_id' => $this->product->id,
                'quantity' => 0,
                'unit_price' => 20.0,
            ])
            ->call('create');

        $response->assertHasFormErrors(['quantity']);
    }

    public function test_form_validation_requires_positive_unit_price(): void
    {
        $this->actingAs($this->adminUser);

        $response = Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\CreateOrderItem::class)
            ->fillForm([
                'order_id' => $this->order->id,
                'product_id' => $this->product->id,
                'quantity' => 1,
                'unit_price' => -10.0,
            ])
            ->call('create');

        $response->assertHasFormErrors(['unit_price']);
    }

    public function test_can_filter_by_order(): void
    {
        $order2 = Order::factory()->create([
            'user_id' => $this->adminUser->id,
            'number' => 'ORD-002',
            'status' => 'pending',
        ]);

        OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'name' => 'Item 1',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $this->product->id,
            'name' => 'Item 2',
        ]);

        $this->actingAs($this->adminUser);

        $response = Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\ListOrderItems::class)
            ->filterTable('order_id', $this->order->id);

        $response->assertCanSeeTableRecords(
            OrderItem::where('order_id', $this->order->id)->get()
        );
    }

    public function test_can_filter_by_product(): void
    {
        $product2 = Product::factory()->create([
            'name' => 'Product 2',
            'sku' => 'PROD-002',
        ]);

        OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'name' => 'Item 1',
        ]);

        OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $product2->id,
            'name' => 'Item 2',
        ]);

        $this->actingAs($this->adminUser);

        $response = Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\ListOrderItems::class)
            ->filterTable('product_id', $this->product->id);

        $response->assertCanSeeTableRecords(
            OrderItem::where('product_id', $this->product->id)->get()
        );
    }

    public function test_can_filter_by_date_range(): void
    {
        $oldItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'name' => 'Old Item',
            'created_at' => now()->subDays(10),
        ]);

        $newItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'name' => 'New Item',
            'created_at' => now()->subDays(2),
        ]);

        $this->actingAs($this->adminUser);

        $response = Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\ListOrderItems::class)
            ->filterTable('created_at', [
                'created_from' => now()->subDays(5)->toDateString(),
                'created_until' => now()->toDateString(),
            ]);

        $response->assertCanSeeTableRecords([$newItem]);
        $response->assertCanNotSeeTableRecords([$oldItem]);
    }

    public function test_can_bulk_delete_order_items(): void
    {
        $orderItem1 = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'name' => 'Item 1',
        ]);

        $orderItem2 = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'name' => 'Item 2',
        ]);

        $this->actingAs($this->adminUser);

        $response = Livewire::test(\App\Filament\Resources\OrderItemResource\Pages\ListOrderItems::class)
            ->callTableBulkAction('delete', [$orderItem1, $orderItem2]);

        $this->assertDatabaseMissing('order_items', [
            'id' => $orderItem1->id,
        ]);

        $this->assertDatabaseMissing('order_items', [
            'id' => $orderItem2->id,
        ]);
    }

    public function test_order_item_relationships(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
        ]);

        $this->assertInstanceOf(Order::class, $orderItem->order);
        $this->assertInstanceOf(Product::class, $orderItem->product);
        $this->assertInstanceOf(ProductVariant::class, $orderItem->productVariant);

        $this->assertEquals($this->order->id, $orderItem->order->id);
        $this->assertEquals($this->product->id, $orderItem->product->id);
        $this->assertEquals($this->variant->id, $orderItem->productVariant->id);
    }

    public function test_order_item_calculates_total_correctly(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 3,
            'unit_price' => 25.0,
        ]);

        $this->assertEquals(75.0, $orderItem->total);
    }

    public function test_order_item_with_discount(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 50.0,
            'total' => 80.0,  // 100 - 20 discount
        ]);

        $this->assertEquals(80.0, $orderItem->total);
    }
}

