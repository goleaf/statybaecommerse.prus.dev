<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Country;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\City>
 */
class CityFactory extends Factory
{
    protected $model = City::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->city();
        
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'code' => strtoupper(fake()->lexify('???')),
            'description' => fake()->optional(0.7)->paragraph(),
            'is_enabled' => fake()->boolean(90),
            'is_default' => fake()->boolean(10),
            'is_capital' => fake()->boolean(5),
            'is_active' => fake()->boolean(95),
            'country_id' => Country::factory(),
            'zone_id' => Zone::factory(),
            'level' => fake()->numberBetween(0, 3),
            'latitude' => fake()->optional(0.8)->latitude(),
            'longitude' => fake()->optional(0.8)->longitude(),
            'population' => fake()->optional(0.7)->numberBetween(1000, 10000000),
            'postal_codes' => fake()->optional(0.5)->randomElements(['01001', '01002', '01003'], fake()->numberBetween(1, 3)),
            'sort_order' => fake()->numberBetween(0, 100),
            'metadata' => fake()->optional(0.3)->randomElements([
                'type' => fake()->randomElement(['metropolitan', 'urban', 'rural']),
                'climate' => fake()->randomElement(['continental', 'oceanic', 'mediterranean']),
            ]),
            'type' => fake()->optional(0.4)->randomElement(['metropolitan', 'urban', 'rural', 'suburban']),
            'area' => fake()->optional(0.6)->randomFloat(2, 1, 1000),
            'density' => fake()->optional(0.5)->randomFloat(2, 10, 5000),
            'elevation' => fake()->optional(0.4)->randomFloat(2, -100, 3000),
            'timezone' => fake()->optional(0.8)->randomElement(['Europe/Vilnius', 'Europe/London', 'America/New_York']),
            'currency_code' => fake()->optional(0.7)->currencyCode(),
            'currency_symbol' => fake()->optional(0.7)->randomElement(['€', '$', '£', '¥']),
            'language_code' => fake()->optional(0.6)->randomElement(['lt', 'en', 'de', 'ru']),
            'language_name' => fake()->optional(0.6)->randomElement(['Lithuanian', 'English', 'German', 'Russian']),
            'phone_code' => fake()->optional(0.5)->numerify('+###'),
            'postal_code' => fake()->optional(0.4)->postcode(),
        ];
    }

    /**
     * Indicate that the city is enabled.
     */
    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the city is disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the city is a capital.
     */
    public function capital(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_capital' => true,
            'is_default' => true,
            'population' => fake()->numberBetween(500000, 10000000),
        ]);
    }

    /**
     * Indicate that the city has specific level.
     */
    public function level(int $level): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => $level,
        ]);
    }

    /**
     * Indicate that the city has coordinates.
     */
    public function withCoordinates(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ]);
    }

    /**
     * Indicate that the city has population.
     */
    public function withPopulation(): static
    {
        return $this->state(fn (array $attributes) => [
            'population' => fake()->numberBetween(10000, 1000000),
        ]);
    }
}
