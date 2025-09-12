<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Country;
use App\Models\Region;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Region>
 */
class RegionFactory extends Factory
{
    protected $model = Region::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'slug' => $this->faker->slug(),
            'code' => strtoupper($this->faker->lexify('??-??')),
            'description' => $this->faker->sentence(),
            'country_id' => Country::factory(),
            'zone_id' => Zone::factory(),
            'parent_id' => null,
            'level' => $this->faker->numberBetween(0, 3),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_enabled' => $this->faker->boolean(80),
            'is_default' => false,
            'metadata' => [
                'type' => $this->faker->randomElement(['state', 'province', 'county', 'district']),
                'area' => $this->faker->randomFloat(2, 100, 10000),
            ],
        ];
    }

    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => true,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function withParent(Region $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'level' => $parent->level + 1,
        ]);
    }
}
