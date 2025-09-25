<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Location;
use App\Models\Translations\LocationTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

final class LocationTranslationFactory extends Factory
{
    protected $model = LocationTranslation::class;

    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'locale' => fake()->randomElement(['en', 'lt']),
            'name' => fake()->company().' Location',
            'description' => fake()->paragraph(),
            'slug' => fake()->slug(),
        ];
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'name' => fake()->company().' Location',
            'description' => fake()->paragraph(),
            'slug' => fake()->slug(),
        ]);
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
            'name' => fake()->company().' Vieta',
            'description' => fake()->paragraph(),
            'slug' => fake()->slug(),
        ]);
    }
}
