<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class AustraliaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'AU')->first();
        if (! $country) {
            $this->command->warn('Australia country not found. Please run CountrySeeder first.');

            return;
        }

        $cities = [
            ['code' => 'AU-SYD', 'slug' => 'sydney', 'name' => ['lt' => 'Sidnėjus', 'en' => 'Sydney'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => -33.8688, 'longitude' => 151.2093, 'population' => 5312163],
            ['code' => 'AU-MEL', 'slug' => 'melbourne', 'name' => ['lt' => 'Melburnas', 'en' => 'Melbourne'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => -37.8136, 'longitude' => 144.9631, 'population' => 5078193],
            ['code' => 'AU-BRI', 'slug' => 'brisbane', 'name' => ['lt' => 'Brisbenas', 'en' => 'Brisbane'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => -27.4698, 'longitude' => 153.0251, 'population' => 2487098],
            ['code' => 'AU-PER', 'slug' => 'perth', 'name' => ['lt' => 'Pertas', 'en' => 'Perth'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => -31.9505, 'longitude' => 115.8605, 'population' => 2143640],
            ['code' => 'AU-ADE', 'slug' => 'adelaide', 'name' => ['lt' => 'Adelaidė', 'en' => 'Adelaide'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => -34.9285, 'longitude' => 138.6007, 'population' => 1359640],
            ['code' => 'AU-GOL', 'slug' => 'gold-coast', 'name' => ['lt' => 'Aukso krantas', 'en' => 'Gold Coast'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => -28.0167, 'longitude' => 153.4000, 'population' => 709000],
            ['code' => 'AU-NEW', 'slug' => 'newcastle', 'name' => ['lt' => 'Niukaslas', 'en' => 'Newcastle'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => -32.9167, 'longitude' => 151.7500, 'population' => 322278],
            ['code' => 'AU-CAN', 'slug' => 'canberra', 'name' => ['lt' => 'Kanbera', 'en' => 'Canberra'], 'description' => 'Capital of ', 'is_capital' => true, 'latitude' => -35.2809, 'longitude' => 149.1300, 'population' => 457330],
            ['code' => 'AU-SUN', 'slug' => 'sunshine-coast', 'name' => ['lt' => 'Saulės krantas', 'en' => 'Sunshine Coast'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => -26.6500, 'longitude' => 153.0667, 'population' => 341069],
            ['code' => 'AU-WOL', 'slug' => 'wollongong', 'name' => ['lt' => 'Vulongongas', 'en' => 'Wollongong'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => -34.4278, 'longitude' => 150.8931, 'population' => 302739],
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

        $this->command->info('Australia cities seeded successfully.');
    }
}
