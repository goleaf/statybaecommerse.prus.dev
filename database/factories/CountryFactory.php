<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Country>
 */
final class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition(): array
    {
        $regions = ['Europe', 'Asia', 'Africa', 'North America', 'South America', 'Oceania'];
        $currencies = ['EUR', 'USD', 'GBP', 'JPY', 'CHF', 'CAD', 'AUD'];
        $currencySymbols = ['€', '$', '£', '¥', 'CHF', 'C$', 'A$'];
        
        $region = fake()->randomElement($regions);
        $currencyIndex = array_rand($currencies);
        $currency = $currencies[$currencyIndex];
        $currencySymbol = $currencySymbols[$currencyIndex];

        return [
            'name' => fake()->country(),
            'name_official' => fake()->country() . ' Republic',
            'cca2' => fake()->unique()->countryCode(),
            'cca3' => fake()->unique()->countryISOAlpha3(),
            'ccn3' => fake()->unique()->numerify('###'),
            'code' => fake()->unique()->countryCode(),
            'iso_code' => fake()->unique()->countryISOAlpha3(),
            'currency_code' => $currency,
            'currency_symbol' => $currencySymbol,
            'phone_code' => fake()->numerify('###'),
            'phone_calling_code' => fake()->numerify('###'),
            'flag' => fake()->word() . '.png',
            'svg_flag' => fake()->word() . '.svg',
            'region' => $region,
            'subregion' => fake()->words(2, true),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'currencies' => [
                $currency => [
                    'name' => fake()->currencyCode(),
                    'symbol' => $currencySymbol,
                ],
            ],
            'languages' => [
                'en' => 'English',
                'lt' => 'Lithuanian',
            ],
            'timezones' => [
                'UTC+2' => 'Eastern European Time',
            ],
            'is_active' => fake()->boolean(80), // 80% chance of being active
            'is_enabled' => fake()->boolean(90), // 90% chance of being enabled
            'is_eu_member' => $region === 'Europe' ? fake()->boolean(30) : false, // 30% chance if European
            'requires_vat' => fake()->boolean(60), // 60% chance of requiring VAT
            'vat_rate' => fake()->boolean(70) ? fake()->randomFloat(2, 0, 30) : null, // 70% chance of having VAT rate
            'timezone' => fake()->timezone(),
            'description' => fake()->paragraph(),
            'metadata' => [
                'population' => fake()->numberBetween(100000, 100000000),
                'area' => fake()->numberBetween(1000, 10000000),
            ],
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'is_enabled' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function euMember(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_eu_member' => true,
            'region' => 'Europe',
            'currency_code' => 'EUR',
            'currency_symbol' => '€',
        ]);
    }

    public function withVat(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_vat' => true,
            'vat_rate' => fake()->randomFloat(2, 5, 25),
        ]);
    }

    public function withoutVat(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_vat' => false,
            'vat_rate' => null,
        ]);
    }

    public function european(): static
    {
        return $this->state(fn (array $attributes) => [
            'region' => 'Europe',
            'subregion' => fake()->randomElement(['Northern Europe', 'Southern Europe', 'Eastern Europe', 'Western Europe']),
        ]);
    }

    public function asian(): static
    {
        return $this->state(fn (array $attributes) => [
            'region' => 'Asia',
            'subregion' => fake()->randomElement(['Eastern Asia', 'Southern Asia', 'Southeast Asia', 'Western Asia']),
        ]);
    }

    public function withCoordinates(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ]);
    }

    public function withFlag(): static
    {
        return $this->state(fn (array $attributes) => [
            'flag' => fake()->word() . '.png',
            'svg_flag' => fake()->word() . '.svg',
        ]);
    }
}