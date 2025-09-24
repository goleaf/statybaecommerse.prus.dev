<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CustomerGroup;
use App\Models\ProductVariant;
use App\Models\VariantPricingRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VariantPricingRule>
 */
final class VariantPricingRuleFactory extends Factory
{
    protected $model = VariantPricingRule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'type' => $this->faker->randomElement(['percentage', 'fixed', 'tier', 'bulk']),
            'value' => $this->faker->randomFloat(2, 1, 100),
            'product_variant_id' => ProductVariant::factory(),
            'customer_group_id' => CustomerGroup::factory(),
            'min_quantity' => $this->faker->numberBetween(1, 10),
            'max_quantity' => $this->faker->numberBetween(100, 1000),
            'priority' => $this->faker->numberBetween(1, 10),
            'is_active' => $this->faker->boolean(80),
            'is_cumulative' => $this->faker->boolean(30),
            'valid_from' => $this->faker->optional(0.7)->dateTimeBetween('-1 month', '+1 month'),
            'valid_until' => $this->faker->optional(0.5)->dateTimeBetween('+1 month', '+1 year'),
            'description' => $this->faker->optional(0.6)->sentence(),
        ];
    }

    /**
     * Indicate that the pricing rule is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the pricing rule is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the pricing rule is cumulative.
     */
    public function cumulative(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_cumulative' => true,
        ]);
    }

    /**
     * Indicate that the pricing rule is percentage type.
     */
    public function percentage(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'percentage',
            'value' => $this->faker->numberBetween(1, 50),
        ]);
    }

    /**
     * Indicate that the pricing rule is fixed type.
     */
    public function fixed(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fixed',
            'value' => $this->faker->randomFloat(2, 1, 100),
        ]);
    }

    /**
     * Indicate that the pricing rule is tier type.
     */
    public function tier(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'tier',
            'value' => $this->faker->numberBetween(1, 20),
        ]);
    }

    /**
     * Indicate that the pricing rule is bulk type.
     */
    public function bulk(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'bulk',
            'value' => $this->faker->numberBetween(1, 15),
        ]);
    }

    /**
     * Indicate that the pricing rule has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $this->faker->numberBetween(8, 10),
        ]);
    }

    /**
     * Indicate that the pricing rule has low priority.
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $this->faker->numberBetween(1, 3),
        ]);
    }

    /**
     * Indicate that the pricing rule is currently valid.
     */
    public function currentlyValid(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'valid_until' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
        ]);
    }

    /**
     * Indicate that the pricing rule is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => $this->faker->dateTimeBetween('-2 months', '-1 month'),
            'valid_until' => $this->faker->dateTimeBetween('-1 month', '-1 week'),
        ]);
    }

    /**
     * Indicate that the pricing rule is future.
     */
    public function future(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'valid_until' => $this->faker->dateTimeBetween('+2 months', '+3 months'),
        ]);
    }
}
