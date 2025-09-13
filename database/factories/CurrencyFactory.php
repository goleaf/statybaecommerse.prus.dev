<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Currency>
 */
final class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        $currencies = [
            ['code' => 'EUR', 'symbol' => '€', 'name' => 'Euro', 'exchange_rate' => 1.0],
            ['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar', 'exchange_rate' => 0.85],
            ['code' => 'GBP', 'symbol' => '£', 'name' => 'British Pound', 'exchange_rate' => 1.15],
            ['code' => 'JPY', 'symbol' => '¥', 'name' => 'Japanese Yen', 'exchange_rate' => 0.0065],
            ['code' => 'CAD', 'symbol' => 'C$', 'name' => 'Canadian Dollar', 'exchange_rate' => 0.65],
        ];

        $currency = fake()->randomElement($currencies);
        
        // Add a random suffix to make the code unique for testing
        $uniqueCode = $currency['code'] . '_' . fake()->unique()->randomNumber(3);

        return [
            'name' => $currency['name'],
            'code' => $uniqueCode,
            'symbol' => $currency['symbol'],
            'exchange_rate' => $currency['exchange_rate'],
            'decimal_places' => fake()->numberBetween(0, 4),
            'is_enabled' => fake()->boolean(80), // 80% chance of being enabled
            'is_default' => false, // Will be set manually in tests
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

    public function eur(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Euro',
            'code' => 'EUR',
            'symbol' => '€',
            'exchange_rate' => 1.0,
            'decimal_places' => 2,
        ]);
    }

    public function usd(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'exchange_rate' => 0.85,
            'decimal_places' => 2,
        ]);
    }
}
