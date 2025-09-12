<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Country>
 */
class CountryFactory extends Factory
{
    protected $model = Country::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->country(),
            'cca2' => $this->faker->unique()->countryCode(),
            'cca3' => $this->faker->unique()->countryISOAlpha3(),
            'is_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the country is disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
        ]);
    }

    /**
     * Create a specific country.
     */
    public function lithuania(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Lithuania',
            'cca2' => 'LT',
            'cca3' => 'LTU',
        ]);
    }

    /**
     * Create a specific country.
     */
    public function latvia(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Latvia',
            'cca2' => 'LV',
            'cca3' => 'LVA',
        ]);
    }

    /**
     * Create a specific country.
     */
    public function estonia(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Estonia',
            'cca2' => 'EE',
            'cca3' => 'EST',
        ]);
    }
}
