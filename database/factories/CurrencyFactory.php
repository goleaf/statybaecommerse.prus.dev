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
        $code = $this->generateCurrencyCode();
        $isoNumeric = $this->nextIsoNumericCode();

        $sequenceOrder = max(0, static::$codeSequence - 1);

        $symbols = ['€', '$', '£', '¥', '₿'];
        $symbolPositionOptions = ['before', 'after'];
        $thousandsSeparators = [',', ' ', '.'];
        $decimalSeparators = ['.', ','];

        return [
            'name'                => sprintf('%s Currency', $code),
            'code'                => $code,
            'iso_code'            => sprintf('%s-%s', $code, $isoNumeric),
            'symbol'              => $symbols[array_rand($symbols)],
            'exchange_rate'       => round(mt_rand(20, 250) / 100, 4),
            'base_currency'       => 'EUR',
            'decimal_places'      => random_int(0, 4),
            'symbol_position'     => $symbolPositionOptions[array_rand($symbolPositionOptions)],
            'thousands_separator' => $thousandsSeparators[array_rand($thousandsSeparators)],
            'decimal_separator'   => $decimalSeparators[array_rand($decimalSeparators)],
            'is_active'           => true,
            'is_enabled'          => true,
            'is_default'          => false,
            'sort_order'          => $sequenceOrder,
            'auto_update_rate'    => false,
            'description'         => sprintf('Auto-generated currency %s', $code),
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

    private static int $codeSequence = 0;

    private static int $isoNumericSequence = 1;

    private function generateCurrencyCode(): string
    {
        $sequence = static::$codeSequence++ % (26 ** 3);

        $code = '';

        for ($index = 0; $index < 3; $index++) {
            $code = chr(ord('A') + ($sequence % 26)) . $code;
            $sequence = intdiv($sequence, 26);
        }

        return $code;
    }

    private function nextIsoNumericCode(): string
    {
        $value = static::$isoNumericSequence++ % 1000;

        if ($value === 0) {
            $value = static::$isoNumericSequence++ % 1000;
        }

        if ($value === 0) {
            $value = 1;
        }

        return str_pad((string) $value, 3, '0', STR_PAD_LEFT);
    }
}
