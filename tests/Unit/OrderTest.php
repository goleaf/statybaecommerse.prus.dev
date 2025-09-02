<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_can_be_created(): void
    {
        $order = Order::factory()->create([
            'number' => 'ORD-123',
            'total' => 150.50,
            'currency' => 'EUR',
        ]);

        $this->assertDatabaseHas('orders', [
            'number' => 'ORD-123',
            'total' => 150.50,
            'currency' => 'EUR',
        ]);
    }

    public function test_order_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    public function test_order_has_many_items(): void
    {
        $order = Order::factory()->create();
        $products = Product::factory()->count(3)->create();

        foreach ($products as $product) {
            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => 2,
                'price' => $product->price,
                'total' => $product->price * 2,
            ]);
        }

        $this->assertCount(3, $order->items);
        $this->assertInstanceOf(OrderItem::class, $order->items->first());
    }

    public function test_order_casts_work_correctly(): void
    {
        $order = Order::factory()->create([
            'subtotal' => 100.50,
            'tax_amount' => 21.11,
            'total' => 121.61,
            'billing_address' => ['city' => 'Vilnius'],
            'shipped_at' => '2025-01-01 12:00:00',
        ]);

        $this->assertIsFloat($order->subtotal);
        $this->assertIsFloat($order->tax_amount);
        $this->assertIsFloat($order->total);
        $this->assertIsArray($order->billing_address);
        $this->assertInstanceOf(\Carbon\Carbon::class, $order->shipped_at);
    }

    public function test_order_soft_deletes(): void
    {
        $order = Order::factory()->create();
        $orderId = $order->id;

        $order->delete();

        $this->assertSoftDeleted('orders', ['id' => $orderId]);
        $this->assertNotNull($order->fresh()->deleted_at);
    }

    public function test_order_fillable_attributes(): void
    {
        $order = new Order();
        
        $expectedFillable = [
            'number',
            'user_id',
            'status',
            'subtotal',
            'tax_amount',
            'shipping_amount',
            'discount_amount',
            'total',
            'currency',
            'billing_address',
            'shipping_address',
            'notes',
            'shipped_at',
            'delivered_at',
        ];

        $this->assertEquals($expectedFillable, $order->getFillable());
    }
}
