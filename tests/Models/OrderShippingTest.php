<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Order;
use App\Models\OrderShipping;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class OrderShippingTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_shipping_exposes_expected_fillable_and_casts(): void
    {
        // Instantiate the model so we can introspect configuration-centric properties.
        $model = new OrderShipping;

        // Ensure all mass assignable attributes cover the known schema columns.
        self::assertSame([
            'order_id',
            'carrier_name',
            'shipping_method',
            'carrier',
            'service',
            'service_type',
            'tracking_number',
            'tracking_url',
            'shipped_at',
            'estimated_delivery',
            'delivered_at',
            'weight',
            'dimensions',
            'cost',
            'base_cost',
            'insurance_cost',
            'total_cost',
            'metadata',
            'status',
            'is_delivered',
            'delivery_notes',
            'notes',
        ], $model->getFillable());

        // Confirm important casts are registered so attributes hydrate as rich types.
        self::assertSame([
            'id'                 => 'int',
            'shipped_at'         => 'datetime',
            'estimated_delivery' => 'datetime',
            'delivered_at'       => 'datetime',
            'weight'             => 'decimal:3',
            'cost'               => 'decimal:2',
            'base_cost'          => 'decimal:2',
            'insurance_cost'     => 'decimal:2',
            'total_cost'         => 'decimal:2',
            'dimensions'         => 'array',
            'metadata'           => 'array',
            'is_delivered'       => 'boolean',
        ], $model->getCasts());
    }

    public function test_relationship_and_state_helpers(): void
    {
        // Persist an order that will be associated to the shipping record.
        $order = Order::factory()->create();

        // Create a shipping instance and explicitly link it to the order for clarity.
        $shipping = OrderShipping::factory()->create([
            'order_id'     => $order->getKey(),
            'carrier_name' => 'Baltic Express',
            'carrier'      => 'Baltic',
            'shipped_at'   => Carbon::now(),
        ]);

        // Guard the type for static analysis before touching relationship helpers.
        self::assertInstanceOf(OrderShipping::class, $shipping);

        // Confirm the belongs-to relationship resolves the owning order instance.
        self::assertInstanceOf(Order::class, $shipping->order);
        self::assertTrue($order->is($shipping->order));

        // Validate the domain helpers expose accurate status flags.
        self::assertTrue($shipping->isShipped());
        self::assertFalse($shipping->isDelivered());
        self::assertTrue($shipping->isInTransit());
        self::assertSame('in_transit', $shipping->status);
    }

    public function test_scopes_support_common_filters_and_sorting(): void
    {
        // Seed a trio of shipments with varied carrier names and lifecycle timestamps.
        $first = OrderShipping::query()->create([
            'order_id'     => Order::factory()->create()->getKey(),
            'carrier_name' => 'Alpha Couriers',
            'carrier'      => 'Alpha',
            'shipped_at'   => Carbon::now()->subDay(),
        ]);
        $second = OrderShipping::query()->create([
            'order_id'     => Order::factory()->create()->getKey(),
            'carrier_name' => 'Zeta Freight',
            'carrier'      => 'Zeta',
            'delivered_at' => Carbon::now()->subHours(2),
            'shipped_at'   => Carbon::now()->subHours(3),
            'is_delivered' => true,
        ]);
        $third = OrderShipping::query()->create([
            'order_id'     => Order::factory()->create()->getKey(),
            'carrier_name' => 'Midway Logistics',
            'carrier'      => 'Midway',
        ]);

        // The shipped scope should only contain records with a non-null shipped_at timestamp.
        self::assertNotNull($first->shipped_at);
        self::assertNotNull($second->shipped_at);
        $shippedIds = OrderShipping::withoutGlobalScopes()->shipped()->pluck('id')->all();
        self::assertSame([
            $first->getKey(),
            $second->getKey(),
        ], $shippedIds);

        // Delivered scope should narrow to entries where delivered_at is populated.
        $delivered = OrderShipping::withoutGlobalScopes()->delivered()->first();
        self::assertInstanceOf(OrderShipping::class, $delivered);
        self::assertTrue($delivered->is($second));

        // Carrier filtering relies on carrier_name for business-friendly lookups.
        $filtered = OrderShipping::withoutGlobalScopes()->byCarrier('Alpha Couriers')->first();
        self::assertInstanceOf(OrderShipping::class, $filtered);
        self::assertTrue($filtered->is($first));

        // Ordered by name scope must respect alphabetical ordering of carrier names.
        $orderedIds = OrderShipping::withoutGlobalScopes()->orderedByName()->pluck('id')->all();
        self::assertSame([
            $first->getKey(),
            $third->getKey(),
            $second->getKey(),
        ], $orderedIds);
    }
}
