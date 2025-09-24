<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class SouthAfricaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'ZA')->first();
        if (! $country) {
            $this->command->warn('South Africa country not found. Please run CountrySeeder first.');

            return;
        }

        $cities = [
            ['code' => 'ZA-JOH', 'slug' => 'johannesburg', 'name' => ['lt' => 'Johanesburgas', 'en' => 'Johannesburg'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => -26.2041, 'longitude' => 28.0473, 'population' => 5634806],
            ['code' => 'ZA-CPT', 'slug' => 'cape-town', 'name' => ['lt' => 'Keiptaunas', 'en' => 'Cape Town'], 'description' => 'Capital of ', 'is_capital' => true, 'latitude' => -33.9249, 'longitude' => 18.4241, 'population' => 4618000],
            ['code' => 'ZA-DUR', 'slug' => 'durban', 'name' => ['lt' => 'Durbanas', 'en' => 'Durban'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => -29.8587, 'longitude' => 31.0218, 'population' => 3442361],
            ['code' => 'ZA-PTA', 'slug' => 'pretoria', 'name' => ['lt' => 'Pretorija', 'en' => 'Pretoria'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => -25.7479, 'longitude' => 28.2293, 'population' => 2921488],
            ['code' => 'ZA-POR', 'slug' => 'port-elizabeth', 'name' => ['lt' => 'Port Elizabetas', 'en' => 'Port Elizabeth'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => -33.9608, 'longitude' => 25.6022, 'population' => 1152115],
            ['code' => 'ZA-BLO', 'slug' => 'bloemfontein', 'name' => ['lt' => 'Blumfontainas', 'en' => 'Bloemfontein'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => -29.1200, 'longitude' => 26.2149, 'population' => 556000],
            ['code' => 'ZA-POL', 'slug' => 'polokwane', 'name' => ['lt' => 'Polokvanė', 'en' => 'Polokwane'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => -23.9000, 'longitude' => 29.4500, 'population' => 130028],
            ['code' => 'ZA-NEL', 'slug' => 'nelspruit', 'name' => ['lt' => 'Nelspruitas', 'en' => 'Nelspruit'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => -25.4744, 'longitude' => 30.9703, 'population' => 110000],
            ['code' => 'ZA-KIM', 'slug' => 'kimberley', 'name' => ['lt' => 'Kimberlis', 'en' => 'Kimberley'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => -28.7386, 'longitude' => 24.7639, 'population' => 225160],
            ['code' => 'ZA-BIS', 'slug' => 'bisho', 'name' => ['lt' => 'Bišas', 'en' => 'Bisho'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => -32.8500, 'longitude' => 27.4333, 'population' => 137287],
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

        $this->command->info('South Africa cities seeded successfully.');
    }
}
