<?php

namespace Database\Factories;

use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Zone>
 */
class ZoneFactory extends Factory
{
    protected $model = Zone::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Europe',
                'North America',
                'Asia Pacific',
                'Middle East',
                'Africa',
                'South America',
                'Central America',
                'Caribbean',
                'Oceania',
                'Arctic',
            ]),
            'code' => $this->faker->unique()->regexify('[A-Z]{2,4}'),
            'is_enabled' => $this->faker->boolean(80),
            'currency_id' => 1, // Default to EUR
        ];
    }

    /**
     * Indicate that the zone is enabled.
     */
    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => true,
        ]);
    }

    /**
     * Indicate that the zone is disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
        ]);
    }
}
