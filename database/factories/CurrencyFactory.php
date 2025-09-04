<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        $currencies = [
            ['name' => 'Euro', 'code' => 'EUR', 'symbol' => '€'],
            ['name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$'],
            ['name' => 'British Pound', 'code' => 'GBP', 'symbol' => '£'],
            ['name' => 'Japanese Yen', 'code' => 'JPY', 'symbol' => '¥'],
        ];

        $currency = $this->faker->randomElement($currencies);

        return [
            'name' => $currency['name'],
            'code' => $currency['code'],
            'symbol' => $currency['symbol'],
            'exchange_rate' => $this->faker->randomFloat(4, 0.5, 2.0),
            'is_enabled' => true,
        ];
    }

    public function euro(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Euro',
            'code' => 'EUR',
            'symbol' => '€',
            'exchange_rate' => 1.0000,
        ]);
    }

    public function usd(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'exchange_rate' => 1.1000,
        ]);
    }
}
