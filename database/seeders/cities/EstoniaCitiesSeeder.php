<?php declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\Translations\CityTranslation;
use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class EstoniaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $estonia = Country::where('cca2', 'EE')->first();
        $euZone = Zone::where('code', 'EU')->first();

        // Get regions
        $harjuRegion = Region::where('code', 'EE-37')->first();
        $tartuRegion = Region::where('code', 'EE-78')->first();
        $idaViruRegion = Region::where('code', 'EE-44')->first();
        $pärnuRegion = Region::where('code', 'EE-67')->first();
        $lääneViruRegion = Region::where('code', 'EE-59')->first();
        $valgaRegion = Region::where('code', 'EE-82')->first();
        $viljandiRegion = Region::where('code', 'EE-84')->first();
        $võruRegion = Region::where('code', 'EE-86')->first();
        $jõgevaRegion = Region::where('code', 'EE-49')->first();
        $järvaRegion = Region::where('code', 'EE-51')->first();
        $lääneRegion = Region::where('code', 'EE-57')->first();
        $põlvaRegion = Region::where('code', 'EE-65')->first();
        $raplaRegion = Region::where('code', 'EE-70')->first();
        $saareRegion = Region::where('code', 'EE-74')->first();
        $hiiuRegion = Region::where('code', 'EE-39')->first();

        $cities = [
            // Harju County (Tallinn region)
            [
                'name' => 'Tallinn',
                'code' => 'EE-37-TAL',
                'is_capital' => true,
                'is_default' => true,
                'region_id' => $harjuRegion?->id,
                'latitude' => 59.437,
                'longitude' => 24.7536,
                'population' => 437619,
                'postal_codes' => ['10111', '10112', '10113'],
                'translations' => [
                    'lt' => ['name' => 'Talinas', 'description' => 'Estijos sostinė'],
                    'en' => ['name' => 'Tallinn', 'description' => 'Capital of Estonia'],
                ],
            ],
            [
                'name' => 'Keila',
                'code' => 'EE-37-KEI',
                'region_id' => $harjuRegion?->id,
                'latitude' => 59.3036,
                'longitude' => 24.4131,
                'population' => 10000,
                'postal_codes' => ['76601'],
                'translations' => [
                    'lt' => ['name' => 'Keila', 'description' => 'Mažas miestas Harju apskrityje'],
                    'en' => ['name' => 'Keila', 'description' => 'Small town in Harju County'],
                ],
            ],
            [
                'name' => 'Maardu',
                'code' => 'EE-37-MAA',
                'region_id' => $harjuRegion?->id,
                'latitude' => 59.4764,
                'longitude' => 25.025,
                'population' => 17000,
                'postal_codes' => ['74111'],
                'translations' => [
                    'lt' => ['name' => 'Maardu', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Maardu', 'description' => 'Industrial city'],
                ],
            ],
            // Tartu County
            [
                'name' => 'Tartu',
                'code' => 'EE-78-TAR',
                'region_id' => $tartuRegion?->id,
                'latitude' => 58.378,
                'longitude' => 26.729,
                'population' => 91407,
                'postal_codes' => ['50050', '50090'],
                'translations' => [
                    'lt' => ['name' => 'Tartu', 'description' => 'Estijos universiteto miestas'],
                    'en' => ['name' => 'Tartu', 'description' => 'University city of Estonia'],
                ],
            ],
            [
                'name' => 'Elva',
                'code' => 'EE-78-ELV',
                'region_id' => $tartuRegion?->id,
                'latitude' => 58.2225,
                'longitude' => 26.4211,
                'population' => 5500,
                'postal_codes' => ['61501'],
                'translations' => [
                    'lt' => ['name' => 'Elva', 'description' => 'Kurortinis miestas'],
                    'en' => ['name' => 'Elva', 'description' => 'Resort town'],
                ],
            ],
            // Ida-Viru County
            [
                'name' => 'Narva',
                'code' => 'EE-44-NAR',
                'region_id' => $idaViruRegion?->id,
                'latitude' => 59.3753,
                'longitude' => 28.1903,
                'population' => 54024,
                'postal_codes' => ['20001'],
                'translations' => [
                    'lt' => ['name' => 'Narva', 'description' => 'Rusijos sienos miestas'],
                    'en' => ['name' => 'Narva', 'description' => 'City on Russian border'],
                ],
            ],
            [
                'name' => 'Kohtla-Järve',
                'code' => 'EE-44-KOH',
                'region_id' => $idaViruRegion?->id,
                'latitude' => 59.3986,
                'longitude' => 27.2731,
                'population' => 35000,
                'postal_codes' => ['30301'],
                'translations' => [
                    'lt' => ['name' => 'Kohtla-Järve', 'description' => 'Alyvos pramonės centras'],
                    'en' => ['name' => 'Kohtla-Järve', 'description' => 'Oil shale industry center'],
                ],
            ],
            [
                'name' => 'Sillamäe',
                'code' => 'EE-44-SIL',
                'region_id' => $idaViruRegion?->id,
                'latitude' => 59.3908,
                'longitude' => 27.7744,
                'population' => 12000,
                'postal_codes' => ['40231'],
                'translations' => [
                    'lt' => ['name' => 'Sillamäe', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'Sillamäe', 'description' => 'Seaside city'],
                ],
            ],
            // Pärnu County
            [
                'name' => 'Pärnu',
                'code' => 'EE-67-PAR',
                'region_id' => $pärnuRegion?->id,
                'latitude' => 58.3859,
                'longitude' => 24.4971,
                'population' => 39179,
                'postal_codes' => ['80010'],
                'translations' => [
                    'lt' => ['name' => 'Pärnu', 'description' => 'Estijos vasaros sostinė'],
                    'en' => ['name' => 'Pärnu', 'description' => 'Summer capital of Estonia'],
                ],
            ],
            // Lääne-Viru County
            [
                'name' => 'Rakvere',
                'code' => 'EE-59-RAK',
                'region_id' => $lääneViruRegion?->id,
                'latitude' => 59.3464,
                'longitude' => 26.3558,
                'population' => 15000,
                'postal_codes' => ['44306'],
                'translations' => [
                    'lt' => ['name' => 'Rakvere', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Rakvere', 'description' => 'Historic city'],
                ],
            ],
            // Valga County
            [
                'name' => 'Valga',
                'code' => 'EE-82-VAL',
                'region_id' => $valgaRegion?->id,
                'latitude' => 57.7778,
                'longitude' => 26.0472,
                'population' => 12000,
                'postal_codes' => ['68203'],
                'translations' => [
                    'lt' => ['name' => 'Valga', 'description' => 'Sienos miestas su Latvija'],
                    'en' => ['name' => 'Valga', 'description' => 'Border city with Latvia'],
                ],
            ],
            // Viljandi County
            [
                'name' => 'Viljandi',
                'code' => 'EE-84-VIL',
                'region_id' => $viljandiRegion?->id,
                'latitude' => 58.3639,
                'longitude' => 25.59,
                'population' => 17000,
                'postal_codes' => ['71020'],
                'translations' => [
                    'lt' => ['name' => 'Viljandi', 'description' => 'Kultūros miestas'],
                    'en' => ['name' => 'Viljandi', 'description' => 'Cultural city'],
                ],
            ],
            // Võru County
            [
                'name' => 'Võru',
                'code' => 'EE-86-VOR',
                'region_id' => $võruRegion?->id,
                'latitude' => 57.8333,
                'longitude' => 27.0167,
                'population' => 12000,
                'postal_codes' => ['65601'],
                'translations' => [
                    'lt' => ['name' => 'Võru', 'description' => 'Pietų Estijos centras'],
                    'en' => ['name' => 'Võru', 'description' => 'Center of Southern Estonia'],
                ],
            ],
            // Jõgeva County
            [
                'name' => 'Jõgeva',
                'code' => 'EE-49-JOG',
                'region_id' => $jõgevaRegion?->id,
                'latitude' => 58.7469,
                'longitude' => 26.3939,
                'population' => 5000,
                'postal_codes' => ['48301'],
                'translations' => [
                    'lt' => ['name' => 'Jõgeva', 'description' => 'Žemės ūkio centras'],
                    'en' => ['name' => 'Jõgeva', 'description' => 'Agricultural center'],
                ],
            ],
            // Järva County
            [
                'name' => 'Paide',
                'code' => 'EE-51-PAI',
                'region_id' => $järvaRegion?->id,
                'latitude' => 58.8856,
                'longitude' => 25.5572,
                'population' => 8000,
                'postal_codes' => ['72711'],
                'translations' => [
                    'lt' => ['name' => 'Paide', 'description' => 'Järva apskrities centras'],
                    'en' => ['name' => 'Paide', 'description' => 'Center of Järva County'],
                ],
            ],
            // Lääne County
            [
                'name' => 'Haapsalu',
                'code' => 'EE-57-HAA',
                'region_id' => $lääneRegion?->id,
                'latitude' => 58.9431,
                'longitude' => 23.5414,
                'population' => 10000,
                'postal_codes' => ['90501'],
                'translations' => [
                    'lt' => ['name' => 'Haapsalu', 'description' => 'Kurortinis miestas'],
                    'en' => ['name' => 'Haapsalu', 'description' => 'Resort town'],
                ],
            ],
            // Põlva County
            [
                'name' => 'Põlva',
                'code' => 'EE-65-POL',
                'region_id' => $põlvaRegion?->id,
                'latitude' => 58.0531,
                'longitude' => 27.0519,
                'population' => 5000,
                'postal_codes' => ['63308'],
                'translations' => [
                    'lt' => ['name' => 'Põlva', 'description' => 'Põlva apskrities centras'],
                    'en' => ['name' => 'Põlva', 'description' => 'Center of Põlva County'],
                ],
            ],
            // Rapla County
            [
                'name' => 'Rapla',
                'code' => 'EE-70-RAP',
                'region_id' => $raplaRegion?->id,
                'latitude' => 59.0072,
                'longitude' => 24.7928,
                'population' => 5000,
                'postal_codes' => ['79511'],
                'translations' => [
                    'lt' => ['name' => 'Rapla', 'description' => 'Rapla apskrities centras'],
                    'en' => ['name' => 'Rapla', 'description' => 'Center of Rapla County'],
                ],
            ],
            // Saare County
            [
                'name' => 'Kuressaare',
                'code' => 'EE-74-KUR',
                'region_id' => $saareRegion?->id,
                'latitude' => 58.2528,
                'longitude' => 22.4853,
                'population' => 13000,
                'postal_codes' => ['93813'],
                'translations' => [
                    'lt' => ['name' => 'Kuressaare', 'description' => 'Saare salos centras'],
                    'en' => ['name' => 'Kuressaare', 'description' => 'Center of Saare Island'],
                ],
            ],
            // Hiiu County
            [
                'name' => 'Kärdla',
                'code' => 'EE-39-KAR',
                'region_id' => $hiiuRegion?->id,
                'latitude' => 58.9978,
                'longitude' => 22.7492,
                'population' => 3000,
                'postal_codes' => ['92401'],
                'translations' => [
                    'lt' => ['name' => 'Kärdla', 'description' => 'Hiiu salos centras'],
                    'en' => ['name' => 'Kärdla', 'description' => 'Center of Hiiu Island'],
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
                    'country_id' => $estonia->id,
                    'zone_id' => $euZone?->id,
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
