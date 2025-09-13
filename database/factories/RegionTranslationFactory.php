<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Region;
use App\Models\Translations\RegionTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translations\RegionTranslation>
 */
final class RegionTranslationFactory extends Factory
{
    protected $model = RegionTranslation::class;

    public function definition(): array
    {
        return [
            'region_id' => Region::factory(),
            'locale' => $this->faker->randomElement(['en', 'lt']),
            'name' => $this->faker->city() . ' Region',
            'description' => $this->faker->optional(0.8)->paragraph(),
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

    public function forRegion(Region $region): static
    {
        return $this->state(fn (array $attributes) => [
            'region_id' => $region->id,
        ]);
    }

    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }

    public function withDescription(string $description): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $description,
        ]);
    }
}