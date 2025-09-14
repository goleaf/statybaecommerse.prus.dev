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
        $regions = ['Europe', 'Asia', 'Africa', 'North America', 'South America', 'Oceania', 'Antarctica'];
        $currencies = ['EUR', 'USD', 'GBP', 'JPY', 'CHF', 'CAD', 'AUD', 'CNY', 'RUB', 'INR'];
        
        return [
            'name' => $this->faker->country(),
            'name_official' => $this->faker->optional(0.7)->country(),
            'description' => $this->faker->optional(0.6)->paragraph(),
            'cca2' => $this->faker->unique()->countryCode(),
            'cca3' => $this->faker->unique()->countryISOAlpha3(),
            'ccn3' => $this->faker->optional(0.8)->numerify('###'),
            'code' => $this->faker->optional(0.5)->lexify('???'),
            'iso_code' => $this->faker->optional(0.5)->lexify('???'),
            'currency_code' => $this->faker->randomElement($currencies),
            'currency_symbol' => $this->faker->optional(0.7)->randomElement(['€', '$', '£', '¥', 'CHF', 'C$', 'A$', '¥', '₽', '₹']),
            'phone_code' => $this->faker->optional(0.8)->numerify('###'),
            'phone_calling_code' => $this->faker->optional(0.8)->numerify('###'),
            'flag' => $this->faker->optional(0.6)->lexify('??.png'),
            'svg_flag' => $this->faker->optional(0.4)->lexify('??.svg'),
            'region' => $this->faker->randomElement($regions),
            'subregion' => $this->faker->optional(0.7)->randomElement(['Northern Europe', 'Western Europe', 'Eastern Europe', 'Southern Europe', 'Central Asia', 'Eastern Asia', 'Southeast Asia', 'Southern Asia', 'Western Asia', 'Northern Africa', 'Western Africa', 'Eastern Africa', 'Middle Africa', 'Southern Africa', 'Northern America', 'Central America', 'Caribbean', 'South America', 'Australia and New Zealand', 'Melanesia', 'Micronesia', 'Polynesia']),
            'latitude' => $this->faker->optional(0.8)->latitude(),
            'longitude' => $this->faker->optional(0.8)->longitude(),
            'currencies' => $this->faker->optional(0.6)->randomElements([
                'EUR' => 'Euro',
                'USD' => 'US Dollar',
                'GBP' => 'British Pound',
                'JPY' => 'Japanese Yen',
                'CHF' => 'Swiss Franc',
                'CAD' => 'Canadian Dollar',
                'AUD' => 'Australian Dollar',
                'CNY' => 'Chinese Yuan',
                'RUB' => 'Russian Ruble',
                'INR' => 'Indian Rupee',
            ], $this->faker->numberBetween(1, 3)),
            'languages' => $this->faker->optional(0.7)->randomElements([
                'en' => 'English',
                'lt' => 'Lithuanian',
                'lv' => 'Latvian',
                'et' => 'Estonian',
                'de' => 'German',
                'fr' => 'French',
                'es' => 'Spanish',
                'it' => 'Italian',
                'pt' => 'Portuguese',
                'ru' => 'Russian',
                'zh' => 'Chinese',
                'ja' => 'Japanese',
                'ko' => 'Korean',
                'ar' => 'Arabic',
                'hi' => 'Hindi',
            ], $this->faker->numberBetween(1, 3)),
            'timezones' => $this->faker->optional(0.6)->randomElements([
                'Europe/Vilnius' => 'Vilnius Time',
                'Europe/London' => 'London Time',
                'Europe/Paris' => 'Paris Time',
                'Europe/Berlin' => 'Berlin Time',
                'Europe/Rome' => 'Rome Time',
                'Europe/Madrid' => 'Madrid Time',
                'America/New_York' => 'New York Time',
                'America/Los_Angeles' => 'Los Angeles Time',
                'Asia/Tokyo' => 'Tokyo Time',
                'Asia/Shanghai' => 'Shanghai Time',
                'Asia/Kolkata' => 'Kolkata Time',
                'Australia/Sydney' => 'Sydney Time',
            ], $this->faker->numberBetween(1, 2)),
            'timezone' => $this->faker->optional(0.7)->timezone(),
            'is_active' => true,
            'is_enabled' => true,
            'is_eu_member' => $this->faker->boolean(20),
            'requires_vat' => $this->faker->boolean(60),
            'vat_rate' => $this->faker->optional(0.7)->randomFloat(2, 0, 30),
            'metadata' => $this->faker->optional(0.4)->randomElements([
                'population' => $this->faker->numberBetween(100000, 1000000000),
                'area' => $this->faker->numberBetween(1000, 10000000),
                'capital' => $this->faker->city(),
                'government' => $this->faker->randomElement(['Republic', 'Monarchy', 'Federation', 'Confederation']),
                'independence_year' => $this->faker->numberBetween(1800, 2020),
            ], $this->faker->numberBetween(1, 3)),
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
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

    public function euMember(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_eu_member' => true,
        ]);
    }

    public function nonEuMember(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_eu_member' => false,
        ]);
    }

    public function withVat(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_vat' => true,
            'vat_rate' => $this->faker->randomFloat(2, 5, 30),
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
            'currency_code' => 'EUR',
            'is_eu_member' => $this->faker->boolean(70),
        ]);
    }

    public function asian(): static
    {
        return $this->state(fn (array $attributes) => [
            'region' => 'Asia',
            'currency_code' => $this->faker->randomElement(['CNY', 'JPY', 'KRW', 'INR', 'THB']),
        ]);
    }

    public function american(): static
    {
        return $this->state(fn (array $attributes) => [
            'region' => $this->faker->randomElement(['North America', 'South America']),
            'currency_code' => $this->faker->randomElement(['USD', 'CAD', 'BRL', 'ARS', 'CLP']),
        ]);
    }

    public function african(): static
    {
        return $this->state(fn (array $attributes) => [
            'region' => 'Africa',
            'currency_code' => $this->faker->randomElement(['ZAR', 'EGP', 'NGN', 'KES', 'MAD']),
        ]);
    }

    public function oceania(): static
    {
        return $this->state(fn (array $attributes) => [
            'region' => 'Oceania',
            'currency_code' => $this->faker->randomElement(['AUD', 'NZD', 'FJD', 'PGK']),
        ]);
    }

    public function withCoordinates(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
        ]);
    }

    public function withFlag(): static
    {
        return $this->state(fn (array $attributes) => [
            'flag' => $this->faker->lexify('??.png'),
            'svg_flag' => $this->faker->lexify('??.svg'),
        ]);
    }

    public function withTranslations(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Lithuania',
            'name_official' => 'Republic of Lithuania',
            'description' => 'A country in Northern Europe',
        ]);
    }
}