<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
final class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Generate deterministic uppercase alpha-numeric code without relying on deprecated faker helpers.
            'code' => Str::upper(Str::random(8)),
            // Provide predictable text fields to keep tests deterministic across Faker versions.
            'name'                 => 'Seasonal Coupon ' . Str::upper(Str::random(4)),
            'description'          => 'Automatically generated coupon for testing scenarios.',
            'type'                 => 'percentage',
            'value'                => 10.00,
            'minimum_amount'       => null,
            'maximum_discount'     => null,
            'usage_limit'          => 100,
            'usage_limit_per_user' => 1,
            'used_count'           => 0,
            'is_active'            => true,
            'is_public'            => false,
            'is_auto_apply'        => false,
            'is_stackable'         => false,
            'is_first_time_only'   => false,
            'customer_group_id'    => null,
            // Default validity window keeps generated coupons immediately usable for tests and seed data.
            'starts_at'             => now()->subWeek(),
            'expires_at'            => now()->addMonths(3),
            'applicable_products'   => null,
            'applicable_categories' => null,
        ];
    }

    /**
     * Indicate that the coupon is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the coupon is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the coupon is a percentage discount.
     */
    public function percentage(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'  => 'percentage',
            'value' => $this->faker->numberBetween(5, 50),
        ]);
    }

    /**
     * Indicate that the coupon is a fixed amount discount.
     */
    public function fixed(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'  => 'fixed',
            'value' => $this->faker->randomFloat(2, 5, 100),
        ]);
    }

    /**
     * Indicate that the coupon is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }

    /**
     * Indicate that the coupon is not yet started.
     */
    public function notStarted(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => $this->faker->dateTimeBetween('+1 day', '+1 month'),
        ]);
    }

    /**
     * Indicate that the coupon has reached its usage limit.
     */
    public function usageLimitReached(): static
    {
        $usageLimit = $this->faker->numberBetween(10, 100);

        return $this->state(fn (array $attributes) => [
            'usage_limit' => $usageLimit,
            'used_count'  => $usageLimit,
        ]);
    }
}
