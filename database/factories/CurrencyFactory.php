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

    private const PRESET_CURRENCIES = [
        'eur' => [
            'name'           => 'Euro',
            'code'           => 'EUR',
            'symbol'         => '€',
            'exchange_rate'  => 1.0,
            'decimal_places' => 2,
            'is_default'     => true,
        ],
        'usd' => [
            'name'           => 'US Dollar',
            'code'           => 'USD',
            'symbol'         => '$',
            'exchange_rate'  => 1.10,
            'decimal_places' => 2,
        ],
        'gbp' => [
            'name'           => 'British Pound Sterling',
            'code'           => 'GBP',
            'symbol'         => '£',
            'exchange_rate'  => 0.85,
            'decimal_places' => 2,
        ],
        'sek' => [
            'name'           => 'Swedish Krona',
            'code'           => 'SEK',
            'symbol'         => 'kr',
            'exchange_rate'  => 10.50,
            'decimal_places' => 2,
        ],
    ];

    public function definition(): array
    {
        // Generate deterministic yet unique codes so SQLite and MySQL behave consistently during tests.
        $code = strtoupper($this->faker->unique()->lexify('???'));
        $isoNumeric = str_pad((string) $this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT);

        return [
            'name'                => sprintf('%s Currency', $code),
            'code'                => $code,
            'iso_code'            => sprintf('%s-%s', $code, $isoNumeric),
            'symbol'              => $this->faker->randomElement(['€', '$', '£', '¥', '₿']),
            'exchange_rate'       => $this->faker->randomFloat(4, 0.2, 2.5),
            'base_currency'       => 'EUR',
            'decimal_places'      => $this->faker->numberBetween(0, 4),
            'symbol_position'     => $this->faker->randomElement(['before', 'after']),
            'thousands_separator' => $this->faker->randomElement([',', ' ', '.']),
            'decimal_separator'   => $this->faker->randomElement(['.', ',']),
            'is_active'           => true,
            'is_enabled'          => true,
            'is_default'          => false,
            'sort_order'          => $this->faker->numberBetween(0, 100),
            'auto_update_rate'    => false,
            'description'         => $this->faker->sentence(),
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
        return $this->state(fn (array $attributes): array => [
            'is_enabled' => false,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_default' => true,
            'is_enabled' => true,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function eur(): static
    {
        return $this->state(fn (array $attributes): array => $this->preset('eur'));
    }

    public function usd(): static
    {
        return $this->state(fn (array $attributes): array => $this->preset('usd'));
    }

    public function gbp(): static
    {
        return $this->state(fn (array $attributes): array => $this->preset('gbp'));
    }

    public function sek(): static
    {
        return $this->state(fn (array $attributes): array => $this->preset('sek'));
    }

    /**
     * @return array<string, mixed>
     */
    private function preset(string $key): array
    {
        $preset = self::PRESET_CURRENCIES[$key] ?? [];

        return array_merge([
            'is_active'        => true,
            'is_enabled'       => true,
            'sort_order'       => array_search($key, array_keys(self::PRESET_CURRENCIES), true) ?: 0,
            'auto_update_rate' => false,
        ], $preset);
    }
}
