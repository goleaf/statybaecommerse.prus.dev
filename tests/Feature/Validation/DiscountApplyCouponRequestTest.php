<?php

declare(strict_types=1);

namespace Tests\Feature\Validation;

use Tests\TestCase;

final class DiscountApplyCouponRequestTest extends TestCase
{
    public function test_apply_coupon_requires_code_returns_422(): void
    {
        $this->postJson('/discounts/apply-coupon', [])
            ->assertStatus(422);
    }
}
