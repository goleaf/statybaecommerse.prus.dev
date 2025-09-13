<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Region;
use App\Models\Translations\RegionTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

final class RegionTranslationFactory extends Factory
{
    protected $model = RegionTranslation::class;

    public function definition(): array
    {
        return [
            'region_id' => Region::factory(),
            'locale' => $this->faker->randomElement(['en', 'lt']),
            'name' => $this->faker->city() . ' Region',
            'description' => $this->faker->paragraph(),
        ];
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'name' => $this->faker->city() . ' Region',
            'description' => $this->faker->paragraph(),
        ]);
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
            'name' => $this->faker->city() . ' Regionas',
            'description' => $this->faker->paragraph(),
        ]);
    }
}