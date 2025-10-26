<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CouponApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_apply_coupon(): void
    {
        $coupon = Coupon::factory()->create([
            'code'                 => 'SAVE10',
            'type'                 => 'percentage',
            'value'                => 10,
            'starts_at'            => now()->subDay(),
            'expires_at'           => now()->addDay(),
            'minimum_amount'       => 50,
            'usage_limit'          => null,
            'usage_limit_per_user' => null,
        ]);

        $response = $this->postJson(route('frontend.discounts.apply-coupon'), [
            'code' => 'save10',
            'cart' => [
                'subtotal' => 150,
            ],
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('coupon.code', $coupon->code);
        $this->assertEquals($coupon->code, session('checkout.coupon.code'));
        $this->assertSame(15.0, session('checkout.coupon.discount_amount'));
    }

    public function test_rejects_invalid_coupon(): void
    {
        $response = $this->postJson(route('frontend.discounts.apply-coupon'), [
            'code' => 'INVALID',
            'cart' => [
                'subtotal' => 200,
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $this->assertNull(session('checkout.coupon'));
    }

    public function test_can_remove_coupon(): void
    {
        Coupon::factory()->create([
            'code'                 => 'REMOVE20',
            'type'                 => 'fixed',
            'value'                => 20,
            'starts_at'            => now()->subDay(),
            'expires_at'           => now()->addDay(),
            'minimum_amount'       => 10,
            'usage_limit'          => null,
            'usage_limit_per_user' => null,
        ]);

        $this->postJson(route('frontend.discounts.apply-coupon'), [
            'code' => 'REMOVE20',
            'cart' => [
                'subtotal' => 120,
            ],
        ])->assertOk();

        $this->assertEquals('REMOVE20', session('checkout.coupon.code'));

        $response = $this->postJson(route('frontend.discounts.remove-coupon'));

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertNull(session('checkout.coupon'));
    }
}
