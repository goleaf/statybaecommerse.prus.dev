<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PartnerTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PartnerTier>
 */
class PartnerTierFactory extends Factory
{
    protected $model = PartnerTier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->word() . ' ' . $this->faker->unique()->numerify('####');

        return [
            'name'                 => ucfirst($name),
            'code'                 => strtolower(str_replace(' ', '-', $name)),
            'priority'             => $this->faker->numberBetween(1, 100),
            'default_discount_pct' => $this->faker->randomFloat(2, 0, 50),
            'is_enabled'           => true,
            'discount_rate'        => $this->faker->randomFloat(4, 0, 0.3),
            'commission_rate'      => $this->faker->randomFloat(4, 0, 0.1),
            'metadata'             => [],
        ];
    }
}
