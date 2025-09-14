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
            'locale' => $this->faker->randomElement(['en', 'lt']),
            'name' => $this->faker->company() . ' Location',
            'description' => $this->faker->paragraph(),
            'slug' => $this->faker->slug(),
        ];
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'name' => $this->faker->company() . ' Location',
            'description' => $this->faker->paragraph(),
            'slug' => $this->faker->slug(),
        ]);
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
            'name' => $this->faker->company() . ' Vieta',
            'description' => $this->faker->paragraph(),
            'slug' => $this->faker->slug(),
        ]);
    }
}
