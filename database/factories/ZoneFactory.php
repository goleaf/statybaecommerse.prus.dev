<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Zone>
 */
final class ZoneFactory extends Factory
{
    protected $model = Zone::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['shipping', 'tax', 'payment', 'delivery', 'general'];

        return [
            'name' => fake()->words(2, true),
            'slug' => fake()->slug(),
            'code' => strtoupper(fake()->lexify('???')),
            'description' => fake()->sentence(),
            'currency_id' => Currency::factory(),
            'tax_rate' => fake()->randomFloat(2, 0, 30),
            'shipping_rate' => fake()->randomFloat(2, 0, 20),
            'type' => fake()->randomElement($types),
            'priority' => fake()->numberBetween(0, 10),
            'min_order_amount' => fake()->optional(0.3)->randomFloat(2, 10, 100),
            'max_order_amount' => fake()->optional(0.2)->randomFloat(2, 500, 2000),
            'free_shipping_threshold' => fake()->optional(0.4)->randomFloat(2, 50, 200),
            'is_enabled' => true,
            'is_active' => true,
            'is_default' => false,
            'sort_order' => fake()->numberBetween(0, 100),
            'metadata' => fake()->optional(0.2)->randomElements([
                'featured' => true,
                'special_handling' => false,
                'custom_field' => fake()->word(),
            ]),
        ];
    }

    /**
     * Indicate that the zone is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the zone is enabled.
     */
    public function enabled(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_enabled' => true,
        ]);
    }

    /**
     * Indicate that the zone is the default zone.
     */
    public function default(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_default' => true,
            'is_enabled' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the zone is a shipping zone.
     */
    public function shipping(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'shipping',
        ]);
    }

    /**
     * Indicate that the zone is a tax zone.
     */
    public function tax(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'tax',
        ]);
    }

    /**
     * Indicate that the zone is a payment zone.
     */
    public function payment(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'payment',
        ]);
    }

    /**
     * Indicate that the zone has free shipping.
     */
    public function withFreeShipping(): static
    {
        return $this->state(fn(array $attributes) => [
            'free_shipping_threshold' => $this->faker->randomFloat(2, 50, 200),
        ]);
    }

    /**
     * Indicate that the zone has order amount limits.
     */
    public function withOrderLimits(): static
    {
        return $this->state(fn(array $attributes) => [
            'min_order_amount' => $this->faker->randomFloat(2, 10, 50),
            'max_order_amount' => $this->faker->randomFloat(2, 500, 1000),
        ]);
    }
}
