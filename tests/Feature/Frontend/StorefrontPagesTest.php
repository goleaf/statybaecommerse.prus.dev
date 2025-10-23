<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Address;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\TestCase;

final class StorefrontPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_listing_displays_published_products(): void
    {
        $category = Category::factory()->create([
            'name' => 'Elektriniai įrankiai',
            'slug' => 'elektriniai-irankiai',
        ]);

        $product = Product::factory()->create([
            'name' => 'Profesionalus plaktukas',
            'status' => 'published',
            'is_visible' => true,
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
            'name' => 'Kampuotasis šlifuoklis',
            'status' => 'published',
            'is_visible' => true,
            'published_at' => now()->subDay(),
        ]);

        $relatedProduct = Product::factory()->create([
            'name' => 'Elektrinis pjūklas',
            'status' => 'published',
            'is_visible' => true,
            'published_at' => now()->subDay(),
        ]);

        $category = Category::factory()->create([
            'name' => 'Statybinės medžiagos',
            'slug' => 'statybines-medziagos',
        ]);

        $product->categories()->attach($category);
        $relatedProduct->categories()->attach($category);

        $this
            ->get(route('frontend.products.show', $product))
            ->assertOk()
            ->assertSee('Kampuotasis šlifuoklis');
    }

    public function test_category_page_lists_products(): void
    {
        $category = Category::factory()->create([
            'name' => 'Dažai ir lakavimo priemonės',
            'slug' => 'dazai-ir-lakavimo-priemones',
        ]);

        $product = Product::factory()->create([
            'name' => 'Fasadiniai dažai',
            'status' => 'published',
            'is_visible' => true,
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
            'slug' => 'makita-tools-lt',
        ]);

        Product::factory()->create([
            'name' => 'Makita suktuvas',
            'status' => 'published',
            'is_visible' => true,
            'published_at' => now()->subDay(),
            'brand_id' => $brand->id,
        ]);

        $this
            ->get(route('frontend.brands.show', $brand))
            ->assertOk()
            ->assertSee('Makita suktuvas');
    }

    public function test_cart_add_update_and_remove_flow(): void
    {
        $product = Product::factory()->create([
            'name' => 'Universalus peilis',
            'status' => 'published',
            'is_visible' => true,
            'published_at' => now()->subDay(),
        ]);

        $this
            ->post(route('frontend.cart.add'), [
                'product_id' => $product->id,
                'quantity' => 2,
            ])
            ->assertRedirect(route('frontend.cart.index'))
            ->assertSessionHas('cart', fn (array $cart): bool => $cart[0]['quantity'] === 2);

        $this
            ->withSession([
                'cart' => [[
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'quantity' => 2,
                    'sku' => $product->sku,
                    'total' => (float) $product->price * 2,
                ]],
            ])
            ->post(route('frontend.cart.update'), [
                'items' => [
                    ['id' => $product->id, 'quantity' => 3],
                ],
            ])
            ->assertRedirect(route('frontend.cart.index'))
            ->assertSessionHas('cart', fn (array $cart): bool => $cart[0]['quantity'] === 3);

        $this
            ->withSession([
                'cart' => [[
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'quantity' => 3,
                    'sku' => $product->sku,
                    'total' => (float) $product->price * 3,
                ]],
            ])
            ->post(route('frontend.cart.remove'), ['id' => $product->id])
            ->assertRedirect(route('frontend.cart.index'))
            ->assertSessionHas('cart', fn (array $cart): bool => count($cart) === 0);

        $this
            ->withSession([
                'cart' => [[
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'quantity' => 1,
                    'sku' => $product->sku,
                    'total' => (float) $product->price,
                ]],
                'cart_discount' => 5.0,
                'applied_coupon' => 'SAVE10',
            ])
            ->post(route('frontend.cart.clear'))
            ->assertRedirect(route('frontend.cart.index'))
            ->assertSessionMissing('cart')
            ->assertSessionMissing('cart_discount')
            ->assertSessionMissing('applied_coupon');
    }

    public function test_checkout_process_persists_summary_and_clears_cart(): void
    {
        $user = User::factory()->create();

        $cartItem = [
            'id' => 1,
            'name' => 'Apsauginis šalmas',
            'price' => 49.99,
            'quantity' => 1,
            'sku' => 'SAFETY-001',
            'total' => 49.99,
        ];

        $this
            ->actingAs($user)
            ->withSession(['cart' => [$cartItem]])
            ->post(route('frontend.checkout.process'), [
                'name' => 'Jonas Statybininkas',
                'email' => 'jonas@example.com',
                'phone' => '+37060000000',
                'billing_address' => 'Vilniaus g. 10, Vilnius',
                'shipping_address' => 'Kauno g. 5, Kaunas',
                'payment_method' => 'card',
            ])
            ->assertRedirect(route('frontend.checkout.success'))
            ->assertSessionHas('checkout.completed', function (array $checkout) use ($user): bool {
                return $checkout['customer']['email'] === 'jonas@example.com'
                    && $checkout['payment_method'] === 'card'
                    && $checkout['summary']['total'] > 0;
            })
            ->assertSessionMissing('cart');
    }

    public function test_checkout_success_requires_completed_session(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->get(route('frontend.checkout.success'))
            ->assertRedirect(route('frontend.cart.index'));
    }

    public function test_discount_pages_render_and_coupon_flow_works(): void
    {
        $discount = Discount::factory()->create([
            'name' => 'Pavasario išpardavimas',
            'slug' => 'pavasario-ispardavimas',
            'status' => 'active',
        ]);

        DiscountCode::factory()->create([
            'code' => 'STATYBA10',
            'type' => 'percentage',
            'value' => 10,
            'minimum_amount' => 0,
            'maximum_discount' => 100,
            'status' => 'active',
            'is_active' => true,
            'discount_id' => $discount->id,
        ]);

        $this
            ->get(route('frontend.discounts.index'))
            ->assertOk()
            ->assertSee('Pavasario išpardavimas');

        $this
            ->get(route('frontend.discounts.show', $discount))
            ->assertOk()
            ->assertSee('Pavasario išpardavimas');

        $this
            ->get(route('frontend.discounts.coupons'))
            ->assertOk()
            ->assertSee('STATYBA10');

        $this
            ->withSession([
                'cart' => [[
                    'id' => 1,
                    'name' => 'Elektrinis perforatorius',
                    'price' => 120.00,
                    'quantity' => 1,
                    'sku' => 'TOOLS-001',
                    'total' => 120.00,
                ]],
            ])
            ->post(route('frontend.discounts.apply-coupon'), ['code' => 'statyba10'])
            ->assertRedirect(route('frontend.cart.index'))
            ->assertSessionHas('cart_discount', fn (float $discountAmount): bool => $discountAmount > 0)
            ->assertSessionHas('applied_coupon', 'STATYBA10');

        $this
            ->withSession([
                'cart_discount' => 12.0,
                'applied_coupon' => 'STATYBA10',
            ])
            ->post(route('frontend.discounts.remove-coupon'))
            ->assertRedirect()
            ->assertSessionMissing('cart_discount')
            ->assertSessionMissing('applied_coupon');
    }

    public function test_profile_pages_allow_updating_user_and_addresses(): void
    {
        $user = User::factory()->create([
            'name' => 'Asta Meistre',
            'email' => 'asta@example.com',
        ]);

        Address::factory()->for($user)->create([
            'first_name' => 'Asta',
            'last_name' => 'Meistre',
            'address_line_1' => 'Statybininkų g. 1',
            'city' => 'Vilnius',
            'postal_code' => '01100',
            'country_code' => 'LT',
        ]);

        Order::factory()->for($user)->create([
            'total' => 199.99,
            'status' => 'completed',
        ]);

        $this
            ->actingAs($user)
            ->get(route('frontend.profile.index'))
            ->assertOk()
            ->assertSee('Asta Meistre')
            ->assertSee('199.99');

        $this
            ->actingAs($user)
            ->get(route('frontend.profile.edit'))
            ->assertOk()
            ->assertSee('Asta Meistre');

        $this
            ->actingAs($user)
            ->put(route('frontend.profile.update'), [
                'name' => 'Asta Statytoja',
                'email' => 'asta@example.com',
            ])
            ->assertRedirect(route('frontend.profile.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Asta Statytoja',
        ]);

        $this
            ->actingAs($user)
            ->post(route('frontend.profile.store-address'), [
                'first_name' => 'Asta',
                'last_name' => 'Statytoja',
                'address_line_1' => 'Vilniaus g. 20',
                'city' => 'Vilnius',
                'postal_code' => '01100',
                'country_code' => 'LT',
                'is_default' => true,
                'is_shipping' => true,
                'is_billing' => true,
            ])
            ->assertRedirect(route('frontend.profile.addresses'));

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'address_line_1' => 'Vilniaus g. 20',
            'is_default' => true,
        ]);

        $address = $user->addresses()->latest()->first();

        $this
            ->actingAs($user)
            ->put(route('frontend.profile.update-address', $address), [
                'first_name' => 'Asta',
                'last_name' => 'Statytoja',
                'address_line_1' => 'Kauno g. 15',
                'city' => 'Kaunas',
                'postal_code' => '44100',
                'country_code' => 'LT',
                'is_default' => false,
                'is_shipping' => true,
                'is_billing' => false,
            ])
            ->assertRedirect(route('frontend.profile.addresses'));

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'address_line_1' => 'Kauno g. 15',
        ]);
    }
}
