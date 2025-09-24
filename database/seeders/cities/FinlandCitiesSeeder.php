<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class FinlandCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $finland = Country::where('cca2', 'FI')->first();
        $euZone = Zone::where('code', 'EU')->first();

        // Regions are no longer used in the database schema

        $cities = [
            // Uusimaa
            [
                'name' => 'Helsinki',
                'code' => 'FI-18-HEL',
                'is_capital' => true,
                'is_default' => true,
                'latitude' => 60.1699,
                'longitude' => 24.9384,
                'population' => 656920,
                'postal_codes' => ['00100', '00101', '00102'],
                'translations' => [
                    'lt' => ['name' => 'Helsinkis', 'description' => 'Suomijos sostinė'],
                    'en' => ['name' => 'Helsinki', 'description' => 'Capital of Finland'],
                ],
            ],
            [
                'name' => 'Espoo',
                'code' => 'FI-18-ESP',
                'latitude' => 60.2052,
                'longitude' => 24.6522,
                'population' => 293000,
                'postal_codes' => ['02000'],
                'translations' => [
                    'lt' => ['name' => 'Espo', 'description' => 'Technologijų miestas'],
                    'en' => ['name' => 'Espoo', 'description' => 'Technology city'],
                ],
            ],
            [
                'name' => 'Vantaa',
                'code' => 'FI-18-VAN',
                'latitude' => 60.2941,
                'longitude' => 25.0408,
                'population' => 240000,
                'postal_codes' => ['01000'],
                'translations' => [
                    'lt' => ['name' => 'Vanta', 'description' => 'Oro uosto miestas'],
                    'en' => ['name' => 'Vantaa', 'description' => 'Airport city'],
                ],
            ],
            [
                'name' => 'Kauniainen',
                'code' => 'FI-18-KAU',
                'latitude' => 60.2092,
                'longitude' => 24.7281,
                'population' => 10000,
                'postal_codes' => ['02700'],
                'translations' => [
                    'lt' => ['name' => 'Kaunianenas', 'description' => 'Prabangus miestas'],
                    'en' => ['name' => 'Kauniainen', 'description' => 'Luxury city'],
                ],
            ],

            // Varsinais-Suomi
            [
                'name' => 'Turku',
                'code' => 'FI-19-TUR',
                'latitude' => 60.4518,
                'longitude' => 22.2666,
                'population' => 195000,
                'postal_codes' => ['20000'],
                'translations' => [
                    'lt' => ['name' => 'Turku', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Turku', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Salo',
                'code' => 'FI-19-SAL',
                'latitude' => 60.3833,
                'longitude' => 23.1333,
                'population' => 55000,
                'postal_codes' => ['24100'],
                'translations' => [
                    'lt' => ['name' => 'Salo', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Salo', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Raisio',
                'code' => 'FI-19-RAI',
                'latitude' => 60.4833,
                'longitude' => 22.1667,
                'population' => 25000,
                'postal_codes' => ['21200'],
                'translations' => [
                    'lt' => ['name' => 'Raisio', 'description' => 'Pramonės centras'],
                    'en' => ['name' => 'Raisio', 'description' => 'Industrial center'],
                ],
            ],

            // Satakunta
            [
                'name' => 'Pori',
                'code' => 'FI-17-POR',
                'latitude' => 61.4833,
                'longitude' => 21.8000,
                'population' => 85000,
                'postal_codes' => ['28000'],
                'translations' => [
                    'lt' => ['name' => 'Pori', 'description' => 'Satakuntos sostinė'],
                    'en' => ['name' => 'Pori', 'description' => 'Capital of Satakunta'],
                ],
            ],
            [
                'name' => 'Rauma',
                'code' => 'FI-17-RAU',
                'latitude' => 61.1333,
                'longitude' => 21.5000,
                'population' => 40000,
                'postal_codes' => ['26100'],
                'translations' => [
                    'lt' => ['name' => 'Rauma', 'description' => 'UNESCO miestas'],
                    'en' => ['name' => 'Rauma', 'description' => 'UNESCO city'],
                ],
            ],

            // Kanta-Häme
            [
                'name' => 'Hämeenlinna',
                'code' => 'FI-05-HAM',
                'latitude' => 61.0000,
                'longitude' => 24.4667,
                'population' => 68000,
                'postal_codes' => ['13000'],
                'translations' => [
                    'lt' => ['name' => 'Hämeenlinna', 'description' => 'Kanta-Häme sostinė'],
                    'en' => ['name' => 'Hämeenlinna', 'description' => 'Capital of Kanta-Häme'],
                ],
            ],

            // Pirkanmaa
            [
                'name' => 'Tampere',
                'code' => 'FI-11-TAM',
                'latitude' => 61.4981,
                'longitude' => 23.7608,
                'population' => 244000,
                'postal_codes' => ['33000'],
                'translations' => [
                    'lt' => ['name' => 'Tampere', 'description' => 'Pirkanmaos sostinė'],
                    'en' => ['name' => 'Tampere', 'description' => 'Capital of Pirkanmaa'],
                ],
            ],
            [
                'name' => 'Nokia',
                'code' => 'FI-11-NOK',
                'latitude' => 61.4667,
                'longitude' => 23.5000,
                'population' => 34000,
                'postal_codes' => ['37100'],
                'translations' => [
                    'lt' => ['name' => 'Nokia', 'description' => 'Technologijų miestas'],
                    'en' => ['name' => 'Nokia', 'description' => 'Technology city'],
                ],
            ],

            // Päijät-Häme
            [
                'name' => 'Lahti',
                'code' => 'FI-16-LAH',
                'latitude' => 60.9833,
                'longitude' => 25.6500,
                'population' => 120000,
                'postal_codes' => ['15000'],
                'translations' => [
                    'lt' => ['name' => 'Lahti', 'description' => 'Päijät-Häme sostinė'],
                    'en' => ['name' => 'Lahti', 'description' => 'Capital of Päijät-Häme'],
                ],
            ],

            // Kymenlaakso
            [
                'name' => 'Kotka',
                'code' => 'FI-09-KOT',
                'latitude' => 60.4667,
                'longitude' => 26.9167,
                'population' => 54000,
                'postal_codes' => ['48000'],
                'translations' => [
                    'lt' => ['name' => 'Kotka', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Kotka', 'description' => 'Port city'],
                ],
            ],
            [
                'name' => 'Kouvola',
                'code' => 'FI-09-KOU',
                'latitude' => 60.8667,
                'longitude' => 26.7000,
                'population' => 85000,
                'postal_codes' => ['45000'],
                'translations' => [
                    'lt' => ['name' => 'Kouvola', 'description' => 'Kymenlaakso sostinė'],
                    'en' => ['name' => 'Kouvola', 'description' => 'Capital of Kymenlaakso'],
                ],
            ],

            // Etelä-Karjala
            [
                'name' => 'Lappeenranta',
                'code' => 'FI-02-LAP',
                'latitude' => 61.0667,
                'longitude' => 28.1833,
                'population' => 73000,
                'postal_codes' => ['53000'],
                'translations' => [
                    'lt' => ['name' => 'Lappeenranta', 'description' => 'Etelä-Karjala sostinė'],
                    'en' => ['name' => 'Lappeenranta', 'description' => 'Capital of Etelä-Karjala'],
                ],
            ],

            // Etelä-Savo
            [
                'name' => 'Mikkeli',
                'code' => 'FI-04-MIK',
                'latitude' => 61.6833,
                'longitude' => 27.2667,
                'population' => 54000,
                'postal_codes' => ['50000'],
                'translations' => [
                    'lt' => ['name' => 'Mikkeli', 'description' => 'Etelä-Savo sostinė'],
                    'en' => ['name' => 'Mikkeli', 'description' => 'Capital of Etelä-Savo'],
                ],
            ],

            // Pohjois-Savo
            [
                'name' => 'Kuopio',
                'code' => 'FI-15-KUO',
                'latitude' => 62.9000,
                'longitude' => 27.6833,
                'population' => 120000,
                'postal_codes' => ['70000'],
                'translations' => [
                    'lt' => ['name' => 'Kuopio', 'description' => 'Pohjois-Savo sostinė'],
                    'en' => ['name' => 'Kuopio', 'description' => 'Capital of Pohjois-Savo'],
                ],
            ],

            // Pohjois-Karjala
            [
                'name' => 'Joensuu',
                'code' => 'FI-13-JOE',
                'latitude' => 62.6000,
                'longitude' => 29.7500,
                'population' => 76000,
                'postal_codes' => ['80000'],
                'translations' => [
                    'lt' => ['name' => 'Joensuu', 'description' => 'Pohjois-Karjala sostinė'],
                    'en' => ['name' => 'Joensuu', 'description' => 'Capital of Pohjois-Karjala'],
                ],
            ],

            // Kainuu
            [
                'name' => 'Kajaani',
                'code' => 'FI-05-KAJ',
                'latitude' => 64.2167,
                'longitude' => 27.7333,
                'population' => 38000,
                'postal_codes' => ['87000'],
                'translations' => [
                    'lt' => ['name' => 'Kajaani', 'description' => 'Kainuu sostinė'],
                    'en' => ['name' => 'Kajaani', 'description' => 'Capital of Kainuu'],
                ],
            ],

            // Keski-Suomi
            [
                'name' => 'Jyväskylä',
                'code' => 'FI-08-JYV',
                'latitude' => 62.2333,
                'longitude' => 25.7333,
                'population' => 140000,
                'postal_codes' => ['40000'],
                'translations' => [
                    'lt' => ['name' => 'Jyväskylä', 'description' => 'Keski-Suomi sostinė'],
                    'en' => ['name' => 'Jyväskylä', 'description' => 'Capital of Keski-Suomi'],
                ],
            ],

            // Etelä-Pohjanmaa
            [
                'name' => 'Seinäjoki',
                'code' => 'FI-03-SEI',
                'latitude' => 62.7833,
                'longitude' => 22.8333,
                'population' => 65000,
                'postal_codes' => ['60000'],
                'translations' => [
                    'lt' => ['name' => 'Seinäjoki', 'description' => 'Etelä-Pohjanmaa sostinė'],
                    'en' => ['name' => 'Seinäjoki', 'description' => 'Capital of Etelä-Pohjanmaa'],
                ],
            ],

            // Pohjanmaa
            [
                'name' => 'Vaasa',
                'code' => 'FI-12-VAA',
                'latitude' => 63.1000,
                'longitude' => 21.6000,
                'population' => 68000,
                'postal_codes' => ['65000'],
                'translations' => [
                    'lt' => ['name' => 'Vaasa', 'description' => 'Pohjanmaa sostinė'],
                    'en' => ['name' => 'Vaasa', 'description' => 'Capital of Pohjanmaa'],
                ],
            ],

            // Keski-Pohjanmaa
            [
                'name' => 'Kokkola',
                'code' => 'FI-07-KOK',
                'latitude' => 63.8333,
                'longitude' => 23.1333,
                'population' => 48000,
                'postal_codes' => ['67000'],
                'translations' => [
                    'lt' => ['name' => 'Kokkola', 'description' => 'Keski-Pohjanmaa sostinė'],
                    'en' => ['name' => 'Kokkola', 'description' => 'Capital of Keski-Pohjanmaa'],
                ],
            ],

            // Pohjois-Pohjanmaa
            [
                'name' => 'Oulu',
                'code' => 'FI-14-OUL',
                'latitude' => 65.0167,
                'longitude' => 25.4667,
                'population' => 210000,
                'postal_codes' => ['90000'],
                'translations' => [
                    'lt' => ['name' => 'Oulu', 'description' => 'Pohjois-Pohjanmaa sostinė'],
                    'en' => ['name' => 'Oulu', 'description' => 'Capital of Pohjois-Pohjanmaa'],
                ],
            ],

            // Lappi
            [
                'name' => 'Rovaniemi',
                'code' => 'FI-10-ROV',
                'latitude' => 66.5000,
                'longitude' => 25.7167,
                'population' => 65000,
                'postal_codes' => ['96000'],
                'translations' => [
                    'lt' => ['name' => 'Rovaniemi', 'description' => 'Lappi sostinė'],
                    'en' => ['name' => 'Rovaniemi', 'description' => 'Capital of Lappi'],
                ],
            ],

            // Ahvenanmaa
            [
                'name' => 'Mariehamn',
                'code' => 'FI-01-MAR',
                'latitude' => 60.1000,
                'longitude' => 19.9333,
                'population' => 12000,
                'postal_codes' => ['22100'],
                'translations' => [
                    'lt' => ['name' => 'Mariehamn', 'description' => 'Ahvenanmaa sostinė'],
                    'en' => ['name' => 'Mariehamn', 'description' => 'Capital of Ahvenanmaa'],
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
                    'country_id' => $finland->id,
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
