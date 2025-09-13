<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Country;
use App\Models\Translations\CountryTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translations\CountryTranslation>
 */
final class CountryTranslationFactory extends Factory
{
    protected $model = CountryTranslation::class;

    public function definition(): array
    {
        return [
            'country_id' => Country::factory(),
            'locale' => $this->faker->randomElement(['en', 'lt', 'de', 'fr', 'es', 'it']),
            'name' => $this->faker->country(),
            'name_official' => $this->faker->optional(0.7)->country(),
            'description' => $this->faker->optional(0.6)->paragraph(),
        ];
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
        ]);
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
        ]);
    }

    public function german(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'de',
        ]);
    }

    public function french(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'fr',
        ]);
    }

    public function spanish(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'es',
        ]);
    }

    public function italian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'it',
        ]);
    }
}