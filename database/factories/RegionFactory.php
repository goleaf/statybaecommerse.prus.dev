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
final class RegionFactory extends Factory
{
    protected $model = Region::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city().' Region',
            'code' => $this->faker->unique()->lexify('REG???'),
            'slug' => $this->faker->unique()->slug(2),
            'description' => $this->faker->optional(0.8)->paragraph(),
            'country_id' => Country::factory(),
            'zone_id' => $this->faker->optional(0.6)->randomElement([Zone::factory(), null]),
            'parent_id' => $this->faker->optional(0.3)->randomElement([Region::factory(), null]),
            'level' => $this->faker->numberBetween(0, 5),
            'is_enabled' => $this->faker->boolean(85),
            'is_default' => $this->faker->boolean(10),
            'sort_order' => $this->faker->numberBetween(0, 100),
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
            'is_enabled' => true,
        ]);
    }

    public function level(int $level): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => $level,
        ]);
    }

    public function withParent(?Region $parent = null): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent?->id ?? Region::factory(),
        ]);
    }

    public function withCountry(?Country $country = null): static
    {
        return $this->state(fn (array $attributes) => [
            'country_id' => $country?->id ?? Country::factory(),
        ]);
    }

    public function withZone(?Zone $zone = null): static
    {
        return $this->state(fn (array $attributes) => [
            'zone_id' => $zone?->id ?? Zone::factory(),
        ]);
    }

    public function withCode(string $code): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => $code,
        ]);
    }

    public function withDescription(string $description): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $description,
        ]);
    }

    public function stateProvince(): static
    {
        return $this->level(1)->state(fn (array $attributes) => [
            'name' => $this->faker->state().' State',
        ]);
    }

    public function county(): static
    {
        return $this->level(2)->state(fn (array $attributes) => [
            'name' => $this->faker->city().' County',
        ]);
    }

    public function district(): static
    {
        return $this->level(3)->state(fn (array $attributes) => [
            'name' => $this->faker->city().' District',
        ]);
    }

    public function municipality(): static
    {
        return $this->level(4)->state(fn (array $attributes) => [
            'name' => $this->faker->city().' Municipality',
        ]);
    }

    public function village(): static
    {
        return $this->level(5)->state(fn (array $attributes) => [
            'name' => $this->faker->city().' Village',
        ]);
    }
}
