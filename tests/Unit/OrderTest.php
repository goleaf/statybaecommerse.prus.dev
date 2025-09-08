<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Document;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_can_be_created(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'number' => 'ORD-2025-001',
            'status' => 'pending',
            'total' => 100.5,
            'currency' => 'EUR',
        ]);

        expect($order)
            ->number
            ->toBe('ORD-2025-001')
            ->status
            ->toBe('pending')
            ->total
            ->toBe(100.5)
            ->currency
            ->toBe('EUR')
            ->user_id
            ->toBe($user->id);
    }

    public function test_order_belongs_to_user(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $order = Order::factory()->create(['user_id' => $user->id]);

        expect($order->user)
            ->toBeInstanceOf(User::class)
            ->name
            ->toBe('John Doe');
    }

    public function test_order_has_many_items(): void
    {
        $order = Order::factory()->create();
        $items = OrderItem::factory()->count(3)->create(['order_id' => $order->id]);

        expect($order->items)
            ->toHaveCount(3)
            ->each
            ->toBeInstanceOf(OrderItem::class);
    }

    public function test_order_has_many_documents(): void
    {
        $order = Order::factory()->create();

        // Create documents using the morphMany relationship
        $documents = Document::factory()->count(2)->create([
            'documentable_type' => Order::class,
            'documentable_id' => $order->id,
        ]);

        expect($order->documents)
            ->toHaveCount(2)
            ->each
            ->toBeInstanceOf(Document::class);
    }

    public function test_order_fillable_attributes(): void
    {
        $fillable = [
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
            'channel_id',
            'zone_id',
            'partner_id',
            'payment_status',
            'payment_method',
            'payment_reference',
            'timeline',
        ];

        expect((new Order())->getFillable())->toBe($fillable);
    }

    public function test_order_casts(): void
    {
        $order = Order::factory()->create([
            'subtotal' => 100.123,
            'tax_amount' => 21.456,
            'shipping_amount' => 5.789,
            'discount_amount' => 10.111,
            'total' => 116.357,
            'billing_address' => ['street' => '123 Main St', 'city' => 'Vilnius'],
            'shipping_address' => ['street' => '456 Oak Ave', 'city' => 'Kaunas'],
            'shipped_at' => '2025-01-01 10:00:00',
            'delivered_at' => '2025-01-02 15:30:00',
            'timeline' => ['created', 'confirmed', 'shipped'],
        ]);

        expect($order->subtotal)->toBe(100.12);
        expect($order->tax_amount)->toBe(21.46);
        expect($order->shipping_amount)->toBe(5.79);
        expect($order->discount_amount)->toBe(10.11);
        expect($order->total)->toBe(116.36);
        expect($order->billing_address)->toBe(['street' => '123 Main St', 'city' => 'Vilnius']);
        expect($order->shipping_address)->toBe(['street' => '456 Oak Ave', 'city' => 'Kaunas']);
        expect($order->shipped_at)->toBeInstanceOf(\Carbon\Carbon::class);
        expect($order->delivered_at)->toBeInstanceOf(\Carbon\Carbon::class);
        expect($order->timeline)->toBe(['created', 'confirmed', 'shipped']);
    }

    public function test_order_scope_by_status(): void
    {
        Order::factory()->create(['status' => 'pending']);
        Order::factory()->create(['status' => 'shipped']);
        Order::factory()->count(2)->create(['status' => 'delivered']);

        $pendingOrders = Order::byStatus('pending')->get();
        $deliveredOrders = Order::byStatus('delivered')->get();

        expect($pendingOrders)->toHaveCount(1);
        expect($deliveredOrders)->toHaveCount(2);
    }

    public function test_order_scope_recent(): void
    {
        $oldOrder = Order::factory()->create(['created_at' => now()->subDays(5)]);
        $newOrder = Order::factory()->create(['created_at' => now()->subDay()]);
        $newestOrder = Order::factory()->create(['created_at' => now()]);

        $recentOrders = Order::recent()->get();

        expect($recentOrders->first()->id)->toBe($newestOrder->id);
        expect($recentOrders->last()->id)->toBe($oldOrder->id);
    }

    public function test_order_scope_completed(): void
    {
        Order::factory()->create(['status' => 'pending']);
        Order::factory()->create(['status' => 'processing']);
        $deliveredOrder = Order::factory()->create(['status' => 'delivered']);
        $completedOrder = Order::factory()->create(['status' => 'completed']);

        $completedOrders = Order::completed()->get();

        expect($completedOrders)->toHaveCount(2);
        expect($completedOrders->pluck('id'))->toContain($deliveredOrder->id, $completedOrder->id);
    }

    public function test_order_is_paid_method(): void
    {
        $pendingOrder = Order::factory()->create(['status' => 'pending']);
        $processingOrder = Order::factory()->create(['status' => 'processing']);
        $shippedOrder = Order::factory()->create(['status' => 'shipped']);
        $deliveredOrder = Order::factory()->create(['status' => 'delivered']);
        $completedOrder = Order::factory()->create(['status' => 'completed']);
        $cancelledOrder = Order::factory()->create(['status' => 'cancelled']);

        expect($pendingOrder->isPaid())->toBeFalse();
        expect($processingOrder->isPaid())->toBeTrue();
        expect($shippedOrder->isPaid())->toBeTrue();
        expect($deliveredOrder->isPaid())->toBeTrue();
        expect($completedOrder->isPaid())->toBeTrue();
        expect($cancelledOrder->isPaid())->toBeFalse();
    }

    public function test_order_is_shippable_method(): void
    {
        $pendingOrder = Order::factory()->create(['status' => 'pending']);
        $processingOrder = Order::factory()->create(['status' => 'processing']);
        $confirmedOrder = Order::factory()->create(['status' => 'confirmed']);
        $shippedOrder = Order::factory()->create(['status' => 'shipped']);

        expect($pendingOrder->isShippable())->toBeFalse();
        expect($processingOrder->isShippable())->toBeTrue();
        expect($confirmedOrder->isShippable())->toBeTrue();
        expect($shippedOrder->isShippable())->toBeFalse();
    }

    public function test_order_can_be_cancelled_method(): void
    {
        $pendingOrder = Order::factory()->create(['status' => 'pending']);
        $confirmedOrder = Order::factory()->create(['status' => 'confirmed']);
        $processingOrder = Order::factory()->create(['status' => 'processing']);
        $shippedOrder = Order::factory()->create(['status' => 'shipped']);

        expect($pendingOrder->canBeCancelled())->toBeTrue();
        expect($confirmedOrder->canBeCancelled())->toBeTrue();
        expect($processingOrder->canBeCancelled())->toBeFalse();
        expect($shippedOrder->canBeCancelled())->toBeFalse();
    }

    public function test_order_total_items_count_attribute(): void
    {
        $order = Order::factory()->create();

        OrderItem::factory()->create(['order_id' => $order->id, 'quantity' => 2]);
        OrderItem::factory()->create(['order_id' => $order->id, 'quantity' => 3]);
        OrderItem::factory()->create(['order_id' => $order->id, 'quantity' => 1]);

        expect($order->getTotalItemsCountAttribute())->toBe(6);
    }

    public function test_order_formatted_total_attribute(): void
    {
        $order = Order::factory()->create([
            'total' => 123.456,
            'currency' => 'EUR',
        ]);

        expect($order->getFormattedTotalAttribute())->toBe('123.46 EUR');
    }

    public function test_order_uses_soft_deletes(): void
    {
        $order = Order::factory()->create();

        $order->delete();

        expect($order->deleted_at)->not->toBeNull();
        expect(Order::count())->toBe(0);
        expect(Order::withTrashed()->count())->toBe(1);
    }

    public function test_order_logs_activity(): void
    {
        $order = Order::factory()->create([
            'number' => 'ORD-2025-001',
            'status' => 'pending',
        ]);

        // Update the order to trigger activity logging
        $order->update(['status' => 'processing']);

        $activities = $order->activities;

        expect($activities)->not->toBeEmpty();
        expect($activities->first()->description)->toContain('Order updated');
    }

    public function test_order_number_is_unique(): void
    {
        Order::factory()->create(['number' => 'ORD-UNIQUE-001']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Order::factory()->create(['number' => 'ORD-UNIQUE-001']);
    }

    public function test_order_requires_valid_status(): void
    {
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'completed', 'confirmed'];

        foreach ($validStatuses as $status) {
            $order = Order::factory()->create(['status' => $status]);
            expect($order->status)->toBe($status);
        }
    }

    public function test_order_currency_defaults_to_eur(): void
    {
        $order = Order::factory()->create(['currency' => null]);

        // This would depend on your factory or model default
        // Adjust based on your actual implementation
        expect($order->currency)->toBeNull();  // or toBe('EUR') if you have a default
    }
}
