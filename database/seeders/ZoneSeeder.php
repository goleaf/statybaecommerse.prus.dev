<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Zone;
use App\Models\Translations\ZoneTranslation;
use Illuminate\Database\Seeder;

class ZoneSeeder extends Seeder
{
    public function run(): void
    {
        // First, ensure we have currencies
        $this->createCurrencies();

        // Get currencies for zones
        $eur = Currency::where('code', 'EUR')->first();
        $usd = Currency::where('code', 'USD')->first();
        $gbp = Currency::where('code', 'GBP')->first();

        $locales = $this->supportedLocales();
        
        // Create zones with translations
        $zones = [
            [
                'slug' => 'european-union',
                'code' => 'EU',
                'is_enabled' => true,
                'is_default' => true,
                'currency_id' => $eur->id,
                'tax_rate' => 21.0,
                'shipping_rate' => 5.99,
                'sort_order' => 1,
                'metadata' => [
                    'region' => 'europe',
                    'tax_included' => true,
                    'free_shipping_threshold' => 50.0,
                ],
                'translations' => [
                    'lt' => [
                        'name' => 'Europos Sąjunga',
                        'description' => 'Europos Sąjungos šalių zona su EUR valiuta',
                    ],
                    'en' => [
                        'name' => 'European Union',
                        'description' => 'European Union countries zone with EUR currency',
                    ],
                ]
            ],
            [
                'slug' => 'north-america',
                'code' => 'NA',
                'is_enabled' => true,
                'is_default' => false,
                'currency_id' => $usd->id,
                'tax_rate' => 8.5,
                'shipping_rate' => 12.99,
                'sort_order' => 2,
                'metadata' => [
                    'region' => 'north_america',
                    'tax_included' => false,
                    'free_shipping_threshold' => 75.0,
                ],
                'translations' => [
                    'lt' => [
                        'name' => 'Šiaurės Amerika',
                        'description' => 'Šiaurės Amerikos šalių zona su USD valiuta',
                    ],
                    'en' => [
                        'name' => 'North America',
                        'description' => 'North American countries zone with USD currency',
                    ],
                ]
            ],
            [
                'slug' => 'united-kingdom',
                'code' => 'UK',
                'is_enabled' => true,
                'is_default' => false,
                'currency_id' => $gbp->id,
                'tax_rate' => 20.0,
                'shipping_rate' => 8.99,
                'sort_order' => 3,
                'metadata' => [
                    'region' => 'uk',
                    'tax_included' => true,
                    'free_shipping_threshold' => 40.0,
                ],
                'translations' => [
                    'lt' => [
                        'name' => 'Jungtinė Karalystė',
                        'description' => 'Jungtinės Karalystės zona su GBP valiuta',
                    ],
                    'en' => [
                        'name' => 'United Kingdom',
                        'description' => 'United Kingdom zone with GBP currency',
                    ],
                ]
            ],
            [
                'slug' => 'lithuania',
                'code' => 'LT',
                'is_enabled' => true,
                'is_default' => false,
                'currency_id' => $eur->id,
                'tax_rate' => 21.0,
                'shipping_rate' => 3.99,
                'sort_order' => 0,
                'metadata' => [
                    'region' => 'lithuania',
                    'tax_included' => true,
                    'free_shipping_threshold' => 30.0,
                    'local_delivery' => true,
                ],
                'translations' => [
                    'lt' => [
                        'name' => 'Lietuva',
                        'description' => 'Lietuvos zona su EUR valiuta ir mažesniais pristatymo mokesčiais',
                    ],
                    'en' => [
                        'name' => 'Lithuania',
                        'description' => 'Lithuania zone with EUR currency and lower shipping costs',
                    ],
                ]
            ]
        ];

        foreach ($zones as $zoneData) {
            $translations = $zoneData['translations'] ?? [];
            unset($zoneData['translations']);
            
            // Set a default name from translations or use code as fallback
            $defaultName = $translations['en']['name'] ?? $zoneData['code'];
            
            $zone = Zone::updateOrCreate(
                ['code' => $zoneData['code']],
                [
                    'name' => $defaultName,
                    'slug' => $zoneData['slug'],
                    'is_enabled' => $zoneData['is_enabled'],
                    'is_default' => $zoneData['is_default'],
                    'currency_id' => $zoneData['currency_id'],
                    'tax_rate' => $zoneData['tax_rate'],
                    'shipping_rate' => $zoneData['shipping_rate'],
                    'sort_order' => $zoneData['sort_order'],
                    'metadata' => $zoneData['metadata']
                ]
            );

            // Create translations for each locale
            foreach ($locales as $locale) {
                $translationData = $translations[$locale] ?? [];
                ZoneTranslation::updateOrCreate([
                    'zone_id' => $zone->id,
                    'locale' => $locale,
                ], [
                    'name' => $translationData['name'] ?? 'Zone',
                    'description' => $translationData['description'] ?? '',
                ]);
            }

            // Attach countries to zones by region/codes
            $countryCodes = match ($zoneData['code']) {
                'EU' => [
                    'LT', 'LV', 'EE', 'PL', 'DE', 'FR', 'NL', 'BE', 'ES', 'IT', 'AT', 'CH', 'CZ', 'SK', 'HU', 'RO', 'BG', 'HR', 'SI', 'SE', 'NO', 'DK', 'FI', 'GB'
                ],
                'NA' => ['US', 'CA', 'MX'],
                'UK' => ['GB'],
                'LT' => ['LT'],
                default => [],
            };

            if (!empty($countryCodes)) {
                $countryIds = Country::query()
                    ->whereIn('cca2', $countryCodes)
                    ->pluck('id')
                    ->all();

                if (!empty($countryIds)) {
                    $zone->countries()->syncWithoutDetaching($countryIds);
                }
            }

            $zoneName = $translations['en']['name'] ?? $translations['lt']['name'] ?? 'Zone';
            $this->command->info("Upserted zone: {$zoneData['code']} - {$zoneName} (countries: " . implode(',', $countryCodes) . ')');
        }

        $this->command->info('Zone seeding completed successfully with translations (locales: ' . implode(',', $locales) . ')!');
    }

    private function supportedLocales(): array
    {
        return collect(explode(',', (string) config('app.supported_locales', 'lt,en')))
            ->map(fn($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    private function createCurrencies(): void
    {
        $currencies = [
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'symbol' => '€',
                'exchange_rate' => 1.0,
                'is_default' => true,
                'is_enabled' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'exchange_rate' => 1.08,
                'is_default' => false,
                'is_enabled' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'GBP',
                'name' => 'British Pound',
                'symbol' => '£',
                'exchange_rate' => 0.85,
                'is_default' => false,
                'is_enabled' => true,
                'decimal_places' => 2
            ]
        ];

        foreach ($currencies as $currencyData) {
            Currency::firstOrCreate(
                ['code' => $currencyData['code']],
                $currencyData
            );
        }

        $this->command->info('Currencies created/updated successfully!');
    }
}
