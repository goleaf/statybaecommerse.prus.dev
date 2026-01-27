<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\Home;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class HomeAddToCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_adds_simple_product_and_cart_page_renders(): void
    {
        $product = Product::factory()->create([
            'price'        => 29.99,
            'sale_price'   => null,
            'status'       => 'published',
            'is_visible'   => true,
            'is_enabled'   => true,
            'manage_stock' => false,
            'published_at' => now()->subDay(),
        ]);

        session()->start();

        Livewire::test(Home::class)
            ->call('addToCart', $product->id, 2);

        $cart = session('cart', []);
        $this->assertArrayHasKey((string) $product->id, $cart);
        $this->assertSame(2, $cart[(string) $product->id]['quantity']);

        $this->get(route('frontend.cart.index'))
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_home_adds_variant_product_and_cart_page_renders(): void
    {
        $product = Product::factory()->create([
            'type'         => 'variable',
            'price'        => 49.99,
            'status'       => 'published',
            'is_visible'   => true,
            'is_enabled'   => true,
            'manage_stock' => false,
            'published_at' => now()->subDay(),
        ]);

        $variant = ProductVariant::factory()
            ->for($product)
            ->create([
                'name'               => 'Size L',
                'price'              => 39.99,
                'promotional_price'  => null,
                'is_on_sale'         => false,
                'track_inventory'    => false,
                'is_enabled'         => true,
            ]);

        session()->start();

        Livewire::test(Home::class)
            ->call('addToCart', $product->id, 1, null, $variant->id);

        $cart = session('cart', []);
        $cartKey = $product->id . ':' . $variant->id;

        $this->assertArrayHasKey($cartKey, $cart);
        $this->assertSame($variant->id, $cart[$cartKey]['variant_id']);

        $this->get(route('frontend.cart.index'))
            ->assertOk()
            ->assertSee('Size L');
    }
}
