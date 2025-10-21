<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Currency;
use App\Models\PriceList;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\PriceList>
 */
final class PriceListFactory extends Factory
{
    protected $model = PriceList::class;

    public function definition(): array
    {
        $startsAt = $this->faker->optional(0.65)->dateTimeBetween('-1 month', '+1 month');
        $endsAt = $startsAt
            ? $this->faker->optional(0.55)->dateTimeBetween($startsAt, '+6 months')
            : null;

        $name = 'Kainoraštis ' . $this->faker->words(2, true);

        return [
            'name'        => $name,
            'code'        => Str::upper($this->faker->unique()->bothify('PL-####')),
            'currency_id' => Currency::factory()->eur(),
            'is_enabled'  => $this->faker->boolean(85),
            'priority'    => $this->faker->numberBetween(1, 100),
            'starts_at'   => $startsAt,
            'ends_at'     => $endsAt,
            'description' => $this->faker->paragraph(),
            'metadata'    => [
                'source'  => $this->faker->randomElement(['manual', 'import', 'sync']),
                'segment' => $this->faker->randomElement(['retail', 'b2b', 'vip']),
            ],
            'is_default'       => false,
            'auto_apply'       => $this->faker->boolean(30),
            'min_order_amount' => $this->faker->optional(0.35)->randomFloat(2, 50, 500),
            'max_order_amount' => $this->faker->optional(0.35)->randomFloat(2, 800, 5000),
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
            'auto_apply' => true,
        ]);
    }
}
