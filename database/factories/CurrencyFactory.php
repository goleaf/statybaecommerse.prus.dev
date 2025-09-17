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
			['code' => 'EUR', 'symbol' => '€', 'name' => 'Euro', 'exchange_rate' => 1.00, 'decimal_places' => 2],
			['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar', 'exchange_rate' => 0.85, 'decimal_places' => 2],
			['code' => 'GBP', 'symbol' => '£', 'name' => 'British Pound', 'exchange_rate' => 1.15, 'decimal_places' => 2],
			['code' => 'JPY', 'symbol' => '¥', 'name' => 'Japanese Yen', 'exchange_rate' => 0.0065, 'decimal_places' => 0],
			['code' => 'CAD', 'symbol' => 'C$', 'name' => 'Canadian Dollar', 'exchange_rate' => 0.65, 'decimal_places' => 2],
		];

		$currency = fake()->randomElement($currencies);

		return [
			'name' => $currency['name'],
			'code' => $currency['code'],
			'symbol' => $currency['symbol'],
			'exchange_rate' => $currency['exchange_rate'],
			'decimal_places' => $currency['decimal_places'],
			'is_enabled' => true,
			'is_default' => $currency['code'] === 'EUR',
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
			'exchange_rate' => 1.00,
			'decimal_places' => 2,
			'is_default' => true,
			'is_enabled' => true,
		]);
	}
}
