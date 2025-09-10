<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        return [
            'name' => ['en' => 'Currency ' . $this->faker->unique()->lexify('???'), 'lt' => 'Valiuta ' . $this->faker->unique()->lexify('???')],
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'symbol' => '¤',
            'exchange_rate' => $this->faker->randomFloat(6, 0.5, 2.0),
            'is_default' => false,
            'is_enabled' => $this->faker->boolean(80),  // 80% chance of being enabled
            'decimal_places' => $this->faker->numberBetween(0, 4),
        ];
    }

    public function euro(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => ['en' => 'Euro', 'lt' => 'Euras'],
            'code' => 'EUR',
            'symbol' => '€',
            'exchange_rate' => 1.0,
            'is_default' => true,
            'is_enabled' => true,
            'decimal_places' => 2,
        ]);
    }

    public function usd(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => ['en' => 'US Dollar', 'lt' => 'JAV doleris'],
            'code' => 'USD',
            'symbol' => '$',
            'exchange_rate' => 1.1,
            'is_default' => false,
            'is_enabled' => true,
            'decimal_places' => 2,
        ]);
    }

    public function enabled(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_enabled' => true,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_enabled' => false,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_default' => true,
        ]);
    }
}


