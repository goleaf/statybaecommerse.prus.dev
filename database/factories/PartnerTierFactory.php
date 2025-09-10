<?php

namespace Database\Factories;

use App\Models\PartnerTier;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerTierFactory extends Factory
{
    protected $model = PartnerTier::class;

    public function definition(): array
    {
        $name = $this->faker->randomElement(['Gold', 'Silver', 'Bronze', 'Custom']);
        return [
            'name' => $name,
            'code' => strtolower($name) . '-' . $this->faker->unique()->lexify('???'),
            'discount_rate' => $this->faker->randomFloat(4, 0, 0.5),
            'commission_rate' => $this->faker->randomFloat(4, 0, 0.2),
            'minimum_order_value' => $this->faker->randomFloat(2, 0, 1000),
            'is_enabled' => true,
            'benefits' => ['priority_support' => $this->faker->boolean()],
        ];
    }
}

