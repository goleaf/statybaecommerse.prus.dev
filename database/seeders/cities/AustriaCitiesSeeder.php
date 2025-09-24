<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class AustriaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'AT')->first();
        if (! $country) {
            $this->command->warn('Austria country not found. Please run CountrySeeder first.');

            return;
        }

        $cities = [
            ['code' => 'AT-VIE', 'slug' => 'vienna', 'name' => ['lt' => 'Viena', 'en' => 'Vienna'], 'description' => 'Capital of Austria', 'is_capital' => true, 'latitude' => 48.2082, 'longitude' => 16.3738, 'population' => 1911191],
            ['code' => 'AT-GRA', 'slug' => 'graz', 'name' => ['lt' => 'Gracas', 'en' => 'Graz'], 'description' => 'Second largest city in Austria', 'is_capital' => false, 'latitude' => 47.0707, 'longitude' => 15.4395, 'population' => 289440],
            ['code' => 'AT-LIN', 'slug' => 'linz', 'name' => ['lt' => 'Lincas', 'en' => 'Linz'], 'description' => 'Capital of Upper Austria', 'is_capital' => false, 'latitude' => 48.3069, 'longitude' => 14.2858, 'population' => 204846],
            ['code' => 'AT-SAL', 'slug' => 'salzburg', 'name' => ['lt' => 'Zalcburgas', 'en' => 'Salzburg'], 'description' => 'Birthplace of Mozart', 'is_capital' => false, 'latitude' => 47.8095, 'longitude' => 13.0550, 'population' => 155021],
            ['code' => 'AT-INN', 'slug' => 'innsbruck', 'name' => ['lt' => 'Insbrukas', 'en' => 'Innsbruck'], 'description' => 'Capital of Tyrol', 'is_capital' => false, 'latitude' => 47.2692, 'longitude' => 11.4041, 'population' => 132493],
            ['code' => 'AT-KLA', 'slug' => 'klagenfurt', 'name' => ['lt' => 'Klagenfurtas', 'en' => 'Klagenfurt'], 'description' => 'Capital of Carinthia', 'is_capital' => false, 'latitude' => 46.6247, 'longitude' => 14.3053, 'population' => 101403],
            ['code' => 'AT-VIL', 'slug' => 'villach', 'name' => ['lt' => 'Vilachas', 'en' => 'Villach'], 'description' => 'City in Carinthia', 'is_capital' => false, 'latitude' => 46.6111, 'longitude' => 13.8558, 'population' => 65000],
            ['code' => 'AT-WEL', 'slug' => 'wels', 'name' => ['lt' => 'Velsas', 'en' => 'Wels'], 'description' => 'City in Upper Austria', 'is_capital' => false, 'latitude' => 48.1575, 'longitude' => 14.0289, 'population' => 62000],
            ['code' => 'AT-SAN', 'slug' => 'sankt-polten', 'name' => ['lt' => 'Šv. Pöltenas', 'en' => 'Sankt Pölten'], 'description' => 'Capital of Lower Austria', 'is_capital' => false, 'latitude' => 48.2047, 'longitude' => 15.6256, 'population' => 55000],
            ['code' => 'AT-DOR', 'slug' => 'dornbirn', 'name' => ['lt' => 'Dornbirnas', 'en' => 'Dornbirn'], 'description' => 'City in Vorarlberg', 'is_capital' => false, 'latitude' => 47.4142, 'longitude' => 9.7419, 'population' => 49000],
        ];

        foreach ($cities as $cityData) {
            $city = City::updateOrCreate(
                ['code' => $cityData['code']],
                array_merge($cityData, [
                    'country_id' => $country->id,
                    'name' => $cityData['name']['en'],
                    'slug' => $cityData['slug'],
                    'is_enabled' => true,
                    'is_default' => false,
                ])
            );

            // Create translations
            foreach (['lt', 'en'] as $locale) {
                CityTranslation::updateOrCreate([
                    'city_id' => $city->id,
                    'locale' => $locale,
                ], [
                    'name' => $cityData['name'][$locale] ?? $cityData['name']['en'],
                    'description' => $cityData['description'][$locale] ?? '',
                ]);
            }
        }

        $this->command->info('Austria cities seeded successfully.');
    }
}
