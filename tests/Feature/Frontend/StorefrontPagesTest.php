<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Brand;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;

/**
 * Smoke-test the main storefront entry points and ensure the JSON-first cart
 * and checkout flows behave as expected.
 */
final class StorefrontPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_listing_displays_published_products(): void
    {
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'name'         => 'Profesionalus plaktukas',
            'status'       => 'published',
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        $product->categories()->attach($category);

        $this
            ->get(route('frontend.products.index', ['search' => 'plaktukas']))
            ->assertOk()
            ->assertSee('Profesionalus plaktukas');
    }

    public function test_product_show_page_renders_successfully(): void
    {
        $product = Product::factory()->create([
            'name'         => 'Kampuotasis šlifuoklis',
            'status'       => 'published',
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        $this
            ->get(route('frontend.products.show', $product))
            ->assertOk()
            ->assertSee('Kampuotasis šlifuoklis');
    }

    public function test_category_page_lists_products(): void
    {
        $category = Category::factory()->create([
            'name' => 'Dažai ir lakavimo priemonės',
        ]);

        $product = Product::factory()->create([
            'name'         => 'Fasadiniai dažai',
            'status'       => 'published',
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        $product->categories()->attach($category);

        $this
            ->get(route('frontend.categories.show', $category))
            ->assertOk()
            ->assertSee('Fasadiniai dažai');
    }

    public function test_brand_page_lists_products(): void
    {
        $brand = Brand::factory()->create([
            'name' => 'Makita Tools LT',
        ]);

        Product::factory()->create([
            'name'         => 'Makita suktuvas',
            'brand_id'     => $brand->id,
            'status'       => 'published',
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        $this
            ->get(route('frontend.brands.show', $brand))
            ->assertOk()
            ->assertSee('Makita suktuvas');
    }

    public function test_cart_add_update_remove_flow_via_json(): void
    {
        $product = Product::factory()->create([
            'price'        => 49.99,
            'status'       => 'published',
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        $sessionData = ['cart-test' => true];

        $addResponse = $this
            ->withSession($sessionData)
            ->postJson(route('frontend.cart.add'), [
                'product_id' => $product->id,
                'quantity'   => 2,
            ])
            ->assertCreated();

        $cartItemId = (int) $addResponse->json('cart_item.id');
        $this->assertDatabaseHas('cart_items', ['id' => $cartItemId, 'quantity' => 2]);

        $this
            ->withSession($sessionData)
            ->patchJson(route('frontend.cart.update', $cartItemId), [
                'quantity' => 3,
            ])
            ->assertOk()
            ->assertJsonPath('cart_item.quantity', 3);

        $this->assertDatabaseHas('cart_items', ['id' => $cartItemId, 'quantity' => 3]);

        $this
            ->withSession($sessionData)
            ->deleteJson(route('frontend.cart.remove', $cartItemId))
            ->assertOk();

        $this->assertDatabaseMissing('cart_items', ['id' => $cartItemId]);
    }

    public function test_checkout_process_returns_json_receipt_and_clears_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price'        => 89.99,
            'status'       => 'published',
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        $sessionStore = app('session');
        $sessionStore->setId('checkout-session');
        $sessionStore->start();

        CartItem::factory()
            ->forUser($user)
            ->forProduct($product)
            ->forSession('checkout-session')
            ->create(['quantity' => 1]);

        $this
            ->actingAs($user)
            ->withSession(['initiated' => true])
            ->postJson(route('frontend.checkout.process'), [
                'payment_method' => 'card',
                'confirm'        => true,
            ])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('cart_items', 0);
    }
}
