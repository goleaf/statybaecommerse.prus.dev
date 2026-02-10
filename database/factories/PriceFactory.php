<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PriceFactory extends Factory
{
    protected $model = \App\Models\Price::class;

    public function definition(): array
    {
        $amount = $this->faker->numberBetween(500, 25000) / 100;
        $cost = $this->faker->boolean(50) ? $amount * $this->faker->randomFloat(2, 0.5, 0.9) : null;

        return [
            'priceable_type' => 'Product',
            'priceable_id'   => fn () => \App\Models\Product::factory(),
            'currency_id'    => fn () => 1, // Default currency
            'amount'         => round($amount, 2),
            'cost_amount'    => $cost ? round($cost, 2) : null,
            'metadata'       => null,
        ];
    }
}
