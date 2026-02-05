<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\Home;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
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
            'status'       => 'published',
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
            'price'        => 49.99,
            'status'       => 'published',
            'is_enabled'   => true,
            'manage_stock' => false,
            'published_at' => now()->subDay(),
        ]);

        $variant = ProductVariant::factory()
            ->for($product)
            ->create([
                'name'              => 'Size L',
                'price'             => 39.99,
                'promotional_price' => null,
                'is_on_sale'        => false,
                'track_inventory'   => false,
                'is_enabled'        => true,
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

    public function test_authenticated_user_adds_simple_product_and_cart_page_renders(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price'        => 19.99,
            'status'       => 'published',
            'is_enabled'   => true,
            'manage_stock' => false,
            'published_at' => now()->subDay(),
        ]);

        session()->start();

        Livewire::actingAs($user)
            ->test(Home::class)
            ->call('addToCart', $product->id, 1);

        $cart = session('cart', []);
        $this->assertArrayHasKey((string) $product->id, $cart);

        $this->actingAs($user)
            ->get(route('frontend.cart.index'))
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_home_adds_default_variant_when_none_is_selected(): void
    {
        $product = Product::factory()->create([
            'price'        => 59.99,
            'status'       => 'published',
            'is_enabled'   => true,
            'manage_stock' => false,
            'published_at' => now()->subDay(),
        ]);

        $defaultVariant = ProductVariant::factory()
            ->for($product)
            ->create([
                'name'              => 'Default Variant',
                'price'             => 54.99,
                'promotional_price' => null,
                'is_on_sale'        => false,
                'track_inventory'   => false,
                'is_enabled'        => true,
                'is_default'        => true,
            ]);

        session()->start();

        Livewire::test(Home::class)
            ->call('addToCart', $product->id, 1);

        $cart = session('cart', []);
        $cartKey = $product->id . ':' . $defaultVariant->id;

        $this->assertArrayHasKey($cartKey, $cart);
        $this->assertSame($defaultVariant->id, $cart[$cartKey]['variant_id']);
    }
}
