<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Country;
use App\Models\ShippingOption;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShippingOption>
 */
final class ShippingOptionFactory extends Factory
{
    protected $model = ShippingOption::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'               => $this->faker->words(3, true),
            'slug'               => Str::slug($this->faker->words(3, true)),
            'description'        => $this->faker->optional()->sentence(),
            'carrier_name'       => $this->faker->randomElement(['DPD', 'Omniva', 'Lietuvos Paštas', 'DHL', 'LP Express']),
            'service_type'       => $this->faker->randomElement(['Standard', 'Express', 'Same Day', 'Economy']),
            'price'              => $this->faker->randomFloat(2, 0, 15),
            'currency_code'      => 'EUR',
            'country_id'         => Country::factory(),
            'is_enabled'         => $this->faker->boolean(80),
            'is_default'         => false,
            'sort_order'         => $this->faker->numberBetween(0, 100),
            'min_weight'         => $this->faker->optional()->numberBetween(0, 5),
            'max_weight'         => $this->faker->optional()->numberBetween(10, 50),
            'min_order_amount'   => $this->faker->optional()->randomFloat(2, 0, 50),
            'max_order_amount'   => null,
            'estimated_days_min' => $this->faker->numberBetween(1, 3),
            'estimated_days_max' => $this->faker->numberBetween(3, 7),
        ];
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'name'         => 'Free Shipping',
            'carrier_name' => 'Lietuvos Paštas',
            'service_type' => 'Standard',
            'price'        => 0.00,
        ]);
    }
}
