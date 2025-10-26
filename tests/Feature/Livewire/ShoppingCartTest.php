<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Shared\ShoppingCart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ShoppingCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_adds_simple_product_to_cart(): void
    {
        $product = Product::factory()->create([
            'price'        => 199.00,
            'sale_price'   => null,
            'status'       => 'published',
            'is_visible'   => true,
            'is_enabled'   => true,
            'published_at' => now()->subDay(),
        ]);

        session()->start();

        Livewire::test(ShoppingCart::class)
            ->call('addToCart', $product->id, 2);

        $cartItem = CartItem::query()->withoutGlobalScopes()->first();

        $this->assertNotNull($cartItem);
        $this->assertSame($product->id, $cartItem->product_id);
        $this->assertNull($cartItem->variant_id);
        $this->assertSame(2, $cartItem->quantity);
        $this->assertSame((float) $product->price, (float) $cartItem->unit_price);
        $this->assertSame((float) $product->price, (float) $cartItem->price);
    }

    public function test_it_adds_variant_to_cart_with_variant_pricing(): void
    {
        $product = Product::factory()->create([
            'type'         => 'variable',
            'price'        => 249.00,
            'sale_price'   => null,
            'status'       => 'published',
            'is_visible'   => true,
            'is_enabled'   => true,
            'published_at' => now()->subDay(),
        ]);

        $variant = ProductVariant::factory()
            ->for($product)
            ->create([
                'price'              => 179.45,
                'promotional_price'  => 149.99,
                'is_on_sale'         => true,
                'sale_start_date'    => now()->subDay(),
                'sale_end_date'      => now()->addDay(),
                'is_default_variant' => true,
                'track_inventory'    => false,
                'is_enabled'         => true,
            ]);

        session()->start();

        Livewire::test(ShoppingCart::class)
            ->call('addToCart', $product->id, 3, $variant->id);

        $cartItem = CartItem::query()->withoutGlobalScopes()->where('variant_id', $variant->id)->first();

        $this->assertNotNull($cartItem);
        $this->assertSame($product->id, $cartItem->product_id);
        $this->assertSame($variant->id, $cartItem->variant_id);
        $this->assertSame(3, $cartItem->quantity);
        $this->assertSame((float) $variant->getCurrentPrice(), (float) $cartItem->unit_price);
        $this->assertSame((float) $variant->getCurrentPrice(), (float) $cartItem->price);
        $this->assertIsArray($cartItem->product_snapshot);
        $this->assertSame($variant->id, $cartItem->product_snapshot['variant_id'] ?? null);
    }
}
