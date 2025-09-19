<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'name' => [
                'lt' => $this->faker->words(2, true),
                'en' => $this->faker->words(2, true),
            ],
            'code' => strtoupper($this->faker->bothify('??##')),
            'description' => [
                'lt' => $this->faker->sentence(),
                'en' => $this->faker->sentence(),
            ],
            'discount_percentage' => $this->faker->randomFloat(2, 0, 50),
            'has_special_pricing' => $this->faker->boolean(30),
            'has_volume_discounts' => $this->faker->boolean(40),
            'can_view_prices' => $this->faker->boolean(80),
            'can_place_orders' => $this->faker->boolean(90),
            'can_view_catalog' => $this->faker->boolean(85),
            'can_use_coupons' => $this->faker->boolean(70),
            'is_active' => $this->faker->boolean(80),
            'is_default' => false,
            'sort_order' => $this->faker->numberBetween(1, 100),
            'type' => $this->faker->randomElement(['regular', 'vip', 'corporate', 'retail']),
            'is_enabled' => $this->faker->boolean(80),
            'conditions' => [],
        ];
    }

    /**
     * Indicate that the customer group is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
            'is_enabled' => true,
        ]);
    }

    /**
     * Indicate that the customer group is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
            'is_enabled' => false,
        ]);
    }

    /**
     * Indicate that the customer group is the default.
     */
    public function default(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_default' => true,
            'is_active' => true,
            'is_enabled' => true,
        ]);
    }
}
