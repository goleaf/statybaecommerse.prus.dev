<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'code' => $this->faker->unique()->regexify('[A-Z0-9]{8}'),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['percentage', 'fixed']),
            'value' => $this->faker->randomFloat(2, 5, 50),
            'minimum_amount' => $this->faker->randomFloat(2, 10, 100),
            'maximum_discount' => $this->faker->randomFloat(2, 20, 200),
            'usage_limit' => $this->faker->numberBetween(10, 1000),
            'usage_limit_per_user' => $this->faker->numberBetween(1, 5),
            'used_count' => 0,
            'is_active' => $this->faker->boolean(80),
            'starts_at' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'expires_at' => $this->faker->dateTimeBetween('+1 month', '+6 months'),
            'applicable_products' => null,
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
            'type' => 'percentage',
            'value' => $this->faker->numberBetween(5, 50),
        ]);
    }

    /**
     * Indicate that the coupon is a fixed amount discount.
     */
    public function fixed(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fixed',
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
            'used_count' => $usageLimit,
        ]);
    }
}
