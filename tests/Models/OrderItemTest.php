<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_configuration_matches_expectations(): void
    {
        // Instantiate the model so we can inspect configuration without hitting the database.
        $model = new OrderItem;

        // Validate the backing table and fillable attributes to guard against accidental schema drift.
        self::assertSame('order_items', $model->getTable());
        self::assertSame([
            'order_id',
            'product_id',
            'product_variant_id',
            'name',
            'sku',
            'quantity',
            'unit_price',
            'price',
            'total',
            'notes',
            'discount_amount',
            'status',
        ], $model->getFillable());

        // Confirm monetary and quantity attributes are cast as expected for arithmetic operations.
        $casts = $model->getCasts();
        self::assertSame('integer', $casts['quantity']);
        self::assertSame('float', $casts['unit_price']);
        self::assertSame('float', $casts['price']);
        self::assertSame('float', $casts['total']);
        self::assertSame('float', $casts['discount_amount']);

        // Ensure the factory produces a persisted instance which proves it is wired correctly.
        $instance = OrderItem::factory()->create();
        self::assertTrue($instance->exists);
    }

    public function test_relationships_resolve_expected_models(): void
    {
        // Create a full order item graph to exercise the belongsTo relationships.
        $orderItem = OrderItem::factory()->create();
        $orderItem->load(['order', 'product']);

        // The order relationship should return the owning aggregate root.
        self::assertInstanceOf(Order::class, $orderItem->order);
        self::assertTrue($orderItem->order()->is($orderItem->order));

        // The product relationship should hydrate the product referenced by the item.
        self::assertInstanceOf(Product::class, $orderItem->product);
        self::assertTrue($orderItem->product()->is($orderItem->product));
    }

    public function test_creating_event_populates_defaults_from_product_information(): void
    {
        // Create the related order and product so the model hooks can derive missing attributes.
        $order = Order::factory()->create();
        $product = Product::factory()->create([
            'name'  => 'Profesionalus grąžtas',
            'sku'   => 'PRO-DRILL-001',
            'price' => 24.50,
        ]);

        // Persist an order item while intentionally omitting name, sku, unit price, and total fields.
        $orderItem = OrderItem::query()->create([
            'order_id'        => $order->getKey(),
            'product_id'      => $product->getKey(),
            'quantity'        => 2,
            'price'           => 24.50,
            'discount_amount' => 4.50,
        ]);
        $orderItem->refresh();

        // The creating hook should back-fill descriptive and pricing attributes from the product context.
        self::assertSame('Profesionalus grąžtas', $orderItem->name);
        self::assertSame('PRO-DRILL-001', $orderItem->sku);
        self::assertSame(24.50, $orderItem->unit_price);
        self::assertSame(44.5, $orderItem->total);
    }

    public function test_updating_event_recalculates_totals_when_pricing_changes(): void
    {
        // Create an order item with a known pricing baseline for deterministic assertions.
        $orderItem = OrderItem::factory()->create([
            'quantity'        => 1,
            'unit_price'      => 10.0,
            'price'           => 10.0,
            'total'           => 10.0,
            'discount_amount' => 0.0,
        ]);

        // Update the quantity which should trigger the updating hook and recompute totals.
        $orderItem->update([
            'quantity' => 3,
        ]);

        // Reload the model to confirm the derived total reflects the latest state.
        $orderItem->refresh();
        self::assertSame(30.0, $orderItem->total);
    }

    public function test_scope_ordered_by_name_returns_alphabetical_collection(): void
    {
        // Seed explicit names to verify ordering semantics without relying on random factory data.
        $alpha = OrderItem::factory()->create([
            'name'       => 'Alpha fastener',
            'sku'        => 'ALPHA-001',
            'quantity'   => 1,
            'unit_price' => 5.0,
            'price'      => 5.0,
            'total'      => 5.0,
        ]);
        $beta = OrderItem::factory()->create([
            'name'       => 'Beta fastener',
            'sku'        => 'BETA-002',
            'quantity'   => 1,
            'unit_price' => 5.0,
            'price'      => 5.0,
            'total'      => 5.0,
        ]);

        // The custom scope should order alphabetically regardless of creation order.
        $names = OrderItem::query()->orderedByName()->pluck('name');
        self::assertSame(['Alpha fastener', 'Beta fastener'], $names->all());

        // Sanity check to ensure both seeded records participated in the ordered result.
        $orderedIds = OrderItem::query()->orderedByName()->pluck('id');
        self::assertSame([$alpha->getKey(), $beta->getKey()], $orderedIds->all());
    }
}
