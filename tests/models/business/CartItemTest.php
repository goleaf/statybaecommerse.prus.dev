<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Builders\CartItemBuilder;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CartItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_price_attributes_are_synchronised_on_save(): void
    {
        // Arrange: create a cart item with partial monetary data to exercise the model hooks.
        $cartItem = CartItem::factory()->create([
            'unit_price'      => 25.00,
            'price'           => null,
            'quantity'        => 2,
            'discount_amount' => 5.00,
        ]);

        // Assert: the persisted record should keep the pricing columns aligned and discounted.
        $cartItem->refresh();
        $this->assertSame(25.00, (float) $cartItem->unit_price);
        $this->assertSame(25.00, (float) $cartItem->price);
        $this->assertSame(45.00, (float) $cartItem->total_price);
    }

    public function test_update_total_price_respects_discount(): void
    {
        // Arrange: provision a cart item that will be updated after creation.
        $cartItem = CartItem::factory()->create([
            'unit_price'      => 10.00,
            'price'           => 10.00,
            'quantity'        => 1,
            'discount_amount' => 0.00,
        ]);

        // Act: adjust the quantity and discount, then recalculate totals via the helper method.
        $cartItem->quantity = 3;
        $cartItem->discount_amount = 4.50;
        $cartItem->updateTotalPrice();

        // Assert: ensure the recalculated totals align with the expected arithmetic.
        $cartItem->refresh();
        $this->assertSame(25.50, (float) $cartItem->total_price);
        $this->assertSame(30.00, $cartItem->calculateSubtotal());
    }

    public function test_increment_and_decrement_quantity_adjusts_totals(): void
    {
        // Arrange: start with a predictable unit price so totals can be asserted directly.
        $cartItem = CartItem::factory()->create([
            'unit_price'      => 15.00,
            'price'           => 15.00,
            'quantity'        => 2,
            'discount_amount' => 0.00,
        ]);

        // Act: increment the quantity and rely on the model helpers to keep totals in sync.
        $cartItem->incrementQuantity();
        $cartItem->refresh();

        // Assert: after incrementing by the default amount, totals should scale accordingly.
        $this->assertSame(3, $cartItem->quantity);
        $this->assertSame(45.00, (float) $cartItem->total_price);

        // Act: decrement by a custom amount to ensure recalculations continue to work.
        $cartItem->decrementQuantity(2);
        $cartItem->refresh();

        // Assert: ensure the quantity and totals were recalculated after decrementing.
        $this->assertSame(1, $cartItem->quantity);
        $this->assertSame(15.00, (float) $cartItem->total_price);
    }

    public function test_decrement_quantity_to_zero_removes_item(): void
    {
        // Arrange: create a minimal cart item entry.
        $cartItem = CartItem::factory()->create([
            'quantity' => 1,
        ]);

        // Act: decrementing by the entire quantity should trigger a hard delete as a clean-up.
        $cartItem->decrementQuantity();

        // Assert: confirm the database no longer contains the removed cart item.
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_scopes_filter_expected_records(): void
    {
        // Arrange: prepare specific identifiers to validate the scope behaviour.
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $sessionId = 'cart-session-1';

        // Arrange: create both a matching and a non-matching cart item.
        $matching = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'session_id' => $sessionId,
        ]);
        $other = CartItem::factory()->create();

        // Assert: each scope should include the matching record and exclude the other.
        $sessionScope = CartItem::query()->forSession($sessionId)->get();
        $userScope = CartItem::query()->forUser($user->id)->get();
        $productScope = CartItem::query()->forProduct($product->id)->get();

        $this->assertTrue($sessionScope->contains($matching));
        $this->assertFalse($sessionScope->contains($other));

        $this->assertTrue($userScope->contains($matching));
        $this->assertFalse($userScope->contains($other));

        $this->assertTrue($productScope->contains($matching));
        $this->assertFalse($productScope->contains($other));
    }

    public function test_needs_restocking_uses_minimum_quantity_defaults(): void
    {
        // Arrange: configure a cart item with an explicit minimum quantity requirement.
        $cartItem = CartItem::factory()->create([
            'quantity'         => 1,
            'minimum_quantity' => 3,
        ]);

        // Assert: the item should request restocking when below the configured minimum.
        $this->assertTrue($cartItem->needsRestocking());

        // Act: elevate the quantity to the minimum threshold and refresh totals.
        $cartItem->updateQuantity(3);
        $cartItem->refresh();

        // Assert: no restocking is needed once the minimum quantity is satisfied.
        $this->assertFalse($cartItem->needsRestocking());

        // Arrange: create an item without a stored minimum quantity to exercise the fallback.
        $fallback = CartItem::factory()->create([
            'quantity'         => 1,
            'minimum_quantity' => null,
        ]);

        // Assert: the fallback minimum should default to one and not trigger restocking.
        $this->assertSame(1, $fallback->getMinimumQuantity());
        $this->assertFalse($fallback->needsRestocking());
    }

    public function test_product_snapshot_accessors_use_live_data_when_available(): void
    {
        // Arrange: create a cart item that initially relies on the snapshot payload.
        $cartItem = CartItem::factory()->create([
            'product_id'       => null,
            'product_snapshot' => [
                'name' => 'Snapshot Name',
                'sku'  => 'SNAP-123',
            ],
        ]);

        // Assert: fall back to snapshot data when no relation is present.
        $this->assertSame('Snapshot Name', $cartItem->product_name);
        $this->assertSame('SNAP-123', $cartItem->product_sku);

        // Act: attach a live product instance and persist the relationship for fresh assertions.
        $product = Product::factory()->create([
            'name' => 'Live Product',
            'sku'  => 'LIVE-001',
        ]);
        $cartItem->product()->associate($product);
        $cartItem->save();
        $cartItem->refresh();

        // Assert: once a product relation exists it should override the snapshot values.
        $this->assertSame('Live Product', $cartItem->product_name);
        $this->assertSame('LIVE-001', $cartItem->product_sku);
    }

    public function test_variant_relationship_can_be_eagerly_loaded(): void
    {
        // Arrange: create a product variant and attach it to the cart item.
        $variant = ProductVariant::factory()->create();
        $cartItem = CartItem::factory()->create([
            'product_variant_id' => $variant->id,
            'product_id'         => $variant->product_id,
        ]);

        // Assert: ensure the relationship resolves the correct variant instance.
        $this->assertFalse($cartItem->relationLoaded('productVariant'));
        $productVariant = $cartItem->productVariant;
        $this->assertNotNull($productVariant);
        $this->assertSame($variant->id, $productVariant->id);
    }

    public function test_custom_query_builder_is_used(): void
    {
        // Assert: verify the model resolves the bespoke CartItemBuilder for fluent methods.
        $this->assertInstanceOf(CartItemBuilder::class, CartItem::query());
    }
}
