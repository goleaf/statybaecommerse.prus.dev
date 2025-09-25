<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PartnerTier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PartnerTier>
 */
final class PartnerTierFactory extends Factory
{
    protected $model = PartnerTier::class;

    public function definition(): array
    {
        $tierNames = ['Bronze', 'Silver', 'Gold', 'Platinum', 'Diamond'];
        $tierName = $this->faker->randomElement($tierNames);

        return [
            'name' => $tierName,
            'code' => Str::of($tierName)->slug('_').'_'.$this->faker->unique()->numerify('###'),
            'discount_rate' => $this->faker->randomFloat(4, 0.01, 0.15), // 1% to 15%
            'commission_rate' => $this->faker->randomFloat(4, 0.02, 0.10), // 2% to 10%
            'minimum_order_value' => $this->faker->randomFloat(2, 100, 5000),
            'is_enabled' => $this->faker->boolean(80), // 80% chance of being enabled
            'benefits' => [
                [
                    'key' => 'Priority Support',
                    'value' => '24/7 dedicated support team',
                ],
                [
                    'key' => 'Marketing Materials',
                    'value' => 'Access to exclusive marketing resources',
                ],
                [
                    'key' => 'Training',
                    'value' => 'Monthly training sessions and webinars',
                ],
            ],
        ];
    }

    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => true,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
        ]);
    }

    public function withHighDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_rate' => $this->faker->randomFloat(4, 0.10, 0.20), // 10% to 20%
        ]);
    }
}
