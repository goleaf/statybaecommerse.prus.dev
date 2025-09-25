<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

final class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'translations' => [
                    'en' => 'Euro',
                    'lt' => 'Euras',
                ],
                'code' => 'EUR',
                'symbol' => '€',
                'exchange_rate' => 1.0,
                'is_default' => true,
                'is_enabled' => true,
                'decimal_places' => 2,
            ],
            [
                'translations' => [
                    'en' => 'US Dollar',
                    'lt' => 'JAV doleris',
                ],
                'code' => 'USD',
                'symbol' => '$',
                'exchange_rate' => 1.1,
                'is_default' => false,
                'is_enabled' => true,
                'decimal_places' => 2,
            ],
            [
                'translations' => [
                    'en' => 'British Pound Sterling',
                    'lt' => 'Svaras sterlingų',
                ],
                'code' => 'GBP',
                'symbol' => '£',
                'exchange_rate' => 0.85,
                'is_default' => false,
                'is_enabled' => true,
                'decimal_places' => 2,
            ],
            [
                'translations' => [
                    'en' => 'Japanese Yen',
                    'lt' => 'Japonijos jena',
                ],
                'code' => 'JPY',
                'symbol' => '¥',
                'exchange_rate' => 130.0,
                'is_default' => false,
                'is_enabled' => true,
                'decimal_places' => 0,
            ],
            [
                'translations' => [
                    'en' => 'Swiss Franc',
                    'lt' => 'Šveicarijos frankas',
                ],
                'code' => 'CHF',
                'symbol' => 'CHF',
                'exchange_rate' => 1.05,
                'is_default' => false,
                'is_enabled' => true,
                'decimal_places' => 2,
            ],
            [
                'translations' => [
                    'en' => 'Canadian Dollar',
                    'lt' => 'Kanados doleris',
                ],
                'code' => 'CAD',
                'symbol' => 'C$',
                'exchange_rate' => 1.35,
                'is_default' => false,
                'is_enabled' => true,
                'decimal_places' => 2,
            ],
            [
                'translations' => [
                    'en' => 'Australian Dollar',
                    'lt' => 'Australijos doleris',
                ],
                'code' => 'AUD',
                'symbol' => 'A$',
                'exchange_rate' => 1.45,
                'is_default' => false,
                'is_enabled' => true,
                'decimal_places' => 2,
            ],
            [
                'translations' => [
                    'en' => 'Chinese Yuan',
                    'lt' => 'Kinijos juanis',
                ],
                'code' => 'CNY',
                'symbol' => '¥',
                'exchange_rate' => 7.2,
                'is_default' => false,
                'is_enabled' => true,
                'decimal_places' => 2,
            ],
            [
                'translations' => [
                    'en' => 'Swedish Krona',
                    'lt' => 'Švedijos krona',
                ],
                'code' => 'SEK',
                'symbol' => 'kr',
                'exchange_rate' => 10.5,
                'is_default' => false,
                'is_enabled' => true,
                'decimal_places' => 2,
            ],
            [
                'translations' => [
                    'en' => 'Norwegian Krone',
                    'lt' => 'Norvegijos krona',
                ],
                'code' => 'NOK',
                'symbol' => 'kr',
                'exchange_rate' => 10.2,
                'is_default' => false,
                'is_enabled' => true,
                'decimal_places' => 2,
            ],
            [
                'translations' => [
                    'en' => 'Danish Krone',
                    'lt' => 'Danijos krona',
                ],
                'code' => 'DKK',
                'symbol' => 'kr',
                'exchange_rate' => 7.45,
                'is_default' => false,
                'is_enabled' => true,
                'decimal_places' => 2,
            ],
            [
                'translations' => [
                    'en' => 'Polish Zloty',
                    'lt' => 'Lenkijos zlotas',
                ],
                'code' => 'PLN',
                'symbol' => 'zł',
                'exchange_rate' => 4.3,
                'is_default' => false,
                'is_enabled' => true,
                'decimal_places' => 2,
            ],
            [
                'translations' => [
                    'en' => 'Czech Koruna',
                    'lt' => 'Čekijos krona',
                ],
                'code' => 'CZK',
                'symbol' => 'Kč',
                'exchange_rate' => 24.5,
                'is_default' => false,
                'is_enabled' => true,
                'decimal_places' => 2,
            ],
            [
                'translations' => [
                    'en' => 'Hungarian Forint',
                    'lt' => 'Vengrijos forintas',
                ],
                'code' => 'HUF',
                'symbol' => 'Ft',
                'exchange_rate' => 350.0,
                'is_default' => false,
                'is_enabled' => true,
                'decimal_places' => 0,
            ],
            [
                'translations' => [
                    'en' => 'Russian Ruble',
                    'lt' => 'Rusijos rublis',
                ],
                'code' => 'RUB',
                'symbol' => '₽',
                'exchange_rate' => 75.0,
                'is_default' => false,
                'is_enabled' => false,  // Disabled due to sanctions
                'decimal_places' => 2,
            ],
        ];

        $locales = $this->supportedLocales();

        foreach ($currencies as $currencyData) {
            $translations = $currencyData['translations'] ?? [];
            unset($currencyData['translations']);
            
            // Set translatable name field using Spatie's HasTranslations
            $currencyData['name'] = $translations;

            // Check if currency already exists to maintain idempotency
            $existingCurrency = Currency::withoutGlobalScopes()->where('code', $currencyData['code'])->first();
            
            if (!$existingCurrency) {
                /** @var Currency $currency */
                $currency = Currency::factory()
                    ->state($currencyData)
                    ->create();
            }
        }

        $this->command->info('Currency seeder completed successfully!');
        $this->command->info('Created '.count($currencies).' currencies with translations (locales: '.implode(',', $locales).').');
    }

    private function supportedLocales(): array
    {
        return collect(explode(',', (string) config('app.supported_locales', 'lt,en')))
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}
