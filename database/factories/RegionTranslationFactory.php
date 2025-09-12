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
            'locale' => $this->faker->randomElement(['lt', 'en', 'de', 'ru']),
            'name' => $this->faker->city.' County',
            'description' => $this->faker->sentence(),
        ];
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
            'name' => $this->faker->city.' apskritis',
            'description' => $this->faker->sentence(),
        ]);
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'name' => $this->faker->city.' County',
            'description' => $this->faker->sentence(),
        ]);
    }

    public function german(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'de',
            'name' => $this->faker->city.' Bezirk',
            'description' => $this->faker->sentence(),
        ]);
    }

    public function russian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'ru',
            'name' => $this->faker->city.' округ',
            'description' => $this->faker->sentence(),
        ]);
    }
}
