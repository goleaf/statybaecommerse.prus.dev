<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Translations\CityTranslation;
use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class LatviaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $latvia = Country::where('cca2', 'LV')->first();
        $euZone = Zone::where('code', 'EU')->first();

        $regions = Region::query()
            ->whereIn('code', ['LV-RI', 'LV-KU', 'LV-LG', 'LV-VI', 'LV-ZM'])
            ->get()
            ->keyBy('code');

        $cities = [
            [
                'name' => 'Riga',
                'code' => 'LV-RI-RIG',
                'is_capital' => true,
                'is_default' => true,
                'region_code' => 'LV-RI',
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
                'region_code' => 'LV-RI',
                'latitude' => 56.968,
                'longitude' => 23.7703,
                'population' => 57409,
                'postal_codes' => ['LV-2015'],
                'translations' => [
                    'lt' => ['name' => 'Jūrmala', 'description' => 'Kurortinis miestas'],
                    'en' => ['name' => 'Jurmala', 'description' => 'Resort city'],
                ],
            ],
            [
                'name' => 'Liepaja',
                'code' => 'LV-KU-LIE',
                'region_code' => 'LV-KU',
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
                'region_code' => 'LV-KU',
                'latitude' => 57.3937,
                'longitude' => 21.5647,
                'population' => 34420,
                'postal_codes' => ['LV-3601'],
                'translations' => [
                    'lt' => ['name' => 'Ventspils', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Ventspils', 'description' => 'Port city'],
                ],
            ],
            [
                'name' => 'Daugavpils',
                'code' => 'LV-LG-DAU',
                'region_code' => 'LV-LG',
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
                'region_code' => 'LV-LG',
                'latitude' => 56.5103,
                'longitude' => 27.3319,
                'population' => 25694,
                'postal_codes' => ['LV-4601'],
                'translations' => [
                    'lt' => ['name' => 'Rezekne', 'description' => 'Latgalios centras'],
                    'en' => ['name' => 'Rezekne', 'description' => 'Center of Latgale'],
                ],
            ],
            [
                'name' => 'Valmiera',
                'code' => 'LV-VI-VAL',
                'region_code' => 'LV-VI',
                'latitude' => 57.5408,
                'longitude' => 25.4275,
                'population' => 23556,
                'postal_codes' => ['LV-4201'],
                'translations' => [
                    'lt' => ['name' => 'Valmiera', 'description' => 'Vidžemos centras'],
                    'en' => ['name' => 'Valmiera', 'description' => 'Center of Vidzeme'],
                ],
            ],
            [
                'name' => 'Jelgava',
                'code' => 'LV-ZM-JEL',
                'region_code' => 'LV-ZM',
                'latitude' => 56.6511,
                'longitude' => 23.7214,
                'population' => 55897,
                'postal_codes' => ['LV-3001'],
                'translations' => [
                    'lt' => ['name' => 'Jelgava', 'description' => 'Žemgalos centras'],
                    'en' => ['name' => 'Jelgava', 'description' => 'Center of Zemgale'],
                ],
            ],
        ];

        foreach ($cities as $cityData) {
            $region = $regions->get($cityData['region_code'] ?? '') ?: null;

            $city = City::updateOrCreate(
                ['code' => $cityData['code']],
                [
                    'name' => $cityData['name'],
                    'slug' => Str::slug($cityData['name']),
                    'is_capital' => $cityData['is_capital'] ?? false,
                    'is_default' => $cityData['is_default'] ?? false,
                    'country_id' => $latvia?->id,
                    'zone_id' => $euZone?->id,
                    'region_id' => $region?->id,
                    'level' => 1,
                    'latitude' => $cityData['latitude'],
                    'longitude' => $cityData['longitude'],
                    'population' => $cityData['population'],
                    'postal_codes' => $cityData['postal_codes'],
                    'is_enabled' => true,
                ]
            );

            foreach ($cityData['translations'] as $locale => $translation) {
                CityTranslation::updateOrCreate(
                    [
                        'city_id' => $city->id,
                        'locale' => $locale,
                    ],
                    [
                        'name' => Arr::get($translation, 'name'),
                        'description' => Arr::get($translation, 'description'),
                    ]
                );
            }
        }
    }
}
