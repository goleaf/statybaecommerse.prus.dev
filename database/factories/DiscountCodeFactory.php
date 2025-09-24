<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CustomerGroup;
use App\Models\DiscountCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DiscountCode>
 */
final class DiscountCodeFactory extends Factory
{
    protected $model = DiscountCode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->bothify('??##??##')),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['percentage', 'fixed', 'free_shipping', 'buy_x_get_y']),
            'value' => $this->faker->randomFloat(2, 1, 100),
            'minimum_amount' => $this->faker->randomFloat(2, 0, 1000),
            'maximum_discount' => $this->faker->randomFloat(2, 0, 500),
            'usage_limit' => $this->faker->numberBetween(1, 1000),
            'usage_limit_per_user' => $this->faker->numberBetween(1, 10),
            'usage_count' => 0,
            'valid_from' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'valid_until' => $this->faker->dateTimeBetween('now', '+1 year'),
            'is_active' => $this->faker->boolean(80),
            'is_public' => $this->faker->boolean(60),
            'is_auto_apply' => $this->faker->boolean(30),
            'is_stackable' => $this->faker->boolean(40),
            'is_first_time_only' => $this->faker->boolean(20),
            'customer_group_id' => CustomerGroup::factory(),
            'status' => $this->faker->randomElement(['active', 'inactive', 'expired', 'scheduled']),
            'metadata' => [],
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the discount code is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the discount code is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the discount code is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'status' => 'expired',
            'valid_until' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    /**
     * Indicate that the discount code is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'status' => 'scheduled',
            'valid_from' => $this->faker->dateTimeBetween('+1 day', '+1 month'),
        ]);
    }

    /**
     * Indicate that the discount code has reached its usage limit.
     */
    public function usageLimitReached(): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_count' => $attributes['usage_limit'] ?? 100,
        ]);
    }

    /**
     * Indicate that the discount code is percentage type.
     */
    public function percentage(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'percentage',
            'value' => $this->faker->numberBetween(1, 50),
        ]);
    }

    /**
     * Indicate that the discount code is fixed type.
     */
    public function fixed(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fixed',
            'value' => $this->faker->randomFloat(2, 1, 100),
        ]);
    }

    /**
     * Indicate that the discount code is free shipping type.
     */
    public function freeShipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'free_shipping',
            'value' => 0,
        ]);
    }
}
