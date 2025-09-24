<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CouponUsage>
 */
final class CouponUsageFactory extends Factory
{
    protected $model = CouponUsage::class;

    public function definition(): array
    {
        return [
            'coupon_id' => Coupon::factory(),
            'user_id' => User::factory(),
            'order_id' => Order::factory(),
            'discount_amount' => fake()->randomFloat(2, 1, 50),
            'used_at' => now(),
            'metadata' => [],
        ];
    }
}


