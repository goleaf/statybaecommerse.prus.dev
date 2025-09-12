<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\Translations\CityTranslation;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class LithuaniaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $lithuania = Country::where('cca2', 'LT')->first();
        $euZone = Zone::where('code', 'EU')->first();
        $ltZone = Zone::where('code', 'LT')->first();

        // Get regions
        $vilniusRegion = Region::where('code', 'LT-VL')->first();
        $kaunasRegion = Region::where('code', 'LT-KA')->first();
        $klaipedaRegion = Region::where('code', 'LT-KL')->first();
        $siauliaiRegion = Region::where('code', 'LT-SA')->first();
        $panevezysRegion = Region::where('code', 'LT-PN')->first();
        $alytusRegion = Region::where('code', 'LT-AL')->first();
        $marijampoleRegion = Region::where('code', 'LT-MR')->first();
        $taurageRegion = Region::where('code', 'LT-TA')->first();
        $telsiaiRegion = Region::where('code', 'LT-TE')->first();
        $utenaRegion = Region::where('code', 'LT-UT')->first();

        $cities = [
            // Vilnius County
            [
                'name' => 'Vilnius',
                'code' => 'LT-VL-VIL',
                'is_capital' => true,
                'is_default' => true,
                'region_id' => $vilniusRegion?->id,
                'latitude' => 54.6872,
                'longitude' => 25.2797,
                'population' => 588412,
                'postal_codes' => ['01001-14001'],
                'translations' => [
                    'lt' => ['name' => 'Vilnius', 'description' => 'Lietuvos sostinė'],
                    'en' => ['name' => 'Vilnius', 'description' => 'Capital of Lithuania'],
                ],
            ],
            [
                'name' => 'Trakai',
                'code' => 'LT-VL-TRA',
                'region_id' => $vilniusRegion?->id,
                'latitude' => 54.6333,
                'longitude' => 24.9333,
                'population' => 5406,
                'postal_codes' => ['21142'],
                'translations' => [
                    'lt' => ['name' => 'Trakai', 'description' => 'Istorinis miestas su pilimi'],
                    'en' => ['name' => 'Trakai', 'description' => 'Historic town with castle'],
                ],
            ],
            [
                'name' => 'Elektrėnai',
                'code' => 'LT-VL-ELE',
                'region_id' => $vilniusRegion?->id,
                'latitude' => 54.7833,
                'longitude' => 24.6667,
                'population' => 11000,
                'postal_codes' => ['26120'],
                'translations' => [
                    'lt' => ['name' => 'Elektrėnai', 'description' => 'Energetikos miestas'],
                    'en' => ['name' => 'Elektrėnai', 'description' => 'Energy city'],
                ],
            ],
            // Kaunas County
            [
                'name' => 'Kaunas',
                'code' => 'LT-KA-KAU',
                'region_id' => $kaunasRegion?->id,
                'latitude' => 54.8985,
                'longitude' => 23.9036,
                'population' => 304097,
                'postal_codes' => ['44001-52001'],
                'translations' => [
                    'lt' => ['name' => 'Kaunas', 'description' => 'Antrasis didžiausias Lietuvos miestas'],
                    'en' => ['name' => 'Kaunas', 'description' => 'Second largest city in Lithuania'],
                ],
            ],
            [
                'name' => 'Jonava',
                'code' => 'LT-KA-JON',
                'region_id' => $kaunasRegion?->id,
                'latitude' => 55.0833,
                'longitude' => 24.2833,
                'population' => 26000,
                'postal_codes' => ['55164'],
                'translations' => [
                    'lt' => ['name' => 'Jonava', 'description' => 'Chemijos pramonės centras'],
                    'en' => ['name' => 'Jonava', 'description' => 'Chemical industry center'],
                ],
            ],
            [
                'name' => 'Kėdainiai',
                'code' => 'LT-KA-KED',
                'region_id' => $kaunasRegion?->id,
                'latitude' => 55.2833,
                'longitude' => 23.9833,
                'population' => 23000,
                'postal_codes' => ['57150'],
                'translations' => [
                    'lt' => ['name' => 'Kėdainiai', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Kėdainiai', 'description' => 'Historic city'],
                ],
            ],
            // Klaipėda County
            [
                'name' => 'Klaipėda',
                'code' => 'LT-KL-KLA',
                'region_id' => $klaipedaRegion?->id,
                'latitude' => 55.7033,
                'longitude' => 21.1442,
                'population' => 152008,
                'postal_codes' => ['91001-95001'],
                'translations' => [
                    'lt' => ['name' => 'Klaipėda', 'description' => 'Pagrindinis Lietuvos uostas'],
                    'en' => ['name' => 'Klaipėda', 'description' => 'Main port of Lithuania'],
                ],
            ],
            [
                'name' => 'Šilutė',
                'code' => 'LT-KL-SIL',
                'region_id' => $klaipedaRegion?->id,
                'latitude' => 55.35,
                'longitude' => 21.4833,
                'population' => 17000,
                'postal_codes' => ['99101'],
                'translations' => [
                    'lt' => ['name' => 'Šilutė', 'description' => 'Mažosios Lietuvos centras'],
                    'en' => ['name' => 'Šilutė', 'description' => 'Center of Little Lithuania'],
                ],
            ],
            // Šiauliai County
            [
                'name' => 'Šiauliai',
                'code' => 'LT-SA-SIA',
                'region_id' => $siauliaiRegion?->id,
                'latitude' => 55.9333,
                'longitude' => 23.3167,
                'population' => 101514,
                'postal_codes' => ['76001-80001'],
                'translations' => [
                    'lt' => ['name' => 'Šiauliai', 'description' => 'Šiaurės Lietuvos centras'],
                    'en' => ['name' => 'Šiauliai', 'description' => 'Center of Northern Lithuania'],
                ],
            ],
            [
                'name' => 'Radviliškis',
                'code' => 'LT-SA-RAD',
                'region_id' => $siauliaiRegion?->id,
                'latitude' => 55.8167,
                'longitude' => 23.5333,
                'population' => 16000,
                'postal_codes' => ['82150'],
                'translations' => [
                    'lt' => ['name' => 'Radviliškis', 'description' => 'Geležinkelio mazgas'],
                    'en' => ['name' => 'Radviliškis', 'description' => 'Railway junction'],
                ],
            ],
            // Panevėžys County
            [
                'name' => 'Panevėžys',
                'code' => 'LT-PN-PAN',
                'region_id' => $panevezysRegion?->id,
                'latitude' => 55.7333,
                'longitude' => 24.35,
                'population' => 87048,
                'postal_codes' => ['35001-39001'],
                'translations' => [
                    'lt' => ['name' => 'Panevėžys', 'description' => 'Aukštaitijos centras'],
                    'en' => ['name' => 'Panevėžys', 'description' => 'Center of Aukštaitija'],
                ],
            ],
            // Alytus County
            [
                'name' => 'Alytus',
                'code' => 'LT-AL-ALY',
                'region_id' => $alytusRegion?->id,
                'latitude' => 54.4,
                'longitude' => 24.05,
                'population' => 52000,
                'postal_codes' => ['62001-66001'],
                'translations' => [
                    'lt' => ['name' => 'Alytus', 'description' => 'Dzūkijos centras'],
                    'en' => ['name' => 'Alytus', 'description' => 'Center of Dzūkija'],
                ],
            ],
            // Marijampolė County
            [
                'name' => 'Marijampolė',
                'code' => 'LT-MR-MAR',
                'region_id' => $marijampoleRegion?->id,
                'latitude' => 54.5667,
                'longitude' => 23.35,
                'population' => 35000,
                'postal_codes' => ['68001-72001'],
                'translations' => [
                    'lt' => ['name' => 'Marijampolė', 'description' => 'Suvalkijos centras'],
                    'en' => ['name' => 'Marijampolė', 'description' => 'Center of Suvalkija'],
                ],
            ],
            // Tauragė County
            [
                'name' => 'Tauragė',
                'code' => 'LT-TA-TAU',
                'region_id' => $taurageRegion?->id,
                'latitude' => 55.25,
                'longitude' => 22.2833,
                'population' => 22000,
                'postal_codes' => ['72001-76001'],
                'translations' => [
                    'lt' => ['name' => 'Tauragė', 'description' => 'Žemaitijos pietų centras'],
                    'en' => ['name' => 'Tauragė', 'description' => 'Southern Žemaitija center'],
                ],
            ],
            // Telšiai County
            [
                'name' => 'Telšiai',
                'code' => 'LT-TE-TEL',
                'region_id' => $telsiaiRegion?->id,
                'latitude' => 55.9833,
                'longitude' => 22.25,
                'population' => 22000,
                'postal_codes' => ['87001-91001'],
                'translations' => [
                    'lt' => ['name' => 'Telšiai', 'description' => 'Žemaitijos centras'],
                    'en' => ['name' => 'Telšiai', 'description' => 'Center of Žemaitija'],
                ],
            ],
            // Utena County
            [
                'name' => 'Utena',
                'code' => 'LT-UT-UTE',
                'region_id' => $utenaRegion?->id,
                'latitude' => 55.5,
                'longitude' => 25.6,
                'population' => 25000,
                'postal_codes' => ['28001-32001'],
                'translations' => [
                    'lt' => ['name' => 'Utena', 'description' => 'Aukštaitijos šiaurės centras'],
                    'en' => ['name' => 'Utena', 'description' => 'Northern Aukštaitija center'],
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
                    'country_id' => $lithuania->id,
                    'zone_id' => $ltZone?->id ?? $euZone?->id,
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
