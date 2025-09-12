<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\City>
 */
class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city(),
            'slug' => $this->faker->slug(),
            'code' => strtoupper($this->faker->lexify('??-???')),
            'description' => $this->faker->sentence(),
            'country_id' => Country::factory(),
            'zone_id' => Zone::factory(),
            'region_id' => Region::factory(),
            'parent_id' => null,
            'level' => $this->faker->numberBetween(0, 3),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'population' => $this->faker->numberBetween(1000, 10000000),
            'postal_codes' => $this->faker->randomElements(['00001', '00002', '00003', '00004', '00005'], 3),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_enabled' => $this->faker->boolean(80),
            'is_default' => false,
            'is_capital' => $this->faker->boolean(10),
            'metadata' => [
                'timezone' => $this->faker->timezone(),
                'area' => $this->faker->randomFloat(2, 10, 1000),
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

    public function capital(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_capital' => true,
        ]);
    }

    public function withParent(City $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'level' => $parent->level + 1,
        ]);
    }
}

