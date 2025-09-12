<?php declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\Translations\CityTranslation;
use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class BelgiumCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $belgium = Country::where('cca2', 'BE')->first();
        $euZone = Zone::where('code', 'EU')->first();

        // Get regions
        $brusselsRegion = Region::where('code', 'BE-BRU')->first();
        $flandersRegion = Region::where('code', 'BE-VLG')->first();
        $walloniaRegion = Region::where('code', 'BE-WAL')->first();

        $cities = [
            // Brussels
            [
                'name' => 'Brussels',
                'code' => 'BE-BRU-BRU',
                'is_capital' => true,
                'is_default' => true,
                'region_id' => $brusselsRegion?->id,
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
                'region_id' => $flandersRegion?->id,
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
                'region_id' => $flandersRegion?->id,
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
                'region_id' => $flandersRegion?->id,
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
                'region_id' => $flandersRegion?->id,
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
                'region_id' => $flandersRegion?->id,
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
                'region_id' => $flandersRegion?->id,
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
                'region_id' => $flandersRegion?->id,
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
                'region_id' => $flandersRegion?->id,
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
                'region_id' => $walloniaRegion?->id,
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
                'region_id' => $walloniaRegion?->id,
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
                'region_id' => $walloniaRegion?->id,
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
                'region_id' => $walloniaRegion?->id,
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
                'region_id' => $walloniaRegion?->id,
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
                'region_id' => $walloniaRegion?->id,
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
                'region_id' => $walloniaRegion?->id,
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
                'region_id' => $walloniaRegion?->id,
                'latitude' => 49.6839,
                'longitude' => 5.8167,
                'population' => 30000,
                'postal_codes' => ['6700'],
                'translations' => [
                    'lt' => ['name' => 'Arlonas', 'description' => 'Liuksemburgo sostinė'],
                    'en' => ['name' => 'Arlon', 'description' => 'Capital of Luxembourg'],
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
                    'country_id' => $belgium->id,
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
