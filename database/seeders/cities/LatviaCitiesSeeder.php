<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class LatviaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $latvia = Country::where('cca2', 'LV')->first();
        $euZone = Zone::where('code', 'EU')->first();

        // Regions are no longer used in the database schema

        $cities = [
            // Riga region
            [
                'name' => 'Riga',
                'code' => 'LV-RI-RIG',
                'is_capital' => true,
                'is_default' => true,
                'latitude' => 56.9496,
                'longitude' => 24.1052,
                'population' => 614618,
                'postal_codes' => ['LV-1001', 'LV-1002', 'LV-1003'],
                'translations' => [
                    'lt' => ['name' => 'Ryga', 'description' => 'Latvijos sostinė'],
                    'en' => ['name' => 'Riga', 'description' => 'Capital of Latvia'],
                ],
            ],
            [
                'name' => 'Jurmala',
                'code' => 'LV-RI-JUR',
                'latitude' => 56.968,
                'longitude' => 23.7703,
                'population' => 57409,
                'postal_codes' => ['LV-2015'],
                'translations' => [
                    'lt' => ['name' => 'Jūrmala', 'description' => 'Kurortinis miestas'],
                    'en' => ['name' => 'Jurmala', 'description' => 'Resort city'],
                ],
            ],
            // Kurzeme region
            [
                'name' => 'Liepaja',
                'code' => 'LV-KU-LIE',
                'latitude' => 56.5084,
                'longitude' => 21.0132,
                'population' => 67964,
                'postal_codes' => ['LV-3401'],
                'translations' => [
                    'lt' => ['name' => 'Liepoja', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'Liepaja', 'description' => 'Seaside city'],
                ],
            ],
            [
                'name' => 'Ventspils',
                'code' => 'LV-KU-VEN',
                'latitude' => 57.3937,
                'longitude' => 21.5647,
                'population' => 34420,
                'postal_codes' => ['LV-3601'],
                'translations' => [
                    'lt' => ['name' => 'Ventspils', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Ventspils', 'description' => 'Port city'],
                ],
            ],
            // Latgale region
            [
                'name' => 'Daugavpils',
                'code' => 'LV-LG-DAU',
                'latitude' => 55.8752,
                'longitude' => 26.5362,
                'population' => 82946,
                'postal_codes' => ['LV-5401'],
                'translations' => [
                    'lt' => ['name' => 'Daugpilis', 'description' => 'Antrasis didžiausias Latvijos miestas'],
                    'en' => ['name' => 'Daugavpils', 'description' => 'Second largest city in Latvia'],
                ],
            ],
            [
                'name' => 'Rezekne',
                'code' => 'LV-LG-REZ',
                'latitude' => 56.5103,
                'longitude' => 27.3319,
                'population' => 25694,
                'postal_codes' => ['LV-4601'],
                'translations' => [
                    'lt' => ['name' => 'Rezekne', 'description' => 'Latgalios centras'],
                    'en' => ['name' => 'Rezekne', 'description' => 'Center of Latgale'],
                ],
            ],
            // Vidzeme region
            [
                'name' => 'Valmiera',
                'code' => 'LV-VI-VAL',
                'latitude' => 57.5408,
                'longitude' => 25.4275,
                'population' => 23556,
                'postal_codes' => ['LV-4201'],
                'translations' => [
                    'lt' => ['name' => 'Valmiera', 'description' => 'Vidžemos centras'],
                    'en' => ['name' => 'Valmiera', 'description' => 'Center of Vidzeme'],
                ],
            ],
            // Zemgale region
            [
                'name' => 'Jelgava',
                'code' => 'LV-ZM-JEL',
                'latitude' => 56.6511,
                'longitude' => 23.7214,
                'population' => 55897,
                'postal_codes' => ['LV-3001'],
                'translations' => [
                    'lt' => ['name' => 'Jelgava', 'description' => 'Žemgalos centras'],
                    'en' => ['name' => 'Jelgava', 'description' => 'Center of Zemgale'],
                ],
            ],
            [
                'name' => 'Bauska',
                'code' => 'LV-ZM-BAU',
                'latitude' => 56.4069,
                'longitude' => 24.1908,
                'population' => 10000,
                'postal_codes' => ['LV-3901'],
                'translations' => [
                    'lt' => ['name' => 'Bauska', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Bauska', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Dobele',
                'code' => 'LV-ZM-DOB',
                'latitude' => 56.6258,
                'longitude' => 23.2789,
                'population' => 9000,
                'postal_codes' => ['LV-3701'],
                'translations' => [
                    'lt' => ['name' => 'Dobele', 'description' => 'Žemės ūkio centras'],
                    'en' => ['name' => 'Dobele', 'description' => 'Agricultural center'],
                ],
            ],
            [
                'name' => 'Jēkabpils',
                'code' => 'LV-ZM-JEK',
                'latitude' => 56.4997,
                'longitude' => 25.8572,
                'population' => 23000,
                'postal_codes' => ['LV-5201'],
                'translations' => [
                    'lt' => ['name' => 'Jēkabpils', 'description' => 'Upės miestas'],
                    'en' => ['name' => 'Jēkabpils', 'description' => 'River city'],
                ],
            ],
            [
                'name' => 'Ogre',
                'code' => 'LV-ZM-OGR',
                'latitude' => 56.8181,
                'longitude' => 24.6053,
                'population' => 25000,
                'postal_codes' => ['LV-5001'],
                'translations' => [
                    'lt' => ['name' => 'Ogre', 'description' => 'Rygo priemiestis'],
                    'en' => ['name' => 'Ogre', 'description' => 'Riga suburb'],
                ],
            ],
            // Additional Riga region cities
            [
                'name' => 'Sigulda',
                'code' => 'LV-RI-SIG',
                'latitude' => 57.1522,
                'longitude' => 24.8647,
                'population' => 16000,
                'postal_codes' => ['LV-2150'],
                'translations' => [
                    'lt' => ['name' => 'Sigulda', 'description' => 'Kurortinis miestas'],
                    'en' => ['name' => 'Sigulda', 'description' => 'Resort town'],
                ],
            ],
            [
                'name' => 'Cesis',
                'code' => 'LV-RI-CES',
                'latitude' => 57.3128,
                'longitude' => 25.2744,
                'population' => 16000,
                'postal_codes' => ['LV-4101'],
                'translations' => [
                    'lt' => ['name' => 'Cesis', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Cesis', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Salaspils',
                'code' => 'LV-RI-SAL',
                'latitude' => 56.8589,
                'longitude' => 24.3583,
                'population' => 18000,
                'postal_codes' => ['LV-2121'],
                'translations' => [
                    'lt' => ['name' => 'Salaspils', 'description' => 'Rygo priemiestis'],
                    'en' => ['name' => 'Salaspils', 'description' => 'Riga suburb'],
                ],
            ],
            [
                'name' => 'Olaine',
                'code' => 'LV-RI-OLA',
                'latitude' => 56.7856,
                'longitude' => 23.9389,
                'population' => 12000,
                'postal_codes' => ['LV-2114'],
                'translations' => [
                    'lt' => ['name' => 'Olaine', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Olaine', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Baldone',
                'code' => 'LV-RI-BAL',
                'latitude' => 56.7425,
                'longitude' => 24.3958,
                'population' => 5000,
                'postal_codes' => ['LV-2125'],
                'translations' => [
                    'lt' => ['name' => 'Baldone', 'description' => 'Kurortinis miestas'],
                    'en' => ['name' => 'Baldone', 'description' => 'Resort town'],
                ],
            ],
            [
                'name' => 'Kekava',
                'code' => 'LV-RI-KEK',
                'latitude' => 56.8286,
                'longitude' => 24.2375,
                'population' => 25000,
                'postal_codes' => ['LV-2123'],
                'translations' => [
                    'lt' => ['name' => 'Kekava', 'description' => 'Rygo priemiestis'],
                    'en' => ['name' => 'Kekava', 'description' => 'Riga suburb'],
                ],
            ],
            // Additional Kurzeme region cities
            [
                'name' => 'Kuldiga',
                'code' => 'LV-KU-KUL',
                'latitude' => 56.9667,
                'longitude' => 21.9833,
                'population' => 11000,
                'postal_codes' => ['LV-3301'],
                'translations' => [
                    'lt' => ['name' => 'Kuldiga', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Kuldiga', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Talsi',
                'code' => 'LV-KU-TAL',
                'latitude' => 57.2431,
                'longitude' => 22.5936,
                'population' => 10000,
                'postal_codes' => ['LV-3201'],
                'translations' => [
                    'lt' => ['name' => 'Talsi', 'description' => 'Ežerų miestas'],
                    'en' => ['name' => 'Talsi', 'description' => 'City of lakes'],
                ],
            ],
            [
                'name' => 'Saldus',
                'code' => 'LV-KU-SAL',
                'latitude' => 56.6667,
                'longitude' => 22.4833,
                'population' => 11000,
                'postal_codes' => ['LV-3801'],
                'translations' => [
                    'lt' => ['name' => 'Saldus', 'description' => 'Žemės ūkio centras'],
                    'en' => ['name' => 'Saldus', 'description' => 'Agricultural center'],
                ],
            ],
            [
                'name' => 'Dundaga',
                'code' => 'LV-KU-DUN',
                'latitude' => 57.5167,
                'longitude' => 22.3500,
                'population' => 3000,
                'postal_codes' => ['LV-3270'],
                'translations' => [
                    'lt' => ['name' => 'Dundaga', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Dundaga', 'description' => 'Small town'],
                ],
            ],
            [
                'name' => 'Grobiņa',
                'code' => 'LV-KU-GRO',
                'latitude' => 56.5333,
                'longitude' => 21.1667,
                'population' => 4000,
                'postal_codes' => ['LV-3430'],
                'translations' => [
                    'lt' => ['name' => 'Grobiņa', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Grobiņa', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Aizpute',
                'code' => 'LV-KU-AIZ',
                'latitude' => 56.7167,
                'longitude' => 21.6000,
                'population' => 5000,
                'postal_codes' => ['LV-3456'],
                'translations' => [
                    'lt' => ['name' => 'Aizpute', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Aizpute', 'description' => 'Historic city'],
                ],
            ],
            // Additional Latgale region cities
            [
                'name' => 'Ludza',
                'code' => 'LV-LG-LUD',
                'latitude' => 56.5417,
                'longitude' => 27.7194,
                'population' => 8000,
                'postal_codes' => ['LV-5701'],
                'translations' => [
                    'lt' => ['name' => 'Ludza', 'description' => 'Sienos miestas'],
                    'en' => ['name' => 'Ludza', 'description' => 'Border town'],
                ],
            ],
            [
                'name' => 'Kraslava',
                'code' => 'LV-LG-KRA',
                'latitude' => 55.8944,
                'longitude' => 27.1667,
                'population' => 9000,
                'postal_codes' => ['LV-5601'],
                'translations' => [
                    'lt' => ['name' => 'Kraslava', 'description' => 'Sienos miestas'],
                    'en' => ['name' => 'Kraslava', 'description' => 'Border town'],
                ],
            ],
            [
                'name' => 'Preili',
                'code' => 'LV-LG-PRE',
                'latitude' => 56.2944,
                'longitude' => 26.7250,
                'population' => 7000,
                'postal_codes' => ['LV-5301'],
                'translations' => [
                    'lt' => ['name' => 'Preili', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Preili', 'description' => 'Small town'],
                ],
            ],
            [
                'name' => 'Balvi',
                'code' => 'LV-LG-BAL',
                'latitude' => 57.1333,
                'longitude' => 27.2667,
                'population' => 6000,
                'postal_codes' => ['LV-4501'],
                'translations' => [
                    'lt' => ['name' => 'Balvi', 'description' => 'Šiaurės Latgalios centras'],
                    'en' => ['name' => 'Balvi', 'description' => 'Northern Latgale center'],
                ],
            ],
            [
                'name' => 'Vilaka',
                'code' => 'LV-LG-VIL',
                'latitude' => 57.1833,
                'longitude' => 27.6833,
                'population' => 1500,
                'postal_codes' => ['LV-4584'],
                'translations' => [
                    'lt' => ['name' => 'Vilaka', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Vilaka', 'description' => 'Small town'],
                ],
            ],
            [
                'name' => 'Zilupe',
                'code' => 'LV-LG-ZIL',
                'latitude' => 56.3833,
                'longitude' => 28.1167,
                'population' => 1500,
                'postal_codes' => ['LV-5751'],
                'translations' => [
                    'lt' => ['name' => 'Zilupe', 'description' => 'Sienos miestas'],
                    'en' => ['name' => 'Zilupe', 'description' => 'Border town'],
                ],
            ],
            // Additional Vidzeme region cities
            [
                'name' => 'Cesis',
                'code' => 'LV-VI-CES',
                'latitude' => 57.3128,
                'longitude' => 25.2744,
                'population' => 16000,
                'postal_codes' => ['LV-4101'],
                'translations' => [
                    'lt' => ['name' => 'Cesis', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Cesis', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Gulbene',
                'code' => 'LV-VI-GUL',
                'latitude' => 57.1778,
                'longitude' => 26.7528,
                'population' => 8000,
                'postal_codes' => ['LV-4401'],
                'translations' => [
                    'lt' => ['name' => 'Gulbene', 'description' => 'Geležinkelio mazgas'],
                    'en' => ['name' => 'Gulbene', 'description' => 'Railway junction'],
                ],
            ],
            [
                'name' => 'Madona',
                'code' => 'LV-VI-MAD',
                'latitude' => 56.8500,
                'longitude' => 26.2167,
                'population' => 8000,
                'postal_codes' => ['LV-4801'],
                'translations' => [
                    'lt' => ['name' => 'Madona', 'description' => 'Vidžemos centras'],
                    'en' => ['name' => 'Madona', 'description' => 'Vidzeme center'],
                ],
            ],
            [
                'name' => 'Aluksne',
                'code' => 'LV-VI-ALU',
                'latitude' => 57.4167,
                'longitude' => 27.0500,
                'population' => 7000,
                'postal_codes' => ['LV-4301'],
                'translations' => [
                    'lt' => ['name' => 'Aluksne', 'description' => 'Ežerų miestas'],
                    'en' => ['name' => 'Aluksne', 'description' => 'City of lakes'],
                ],
            ],
            [
                'name' => 'Smiltene',
                'code' => 'LV-VI-SMI',
                'latitude' => 57.4333,
                'longitude' => 25.9000,
                'population' => 5000,
                'postal_codes' => ['LV-4729'],
                'translations' => [
                    'lt' => ['name' => 'Smiltene', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Smiltene', 'description' => 'Small town'],
                ],
            ],
            [
                'name' => 'Valka',
                'code' => 'LV-VI-VAL',
                'latitude' => 57.7833,
                'longitude' => 26.0167,
                'population' => 5000,
                'postal_codes' => ['LV-4701'],
                'translations' => [
                    'lt' => ['name' => 'Valka', 'description' => 'Sienos miestas'],
                    'en' => ['name' => 'Valka', 'description' => 'Border town'],
                ],
            ],
            [
                'name' => 'Strenci',
                'code' => 'LV-VI-STR',
                'latitude' => 57.6167,
                'longitude' => 25.7167,
                'population' => 1200,
                'postal_codes' => ['LV-4730'],
                'translations' => [
                    'lt' => ['name' => 'Strenci', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Strenci', 'description' => 'Small town'],
                ],
            ],
        ];

        foreach ($cities as $cityData) {
            $city = City::updateOrCreate(
                ['code' => $cityData['code']],
                [
                    'name' => $cityData['name'],
                    'slug' => \Str::slug($cityData['name'].'-'.$cityData['code']),
                    'is_enabled' => true,
                    'is_default' => $cityData['is_default'] ?? false,
                    'is_capital' => $cityData['is_capital'] ?? false,
                    'country_id' => $latvia->id,
                    'zone_id' => $euZone?->id,
                    'level' => 1,
                    'latitude' => $cityData['latitude'],
                    'longitude' => $cityData['longitude'],
                    'population' => $cityData['population'],
                    'postal_codes' => $cityData['postal_codes'],
                    'sort_order' => 0,
                ]
            );

            // Create translations
            foreach ($cityData['translations'] as $locale => $translation) {
                CityTranslation::updateOrCreate(
                    [
                        'city_id' => $city->id,
                        'locale' => $locale,
                    ],
                    [
                        'name' => $translation['name'],
                        'description' => $translation['description'],
                    ]
                );
            }
        }
    }
}
