<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Coupon;
use App\Models\Discount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DiscountControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_active_discounts(): void
    {
        $discount = Discount::factory()->create([
            'status' => 'active',
            'priority' => 1,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $response = $this->get(route('frontend.discounts.index'));

        $response->assertOk();
        $response->assertViewIs('frontend.discounts.index');
        $response->assertSee($discount->name);
    }

    public function test_apply_coupon_sets_discount_in_session(): void
    {
        $coupon = Coupon::factory()->create([
            'code' => 'SAVE10',
            'type' => 'percentage',
            'value' => 10,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
            'usage_limit' => null,
        ]);

        $response = $this->withSession([
            'cart' => [1 => ['product_id' => 1, 'name' => 'Item', 'price' => 100, 'quantity' => 1, 'sku' => 'SKU-1']],
        ])->post(route('frontend.discounts.apply-coupon'), ['code' => 'SAVE10']);

        $response->assertRedirect(route('frontend.cart.index'));
        $this->assertEquals(10.0, session('cart_discount'));
        $this->assertEquals('SAVE10', session('applied_coupon.code'));
    }

    public function test_apply_coupon_requires_valid_code(): void
    {
        $response = $this->withSession([
            'cart' => [1 => ['product_id' => 1, 'name' => 'Item', 'price' => 50, 'quantity' => 1, 'sku' => 'SKU-1']],
        ])->post(route('frontend.discounts.apply-coupon'), ['code' => 'INVALID']);

        $response->assertSessionHasErrors('code');
    }

    public function test_apply_coupon_requires_cart_items(): void
    {
        $coupon = Coupon::factory()->create([
            'code' => 'SAVE',
            'type' => 'fixed',
            'value' => 5,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
            'usage_limit' => null,
        ]);

        $response = $this->post(route('frontend.discounts.apply-coupon'), ['code' => $coupon->code]);

        $response->assertRedirect(route('frontend.cart.index'));
        $response->assertSessionHasErrors('cart');
    }

    public function test_remove_coupon_clears_session(): void
    {
        $this->withSession([
            'cart_discount' => 5,
            'applied_coupon' => ['code' => 'SAVE10'],
        ])->post(route('frontend.discounts.remove-coupon'));

        $this->assertNull(session('cart_discount'));
        $this->assertNull(session('applied_coupon'));
    }
}
