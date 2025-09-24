<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class VietnamCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'VN')->first();
        if (! $country) {
            $this->command->warn('Vietnam country not found. Please run CountrySeeder first.');

            return;
        }

        $cities = [
            ['code' => 'VN-HCM', 'slug' => 'ho-chi-minh-city', 'name' => ['lt' => 'Ho Či Min miestas', 'en' => 'Ho Chi Minh City'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 10.8231, 'longitude' => 106.6297, 'population' => 8993000],
            ['code' => 'VN-HAN', 'slug' => 'hanoi', 'name' => ['lt' => 'Hanojus', 'en' => 'Hanoi'], 'description' => 'Capital of ', 'is_capital' => true, 'latitude' => 21.0285, 'longitude' => 105.8542, 'population' => 8053663],
            ['code' => 'VN-DAN', 'slug' => 'da-nang', 'name' => ['lt' => 'Da Nangas', 'en' => 'Da Nang'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 16.0544, 'longitude' => 108.2022, 'population' => 1134310],
            ['code' => 'VN-HAI', 'slug' => 'hai-phong', 'name' => ['lt' => 'Hai Fongas', 'en' => 'Hai Phong'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 20.8449, 'longitude' => 106.6881, 'population' => 2028513],
            ['code' => 'VN-CAN', 'slug' => 'can-tho', 'name' => ['lt' => 'Kan Tho', 'en' => 'Can Tho'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 10.0452, 'longitude' => 105.7469, 'population' => 1238000],
            ['code' => 'VN-BIE', 'slug' => 'bien-hoa', 'name' => ['lt' => 'Bien Hoa', 'en' => 'Bien Hoa'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 10.9447, 'longitude' => 106.8243, 'population' => 1100000],
            ['code' => 'VN-HUE', 'slug' => 'hue', 'name' => ['lt' => 'Hue', 'en' => 'Hue'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 16.4637, 'longitude' => 107.5909, 'population' => 652572],
            ['code' => 'VN-NHA', 'slug' => 'nha-trang', 'name' => ['lt' => 'Nha Trangas', 'en' => 'Nha Trang'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 12.2388, 'longitude' => 109.1967, 'population' => 392279],
            ['code' => 'VN-BUI', 'slug' => 'buon-ma-thuot', 'name' => ['lt' => 'Buon Ma Thuot', 'en' => 'Buon Ma Thuot'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 12.6667, 'longitude' => 108.0500, 'population' => 340000],
            ['code' => 'VN-PLE', 'slug' => 'pleiku', 'name' => ['lt' => 'Pleiku', 'en' => 'Pleiku'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 13.9833, 'longitude' => 108.0000, 'population' => 186763],
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

        $this->command->info('Vietnam cities seeded successfully.');
    }
}
