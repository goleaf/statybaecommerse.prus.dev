<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Zone;
use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ZoneFactory extends Factory
{
    protected $model = Zone::class;

    public function definition(): array
    {
        return [
            'name' => [
                'lt' => $this->faker->country(),
                'en' => $this->faker->country(),
            ],
            'slug' => $this->faker->unique()->slug(),
            'code' => $this->faker->unique()->countryCode(),
            'description' => [
                'lt' => $this->faker->sentence(),
                'en' => $this->faker->sentence(),
            ],
            'is_enabled' => true,
            'is_default' => false,
            'currency_id' => Currency::factory(),
            'tax_rate' => $this->faker->randomFloat(2, 0, 25),
            'shipping_rate' => $this->faker->randomFloat(2, 0, 50),
            'sort_order' => $this->faker->numberBetween(1, 100),
            'metadata' => [],
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
        ]);
    }
}


