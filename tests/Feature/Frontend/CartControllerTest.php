<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_displays_cart_summary(): void
    {
        $response = $this->withSession([
            'cart' => [
                1 => ['product_id' => 1, 'name' => 'Sample item', 'price' => 10.0, 'quantity' => 2, 'sku' => 'SKU-1'],
            ],
        ])->get(route('frontend.cart.index'));

        $response->assertOk();
        $response->assertViewIs('frontend.cart.index');
        $response->assertSee('Sample item');
    }

    public function test_add_stores_product_in_session(): void
    {
        $product = $this->createVisibleProduct();

        $response = $this->post(route('frontend.cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect(route('frontend.cart.index'));
        $this->assertEquals(2, session('cart')[(string) $product->id]['quantity']);
    }

    public function test_add_validates_quantity(): void
    {
        $product = $this->createVisibleProduct();

        $response = $this->post(route('frontend.cart.add'), [
            'product_id' => $product->id,
            'quantity' => 0,
        ]);

        $response->assertSessionHasErrors('quantity');
    }

    public function test_update_changes_quantity(): void
    {
        $product = $this->createVisibleProduct();

        $this->withSession([
            'cart' => [
                (string) $product->id => ['product_id' => $product->id, 'name' => $product->name, 'price' => 10.0, 'quantity' => 2, 'sku' => 'SKU-1'],
            ],
        ])->post(route('frontend.cart.update'), [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $this->assertEquals(5, session('cart')[(string) $product->id]['quantity']);
    }

    public function test_remove_deletes_item(): void
    {
        $product = $this->createVisibleProduct();

        $this->withSession([
            'cart' => [
                (string) $product->id => ['product_id' => $product->id, 'name' => $product->name, 'price' => 10.0, 'quantity' => 2, 'sku' => 'SKU-1'],
            ],
        ])->post(route('frontend.cart.remove'), ['product_id' => $product->id]);

        $this->assertArrayNotHasKey((string) $product->id, session('cart', []));
    }

    public function test_clear_empties_cart_and_discount(): void
    {
        $this->withSession([
            'cart' => [1 => ['product_id' => 1, 'name' => 'Item', 'price' => 10, 'quantity' => 1, 'sku' => 'SKU-1']],
            'cart_discount' => 5,
            'applied_coupon' => ['code' => 'TEST'],
        ])->post(route('frontend.cart.clear'));

        $this->assertEmpty(session('cart', []));
        $this->assertNull(session('cart_discount'));
        $this->assertNull(session('applied_coupon'));
    }

    private function createVisibleProduct(): Product
    {
        $brand = Brand::factory()->create([
            'is_active' => true,
            'is_enabled' => true,
            'is_visible' => true,
        ]);
        $category = Category::factory()->create(['is_visible' => true]);

        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'is_visible' => true,
            'status' => 'active',
            'published_at' => now(),
        ]);
        $product->categories()->attach($category->id);

        return $product;
    }
}
