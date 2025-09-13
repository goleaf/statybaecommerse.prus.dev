<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\Translations\CityTranslation;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class RussiaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $russia = Country::where('cca2', 'RU')->first();
        $ruZone = Zone::where('code', 'RU')->first();

        // Get regions
        $moscowRegion = Region::where('code', 'RU-MOW')->first();
        $spbRegion = Region::where('code', 'RU-SPE')->first();
        $novosibirskRegion = Region::where('code', 'RU-NVS')->first();
        $yekaterinburgRegion = Region::where('code', 'RU-SVE')->first();
        $kazanRegion = Region::where('code', 'RU-TA')->first();
        $nizhnyNovgorodRegion = Region::where('code', 'RU-NIZ')->first();
        $chelyabinskRegion = Region::where('code', 'RU-CHE')->first();
        $omskRegion = Region::where('code', 'RU-OMS')->first();
        $samaraRegion = Region::where('code', 'RU-SAM')->first();
        $rostovRegion = Region::where('code', 'RU-ROS')->first();

        $cities = [
            // Moscow
            [
                'name' => 'Moscow',
                'code' => 'RU-MOW-MOS',
                'is_capital' => true,
                'is_default' => true,
                'region_id' => $moscowRegion?->id,
                'latitude' => 55.7558,
                'longitude' => 37.6176,
                'population' => 12615079,
                'postal_codes' => ['101000', '101001', '101002'],
                'translations' => [
                    'lt' => ['name' => 'Maskva', 'description' => 'Rusijos sostinė'],
                    'en' => ['name' => 'Moscow', 'description' => 'Capital of Russia'],
                ],
            ],
            // Saint Petersburg
            [
                'name' => 'Saint Petersburg',
                'code' => 'RU-SPE-SPB',
                'region_id' => $spbRegion?->id,
                'latitude' => 59.9311,
                'longitude' => 30.3609,
                'population' => 5383890,
                'postal_codes' => ['190000', '190001', '190002'],
                'translations' => [
                    'lt' => ['name' => 'Sankt Peterburgas', 'description' => 'Kultūros sostinė'],
                    'en' => ['name' => 'Saint Petersburg', 'description' => 'Cultural capital'],
                ],
            ],
            // Novosibirsk
            [
                'name' => 'Novosibirsk',
                'code' => 'RU-NVS-NOV',
                'region_id' => $novosibirskRegion?->id,
                'latitude' => 55.0084,
                'longitude' => 82.9357,
                'population' => 1625631,
                'postal_codes' => ['630000'],
                'translations' => [
                    'lt' => ['name' => 'Novosibirskas', 'description' => 'Sibiro sostinė'],
                    'en' => ['name' => 'Novosibirsk', 'description' => 'Capital of Siberia'],
                ],
            ],
            // Yekaterinburg
            [
                'name' => 'Yekaterinburg',
                'code' => 'RU-SVE-YEK',
                'region_id' => $yekaterinburgRegion?->id,
                'latitude' => 56.8431,
                'longitude' => 60.6454,
                'population' => 1493749,
                'postal_codes' => ['620000'],
                'translations' => [
                    'lt' => ['name' => 'Jekaterinburgas', 'description' => 'Uralo sostinė'],
                    'en' => ['name' => 'Yekaterinburg', 'description' => 'Capital of Urals'],
                ],
            ],
            // Kazan
            [
                'name' => 'Kazan',
                'code' => 'RU-TA-KAZ',
                'region_id' => $kazanRegion?->id,
                'latitude' => 55.8304,
                'longitude' => 49.0661,
                'population' => 1257391,
                'postal_codes' => ['420000'],
                'translations' => [
                    'lt' => ['name' => 'Kazanė', 'description' => 'Tatarstano sostinė'],
                    'en' => ['name' => 'Kazan', 'description' => 'Capital of Tatarstan'],
                ],
            ],
            // Nizhny Novgorod
            [
                'name' => 'Nizhny Novgorod',
                'code' => 'RU-NIZ-NIZ',
                'region_id' => $nizhnyNovgorodRegion?->id,
                'latitude' => 56.3269,
                'longitude' => 44.0075,
                'population' => 1250619,
                'postal_codes' => ['603000'],
                'translations' => [
                    'lt' => ['name' => 'Nižnij Novgorodas', 'description' => 'Automobilių pramonės centras'],
                    'en' => ['name' => 'Nizhny Novgorod', 'description' => 'Automotive industry center'],
                ],
            ],
            // Chelyabinsk
            [
                'name' => 'Chelyabinsk',
                'code' => 'RU-CHE-CHE',
                'region_id' => $chelyabinskRegion?->id,
                'latitude' => 55.1644,
                'longitude' => 61.4368,
                'population' => 1202371,
                'postal_codes' => ['454000'],
                'translations' => [
                    'lt' => ['name' => 'Čeliabinskas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Chelyabinsk', 'description' => 'Industrial city'],
                ],
            ],
            // Omsk
            [
                'name' => 'Omsk',
                'code' => 'RU-OMS-OMS',
                'region_id' => $omskRegion?->id,
                'latitude' => 54.9885,
                'longitude' => 73.3242,
                'population' => 1178391,
                'postal_codes' => ['644000'],
                'translations' => [
                    'lt' => ['name' => 'Omskas', 'description' => 'Sibiro miestas'],
                    'en' => ['name' => 'Omsk', 'description' => 'Siberian city'],
                ],
            ],
            // Samara
            [
                'name' => 'Samara',
                'code' => 'RU-SAM-SAM',
                'region_id' => $samaraRegion?->id,
                'latitude' => 53.2001,
                'longitude' => 50.15,
                'population' => 1164685,
                'postal_codes' => ['443000'],
                'translations' => [
                    'lt' => ['name' => 'Samara', 'description' => 'Volgos miestas'],
                    'en' => ['name' => 'Samara', 'description' => 'Volga city'],
                ],
            ],
            // Rostov-on-Don
            [
                'name' => 'Rostov-on-Don',
                'code' => 'RU-ROS-ROS',
                'region_id' => $rostovRegion?->id,
                'latitude' => 47.2357,
                'longitude' => 39.7015,
                'population' => 1125299,
                'postal_codes' => ['344000'],
                'translations' => [
                    'lt' => ['name' => 'Rostovas prie Dono', 'description' => 'Pietų Rusijos centras'],
                    'en' => ['name' => 'Rostov-on-Don', 'description' => 'Center of Southern Russia'],
                ],
            ],
            // Additional major cities
            [
                'name' => 'Ufa',
                'code' => 'RU-BA-UFA',
                'region_id' => null,
                'latitude' => 54.7388,
                'longitude' => 55.9721,
                'population' => 1125699,
                'postal_codes' => ['450000'],
                'translations' => [
                    'lt' => ['name' => 'Ufa', 'description' => 'Baškirijos sostinė'],
                    'en' => ['name' => 'Ufa', 'description' => 'Capital of Bashkortostan'],
                ],
            ],
            [
                'name' => 'Krasnoyarsk',
                'code' => 'RU-KYA-KRA',
                'region_id' => null,
                'latitude' => 56.0184,
                'longitude' => 92.8672,
                'population' => 1091551,
                'postal_codes' => ['660000'],
                'translations' => [
                    'lt' => ['name' => 'Krasnojarskas', 'description' => 'Sibiro miestas'],
                    'en' => ['name' => 'Krasnoyarsk', 'description' => 'Siberian city'],
                ],
            ],
            [
                'name' => 'Perm',
                'code' => 'RU-PER-PER',
                'region_id' => null,
                'latitude' => 58.0105,
                'longitude' => 56.2502,
                'population' => 1053738,
                'postal_codes' => ['614000'],
                'translations' => [
                    'lt' => ['name' => 'Permė', 'description' => 'Uralo miestas'],
                    'en' => ['name' => 'Perm', 'description' => 'Ural city'],
                ],
            ],
            [
                'name' => 'Voronezh',
                'code' => 'RU-VOR-VOR',
                'region_id' => null,
                'latitude' => 51.672,
                'longitude' => 39.1843,
                'population' => 1057681,
                'postal_codes' => ['394000'],
                'translations' => [
                    'lt' => ['name' => 'Voronežas', 'description' => 'Centrinės Rusijos miestas'],
                    'en' => ['name' => 'Voronezh', 'description' => 'Central Russian city'],
                ],
            ],
            [
                'name' => 'Volgograd',
                'code' => 'RU-VGG-VOL',
                'region_id' => null,
                'latitude' => 48.708,
                'longitude' => 44.5133,
                'population' => 1015586,
                'postal_codes' => ['400000'],
                'translations' => [
                    'lt' => ['name' => 'Volgogradas', 'description' => 'Stalingrado miestas'],
                    'en' => ['name' => 'Volgograd', 'description' => 'Stalingrad city'],
                ],
            ],
            [
                'name' => 'Krasnodar',
                'code' => 'RU-KDA-KRA',
                'region_id' => null,
                'latitude' => 45.0448,
                'longitude' => 38.976,
                'population' => 932629,
                'postal_codes' => ['350000'],
                'translations' => [
                    'lt' => ['name' => 'Krasnodaras', 'description' => 'Kubanės sostinė'],
                    'en' => ['name' => 'Krasnodar', 'description' => 'Capital of Kuban'],
                ],
            ],
            [
                'name' => 'Saratov',
                'code' => 'RU-SAR-SAR',
                'region_id' => null,
                'latitude' => 51.5406,
                'longitude' => 46.0086,
                'population' => 838042,
                'postal_codes' => ['410000'],
                'translations' => [
                    'lt' => ['name' => 'Saratovas', 'description' => 'Volgos miestas'],
                    'en' => ['name' => 'Saratov', 'description' => 'Volga city'],
                ],
            ],
            [
                'name' => 'Tyumen',
                'code' => 'RU-TYU-TYU',
                'region_id' => null,
                'latitude' => 57.1522,
                'longitude' => 65.5272,
                'population' => 807271,
                'postal_codes' => ['625000'],
                'translations' => [
                    'lt' => ['name' => 'Tiumenė', 'description' => 'Naftos pramonės centras'],
                    'en' => ['name' => 'Tyumen', 'description' => 'Oil industry center'],
                ],
            ],
            [
                'name' => 'Tolyatti',
                'code' => 'RU-SAM-TOL',
                'region_id' => $samaraRegion?->id,
                'latitude' => 53.5303,
                'longitude' => 49.3461,
                'population' => 707408,
                'postal_codes' => ['445000'],
                'translations' => [
                    'lt' => ['name' => 'Toljatis', 'description' => 'Automobilių pramonės centras'],
                    'en' => ['name' => 'Tolyatti', 'description' => 'Automotive industry center'],
                ],
            ],
        ];

        foreach ($cities as $cityData) {
            $city = City::updateOrCreate(
                ['code' => $cityData['code']],
                [
                    'name' => $cityData['name'],
                    'slug' => \Str::slug($cityData['name']),
                    'is_enabled' => true,
                    'is_default' => $cityData['is_default'] ?? false,
                    'is_capital' => $cityData['is_capital'] ?? false,
                    'country_id' => $russia->id,
                    'zone_id' => $ruZone?->id,
                    'region_id' => $cityData['region_id'],
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
