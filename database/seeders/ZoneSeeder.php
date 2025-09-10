<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Country;
use App\Models\Zone;
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
        
        // Create zones with translations
        $zones = [
            [
                'name' => ['lt' => 'Europos Sąjunga', 'en' => 'European Union'],
                'slug' => 'european-union',
                'code' => 'EU',
                'is_enabled' => true,
                'is_default' => true,
                'currency_id' => $eur->id,
                'tax_rate' => 21.0000,
                'shipping_rate' => 5.99,
                'sort_order' => 1,
                'metadata' => [
                    'region' => 'europe',
                    'tax_included' => true,
                    'free_shipping_threshold' => 50.00,
                    'description' => [
                        'lt' => 'Europos Sąjungos šalių zona su EUR valiuta',
                        'en' => 'European Union countries zone with EUR currency'
                    ]
                ]
            ],
            [
                'name' => ['lt' => 'Šiaurės Amerika', 'en' => 'North America'],
                'slug' => 'north-america',
                'code' => 'NA',
                'is_enabled' => true,
                'is_default' => false,
                'currency_id' => $usd->id,
                'tax_rate' => 8.5000,
                'shipping_rate' => 12.99,
                'sort_order' => 2,
                'metadata' => [
                    'region' => 'north_america',
                    'tax_included' => false,
                    'free_shipping_threshold' => 75.00,
                    'description' => [
                        'lt' => 'Šiaurės Amerikos šalių zona su USD valiuta',
                        'en' => 'North American countries zone with USD currency'
                    ]
                ]
            ],
            [
                'name' => ['lt' => 'Jungtinė Karalystė', 'en' => 'United Kingdom'],
                'slug' => 'united-kingdom',
                'code' => 'UK',
                'is_enabled' => true,
                'is_default' => false,
                'currency_id' => $gbp->id,
                'tax_rate' => 20.0000,
                'shipping_rate' => 8.99,
                'sort_order' => 3,
                'metadata' => [
                    'region' => 'uk',
                    'tax_included' => true,
                    'free_shipping_threshold' => 40.00,
                    'description' => [
                        'lt' => 'Jungtinės Karalystės zona su GBP valiuta',
                        'en' => 'United Kingdom zone with GBP currency'
                    ]
                ]
            ],
            [
                'name' => ['lt' => 'Lietuva', 'en' => 'Lithuania'],
                'slug' => 'lithuania',
                'code' => 'LT',
                'is_enabled' => true,
                'is_default' => false,
                'currency_id' => $eur->id,
                'tax_rate' => 21.0000,
                'shipping_rate' => 3.99,
                'sort_order' => 0,
                'metadata' => [
                    'region' => 'lithuania',
                    'tax_included' => true,
                    'free_shipping_threshold' => 30.00,
                    'local_delivery' => true,
                    'description' => [
                        'lt' => 'Lietuvos zona su EUR valiuta ir mažesniais pristatymo mokesčiais',
                        'en' => 'Lithuania zone with EUR currency and lower shipping costs'
                    ]
                ]
            ]
        ];

        foreach ($zones as $zoneData) {
            $zone = Zone::updateOrCreate(
                ['code' => $zoneData['code']],
                [
                    'name' => $zoneData['name'],
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

            // Attach countries to zones by region/codes
            $countryCodes = match ($zoneData['code']) {
                'EU' => [
                    'LT','LV','EE','PL','DE','FR','NL','BE','ES','IT','AT','CH','CZ','SK','HU','RO','BG','HR','SI','SE','NO','DK','FI','GB'
                ],
                'NA' => ['US','CA','MX'],
                'UK' => ['GB'],
                'LT' => ['LT'],
                default => [],
            };

            if (! empty($countryCodes)) {
                $countryIds = Country::query()
                    ->whereIn('cca2', $countryCodes)
                    ->pluck('id')
                    ->all();

                if (! empty($countryIds)) {
                    $zone->countries()->syncWithoutDetaching($countryIds);
                }
            }

            $this->command->info("Upserted zone: {$zoneData['code']} - {$zoneData['name']['en']} (countries: " . implode(',', $countryCodes) . ")");
        }

        $this->command->info('Zone seeding completed successfully!');
    }

    private function createCurrencies(): void
    {
        $currencies = [
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'symbol' => '€',
                'exchange_rate' => 1.0000,
                'is_default' => true,
                'is_enabled' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'exchange_rate' => 1.0800,
                'is_default' => false,
                'is_enabled' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'GBP',
                'name' => 'British Pound',
                'symbol' => '£',
                'exchange_rate' => 0.8500,
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
