<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class BelgiumCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $belgium = Country::where('cca2', 'BE')->first();
        $euZone = Zone::where('code', 'EU')->first();

        // Regions are no longer used in the database schema

        $cities = [
            // Brussels
            [
                'name' => 'Brussels',
                'code' => 'BE-BRU-BRU',
                'is_capital' => true,
                'is_default' => true,
                'latitude' => 50.8503,
                'longitude' => 4.3517,
                'population' => 1218255,
                'postal_codes' => ['1000', '1001', '1002'],
                'translations' => [
                    'lt' => ['name' => 'Briuselis', 'description' => 'Belgijos sostinė'],
                    'en' => ['name' => 'Brussels', 'description' => 'Capital of Belgium'],
                ],
            ],
            // Flanders
            [
                'name' => 'Antwerp',
                'code' => 'BE-VLG-ANT',
                'latitude' => 51.2194,
                'longitude' => 4.4025,
                'population' => 529247,
                'postal_codes' => ['2000'],
                'translations' => [
                    'lt' => ['name' => 'Antverpenas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Antwerp', 'description' => 'Port city'],
                ],
            ],
            [
                'name' => 'Ghent',
                'code' => 'BE-VLG-GHE',
                'latitude' => 51.0543,
                'longitude' => 3.7174,
                'population' => 263927,
                'postal_codes' => ['9000'],
                'translations' => [
                    'lt' => ['name' => 'Gandas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Ghent', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Bruges',
                'code' => 'BE-VLG-BRU',
                'latitude' => 51.2093,
                'longitude' => 3.2247,
                'population' => 118656,
                'postal_codes' => ['8000'],
                'translations' => [
                    'lt' => ['name' => 'Briugė', 'description' => 'UNESCO miestas'],
                    'en' => ['name' => 'Bruges', 'description' => 'UNESCO city'],
                ],
            ],
            [
                'name' => 'Leuven',
                'code' => 'BE-VLG-LEU',
                'latitude' => 50.8798,
                'longitude' => 4.7005,
                'population' => 101396,
                'postal_codes' => ['3000'],
                'translations' => [
                    'lt' => ['name' => 'Leuvenas', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Leuven', 'description' => 'University city'],
                ],
            ],
            [
                'name' => 'Mechelen',
                'code' => 'BE-VLG-MEC',
                'latitude' => 51.0259,
                'longitude' => 4.4776,
                'population' => 86921,
                'postal_codes' => ['2800'],
                'translations' => [
                    'lt' => ['name' => 'Mechlenas', 'description' => 'Katedros miestas'],
                    'en' => ['name' => 'Mechelen', 'description' => 'Cathedral city'],
                ],
            ],
            [
                'name' => 'Aalst',
                'code' => 'BE-VLG-AAL',
                'latitude' => 50.9378,
                'longitude' => 4.0403,
                'population' => 87000,
                'postal_codes' => ['9300'],
                'translations' => [
                    'lt' => ['name' => 'Alstas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Aalst', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Kortrijk',
                'code' => 'BE-VLG-KOR',
                'latitude' => 50.8278,
                'longitude' => 3.2647,
                'population' => 77000,
                'postal_codes' => ['8500'],
                'translations' => [
                    'lt' => ['name' => 'Kortreikas', 'description' => 'Tekstilės pramonės centras'],
                    'en' => ['name' => 'Kortrijk', 'description' => 'Textile industry center'],
                ],
            ],
            [
                'name' => 'Hasselt',
                'code' => 'BE-VLG-HAS',
                'latitude' => 50.9307,
                'longitude' => 5.3375,
                'population' => 78000,
                'postal_codes' => ['3500'],
                'translations' => [
                    'lt' => ['name' => 'Haseltas', 'description' => 'Limburgo sostinė'],
                    'en' => ['name' => 'Hasselt', 'description' => 'Capital of Limburg'],
                ],
            ],
            // Wallonia
            [
                'name' => 'Liège',
                'code' => 'BE-WAL-LIE',
                'latitude' => 50.6403,
                'longitude' => 5.5714,
                'population' => 197355,
                'postal_codes' => ['4000'],
                'translations' => [
                    'lt' => ['name' => 'Lježas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Liège', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Charleroi',
                'code' => 'BE-WAL-CHA',
                'latitude' => 50.4108,
                'longitude' => 4.4446,
                'population' => 201816,
                'postal_codes' => ['6000'],
                'translations' => [
                    'lt' => ['name' => 'Šarleua', 'description' => 'Pramonės centras'],
                    'en' => ['name' => 'Charleroi', 'description' => 'Industrial center'],
                ],
            ],
            [
                'name' => 'Namur',
                'code' => 'BE-WAL-NAM',
                'latitude' => 50.4669,
                'longitude' => 4.8675,
                'population' => 110939,
                'postal_codes' => ['5000'],
                'translations' => [
                    'lt' => ['name' => 'Namuras', 'description' => 'Valonijos sostinė'],
                    'en' => ['name' => 'Namur', 'description' => 'Capital of Wallonia'],
                ],
            ],
            [
                'name' => 'Mons',
                'code' => 'BE-WAL-MON',
                'latitude' => 50.4542,
                'longitude' => 3.9569,
                'population' => 95000,
                'postal_codes' => ['7000'],
                'translations' => [
                    'lt' => ['name' => 'Monas', 'description' => 'Henegau sostinė'],
                    'en' => ['name' => 'Mons', 'description' => 'Capital of Hainaut'],
                ],
            ],
            [
                'name' => 'Tournai',
                'code' => 'BE-WAL-TOU',
                'latitude' => 50.6064,
                'longitude' => 3.3889,
                'population' => 69000,
                'postal_codes' => ['7500'],
                'translations' => [
                    'lt' => ['name' => 'Turnė', 'description' => 'Senovinis miestas'],
                    'en' => ['name' => 'Tournai', 'description' => 'Ancient city'],
                ],
            ],
            [
                'name' => 'La Louvière',
                'code' => 'BE-WAL-LOU',
                'latitude' => 50.4764,
                'longitude' => 4.1875,
                'population' => 81000,
                'postal_codes' => ['7100'],
                'translations' => [
                    'lt' => ['name' => 'La Luviere', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'La Louvière', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Verviers',
                'code' => 'BE-WAL-VER',
                'latitude' => 50.5908,
                'longitude' => 5.8667,
                'population' => 55000,
                'postal_codes' => ['4800'],
                'translations' => [
                    'lt' => ['name' => 'Verve', 'description' => 'Tekstilės pramonės centras'],
                    'en' => ['name' => 'Verviers', 'description' => 'Textile industry center'],
                ],
            ],
            [
                'name' => 'Arlon',
                'code' => 'BE-WAL-ARL',
                'latitude' => 49.6839,
                'longitude' => 5.8167,
                'population' => 30000,
                'postal_codes' => ['6700'],
                'translations' => [
                    'lt' => ['name' => 'Arlonas', 'description' => 'Liuksemburgo sostinė'],
                    'en' => ['name' => 'Arlon', 'description' => 'Capital of Luxembourg'],
                ],
            ],
            // Additional Flanders cities
            [
                'name' => 'Sint-Niklaas',
                'code' => 'BE-VLG-SNI',
                'latitude' => 51.1653,
                'longitude' => 4.1394,
                'population' => 78000,
                'postal_codes' => ['9100'],
                'translations' => [
                    'lt' => ['name' => 'Sint Niklaasas', 'description' => 'Rytų Flandrijos sostinė'],
                    'en' => ['name' => 'Sint-Niklaas', 'description' => 'Capital of East Flanders'],
                ],
            ],
            [
                'name' => 'Ostend',
                'code' => 'BE-VLG-OST',
                'latitude' => 51.2297,
                'longitude' => 2.9114,
                'population' => 72000,
                'postal_codes' => ['8400'],
                'translations' => [
                    'lt' => ['name' => 'Ostendė', 'description' => 'Pajūrio kurortas'],
                    'en' => ['name' => 'Ostend', 'description' => 'Seaside resort'],
                ],
            ],
            [
                'name' => 'Genk',
                'code' => 'BE-VLG-GEN',
                'latitude' => 50.9650,
                'longitude' => 5.5008,
                'population' => 67000,
                'postal_codes' => ['3600'],
                'translations' => [
                    'lt' => ['name' => 'Genkas', 'description' => 'Automobilių pramonės centras'],
                    'en' => ['name' => 'Genk', 'description' => 'Automotive industry center'],
                ],
            ],
            [
                'name' => 'Roeselare',
                'code' => 'BE-VLG-ROE',
                'latitude' => 50.9444,
                'longitude' => 3.1250,
                'population' => 63000,
                'postal_codes' => ['8800'],
                'translations' => [
                    'lt' => ['name' => 'Ruzelarė', 'description' => 'Tekstilės pramonės centras'],
                    'en' => ['name' => 'Roeselare', 'description' => 'Textile industry center'],
                ],
            ],
            [
                'name' => 'Dendermonde',
                'code' => 'BE-VLG-DEN',
                'latitude' => 51.0294,
                'longitude' => 4.1006,
                'population' => 46000,
                'postal_codes' => ['9200'],
                'translations' => [
                    'lt' => ['name' => 'Dendermondė', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Dendermonde', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Turnhout',
                'code' => 'BE-VLG-TUR',
                'latitude' => 51.3228,
                'longitude' => 4.9447,
                'population' => 44000,
                'postal_codes' => ['2300'],
                'translations' => [
                    'lt' => ['name' => 'Turnhoutas', 'description' => 'Spalvų spaudos centras'],
                    'en' => ['name' => 'Turnhout', 'description' => 'Playing cards center'],
                ],
            ],
            [
                'name' => 'Lier',
                'code' => 'BE-VLG-LIE',
                'latitude' => 51.1311,
                'longitude' => 4.5703,
                'population' => 37000,
                'postal_codes' => ['2500'],
                'translations' => [
                    'lt' => ['name' => 'Lieris', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Lier', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Geel',
                'code' => 'BE-VLG-GEE',
                'latitude' => 51.1611,
                'longitude' => 4.9906,
                'population' => 40000,
                'postal_codes' => ['2440'],
                'translations' => [
                    'lt' => ['name' => 'Gelis', 'description' => 'Psichiatrijos centras'],
                    'en' => ['name' => 'Geel', 'description' => 'Psychiatric care center'],
                ],
            ],
            [
                'name' => 'Sint-Truiden',
                'code' => 'BE-VLG-STT',
                'latitude' => 50.8158,
                'longitude' => 5.1867,
                'population' => 42000,
                'postal_codes' => ['3800'],
                'translations' => [
                    'lt' => ['name' => 'Sint Truidenas', 'description' => 'Vaisių auginimo centras'],
                    'en' => ['name' => 'Sint-Truiden', 'description' => 'Fruit growing center'],
                ],
            ],
            // Additional Wallonia cities
            [
                'name' => 'La Louvière',
                'code' => 'BE-WAL-LAL',
                'latitude' => 50.4764,
                'longitude' => 4.1875,
                'population' => 81000,
                'postal_codes' => ['7100'],
                'translations' => [
                    'lt' => ['name' => 'La Luviere', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'La Louvière', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Seraing',
                'code' => 'BE-WAL-SER',
                'latitude' => 50.5833,
                'longitude' => 5.5000,
                'population' => 65000,
                'postal_codes' => ['4100'],
                'translations' => [
                    'lt' => ['name' => 'Seraingas', 'description' => 'Metalingų pramonės centras'],
                    'en' => ['name' => 'Seraing', 'description' => 'Metallurgy center'],
                ],
            ],
            [
                'name' => 'Herstal',
                'code' => 'BE-WAL-HER',
                'latitude' => 50.6667,
                'longitude' => 5.6333,
                'population' => 40000,
                'postal_codes' => ['4040'],
                'translations' => [
                    'lt' => ['name' => 'Herstalas', 'description' => 'Ginklų pramonės centras'],
                    'en' => ['name' => 'Herstal', 'description' => 'Arms industry center'],
                ],
            ],
            [
                'name' => 'Tubize',
                'code' => 'BE-WAL-TUB',
                'latitude' => 50.6833,
                'longitude' => 4.2167,
                'population' => 25000,
                'postal_codes' => ['1480'],
                'translations' => [
                    'lt' => ['name' => 'Tubizė', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Tubize', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Dinant',
                'code' => 'BE-WAL-DIN',
                'latitude' => 50.2611,
                'longitude' => 4.9111,
                'population' => 14000,
                'postal_codes' => ['5500'],
                'translations' => [
                    'lt' => ['name' => 'Dinantas', 'description' => 'Turizmo miestas'],
                    'en' => ['name' => 'Dinant', 'description' => 'Tourist city'],
                ],
            ],
            [
                'name' => 'Bastogne',
                'code' => 'BE-WAL-BAS',
                'latitude' => 50.0000,
                'longitude' => 5.7167,
                'population' => 15000,
                'postal_codes' => ['6600'],
                'translations' => [
                    'lt' => ['name' => 'Bastonė', 'description' => 'Antrojo pasaulinio karo miestas'],
                    'en' => ['name' => 'Bastogne', 'description' => 'World War II city'],
                ],
            ],
            [
                'name' => 'Spa',
                'code' => 'BE-WAL-SPA',
                'latitude' => 50.4833,
                'longitude' => 5.8667,
                'population' => 10000,
                'postal_codes' => ['4900'],
                'translations' => [
                    'lt' => ['name' => 'Spa', 'description' => 'Gydomųjų vandenų kurortas'],
                    'en' => ['name' => 'Spa', 'description' => 'Spa resort'],
                ],
            ],
            [
                'name' => 'Couvin',
                'code' => 'BE-WAL-COU',
                'latitude' => 50.0500,
                'longitude' => 4.5000,
                'population' => 14000,
                'postal_codes' => ['5660'],
                'translations' => [
                    'lt' => ['name' => 'Kuvinas', 'description' => 'Gamtos miestas'],
                    'en' => ['name' => 'Couvin', 'description' => 'Nature city'],
                ],
            ],
            [
                'name' => 'Châtelet',
                'code' => 'BE-WAL-CHT',
                'latitude' => 50.4000,
                'longitude' => 4.5167,
                'population' => 36000,
                'postal_codes' => ['6200'],
                'translations' => [
                    'lt' => ['name' => 'Šatle', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Châtelet', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Fleurus',
                'code' => 'BE-WAL-FLE',
                'latitude' => 50.4833,
                'longitude' => 4.5500,
                'population' => 23000,
                'postal_codes' => ['6220'],
                'translations' => [
                    'lt' => ['name' => 'Fleuras', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Fleurus', 'description' => 'Historic city'],
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
                    'country_id' => $belgium->id,
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
