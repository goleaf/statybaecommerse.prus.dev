<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomerGroup>
 */
final class CustomerGroupFactory extends Factory
{
    protected $model = CustomerGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'                 => $this->faker->words(2, true),
            'code'                 => strtoupper($this->faker->bothify('??##')),
            'color'                => $this->faker->hexColor(),
            'icon'                 => $this->faker->randomElement(['users', 'star', 'crown', 'sparkles']),
            'description'          => $this->faker->sentence(),
            'slug'                 => $this->faker->slug(),
            'discount_percentage'  => $this->faker->randomFloat(2, 0, 50),
            'discount_fixed'       => $this->faker->randomFloat(2, 0, 200),
            'minimum_order_amount' => $this->faker->randomFloat(2, 0, 5000),
            'credit_limit'         => $this->faker->randomFloat(2, 0, 20000),
            'payment_terms'        => $this->faker->randomElement(['due_on_receipt', 'net_15', 'net_30', 'net_45', 'net_60']),
            'has_special_pricing'  => $this->faker->boolean(30),
            'has_volume_discounts' => $this->faker->boolean(40),
            'can_view_prices'      => $this->faker->boolean(80),
            'can_place_orders'     => $this->faker->boolean(90),
            'can_view_catalog'     => $this->faker->boolean(85),
            'can_use_coupons'      => $this->faker->boolean(70),
            'is_active'            => $this->faker->boolean(90),
            'is_default'           => false,
            'sort_order'           => $this->faker->numberBetween(1, 100),
            'type'                 => $this->faker->randomElement(['regular', 'vip', 'corporate', 'retail', 'wholesale']),
            'is_enabled'           => $this->faker->boolean(80),
            'metadata'             => [],
            'conditions'           => [],
        ];
    }

    /**
     * Indicate that the customer group is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active'  => true,
            'is_enabled' => true,
        ]);
    }

    /**
     * Indicate that the customer group is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active'  => false,
            'is_enabled' => false,
        ]);
    }

    /**
     * Indicate that the customer group is the default.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
            'is_active'  => true,
            'is_enabled' => true,
        ]);
    }
}
