<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CartItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes_cover_expected_cart_columns(): void
    {
        $model = new CartItem;

        $this->assertContains('session_id', $model->getFillable());
        $this->assertContains('quantity', $model->getFillable());
        $this->assertContains('total_price', $model->getFillable());
        $this->assertContains('product_snapshot', $model->getFillable());
    }

    public function test_casts_configuration_handles_numeric_and_array_fields(): void
    {
        $casts = (new CartItem)->getCasts();

        $this->assertSame('integer', $casts['quantity'] ?? null);
        $this->assertSame('decimal:2', $casts['unit_price'] ?? null);
        $this->assertSame('array', $casts['product_snapshot'] ?? null);
        $this->assertSame('array', $casts['attributes'] ?? null);
    }

    public function test_update_total_price_recalculates_totals_consistently(): void
    {
        $cartItem = CartItem::factory()->create([
            'quantity'        => 1,
            'unit_price'      => 10.00,
            'discount_amount' => 0.0,
        ]);

        $cartItem->quantity = 3;
        $cartItem->unit_price = 12.50;
        $cartItem->discount_amount = 2.25;
        $cartItem->updateTotalPrice();

        $cartItem->refresh();
        $this->assertSame(3, $cartItem->quantity);
        $this->assertSame(12.50, (float) $cartItem->unit_price);
        $this->assertSame(35.25, (float) $cartItem->total_price);
    }

    public function test_product_relationship_is_configured(): void
    {
        $product = Product::factory()->create();
        $cartItem = CartItem::factory()->for($product)->create();

        $this->assertInstanceOf(BelongsTo::class, $cartItem->product());
        $this->assertTrue($cartItem->product->is($product));
    }
}
