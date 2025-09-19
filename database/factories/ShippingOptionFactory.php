<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShippingOption>
 */
class ShippingOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $carriers = ['DHL', 'FedEx', 'UPS', 'PostNord', 'DPD', 'GLS'];
        $services = ['Standard', 'Express', 'Overnight', 'Economy', 'Priority'];
        $carrier = fake()->randomElement($carriers);
        $service = fake()->randomElement($services);

        return [
            'name' => $carrier . ' ' . $service,
            'slug' => fake()->unique()->slug(2),
            'description' => fake()->sentence(),
            'carrier_name' => $carrier,
            'service_type' => $service,
            'price' => fake()->randomFloat(2, 5, 50),
            'currency_code' => 'EUR',
            'zone_id' => \App\Models\Zone::factory(),
            'is_enabled' => true,
            'is_default' => false,
            'sort_order' => fake()->numberBetween(0, 100),
            'min_weight' => fake()->optional(0.3)->numberBetween(0, 5),
            'max_weight' => fake()->optional(0.3)->numberBetween(10, 100),
            'min_order_amount' => fake()->optional(0.2)->randomFloat(2, 0, 50),
            'max_order_amount' => fake()->optional(0.2)->randomFloat(2, 100, 1000),
            'estimated_days_min' => fake()->numberBetween(1, 3),
            'estimated_days_max' => fake()->numberBetween(3, 7),
            'metadata' => null,
        ];
    }

    public function enabled(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_enabled' => true,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_enabled' => false,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_default' => true,
            'sort_order' => 0,
        ]);
    }

    public function free(): static
    {
        return $this->state(fn(array $attributes) => [
            'price' => 0,
            'name' => 'Free Shipping',
            'carrier_name' => 'Standard',
            'service_type' => 'Free',
        ]);
    }
}
