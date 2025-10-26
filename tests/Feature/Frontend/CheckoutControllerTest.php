<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_requires_authentication(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->withSession([
            'cart' => [1 => ['product_id' => 1, 'name' => 'Item', 'price' => 10, 'quantity' => 1, 'sku' => 'SKU-1']],
        ])->get(route('frontend.checkout.index'));

        $response->assertOk();
        $response->assertViewIs('frontend.checkout.index');
    }

    public function test_process_creates_order_and_clears_cart(): void
    {
        $user = User::factory()->create();
        $product = $this->createVisibleProduct();

        $cartItem = [
            (string) $product->id => [
                'product_id' => $product->id,
                'name'       => $product->name,
                'price'      => 20.0,
                'quantity'   => 2,
                'sku'        => 'SKU-123',
            ],
        ];

        $response = $this->actingAs($user)->withSession(['cart' => $cartItem])->post(route('frontend.checkout.process'), [
            'full_name'      => 'Test User',
            'email'          => 'test@example.com',
            'phone'          => '123456789',
            'address_line_1' => 'Main street 1',
            'address_line_2' => 'Apt 2',
            'city'           => 'Vilnius',
            'postal_code'    => '12345',
            'country'        => 'Lithuania',
            'payment_method' => 'card',
            'notes'          => 'Leave at door',
        ]);

        $response->assertRedirect(route('frontend.checkout.success'));

        $this->assertDatabaseHas('orders', [
            'user_id'         => $user->id,
            'subtotal'        => 40.0,
            'tax_amount'      => 8.4,
            'shipping_amount' => 5.99,
            'discount_amount' => 0,
            'total'           => 54.39,
        ]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);
        $this->assertEmpty(session('cart', []));
        $this->assertNull(session('cart_discount'));
        $this->assertNotNull(session('checkout_order_id'));

        $success = $this->actingAs($user)->withSession(['checkout_order_id' => session('checkout_order_id')])->get(route('frontend.checkout.success'));
        $success->assertOk();
        $success->assertViewIs('frontend.checkout.success');
    }

    public function test_process_requires_items_in_cart(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('frontend.checkout.process'), [
            'full_name'      => 'Test User',
            'email'          => 'test@example.com',
            'address_line_1' => 'Main street 1',
            'city'           => 'Vilnius',
            'postal_code'    => '12345',
            'country'        => 'Lithuania',
            'payment_method' => 'card',
        ]);

        $response->assertRedirect(route('frontend.cart.index'));
        $response->assertSessionHasErrors('cart');
    }

    public function test_process_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->withSession([
            'cart' => [1 => ['product_id' => 1, 'name' => 'Item', 'price' => 10, 'quantity' => 1, 'sku' => 'SKU']],
        ])->post(route('frontend.checkout.process'), []);

        $response->assertSessionHasErrors(['full_name', 'email', 'address_line_1', 'city', 'postal_code', 'country', 'payment_method']);
    }

    private function createVisibleProduct(): Product
    {
        $brand = Brand::factory()->create([
            'is_active'  => true,
            'is_enabled' => true,
            'is_visible' => true,
        ]);
        $category = Category::factory()->create(['is_visible' => true]);

        $product = Product::factory()->create([
            'brand_id'     => $brand->id,
            'is_visible'   => true,
            'status'       => 'active',
            'published_at' => now(),
        ]);
        $product->categories()->attach($category->id);

        return $product;
    }
}
