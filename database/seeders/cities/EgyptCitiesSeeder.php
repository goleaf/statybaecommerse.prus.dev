<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class EgyptCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'EG')->first();
        if (!$country) {
            $this->command->warn('Egypt country not found. Please run CountrySeeder first.');
            return;
        }

        $cities = [
            ['code' => 'EG-CAI', 'slug' => 'cairo', 'name' => ['lt' => 'Kairas', 'en' => 'Cairo'], 'description' => 'Capital of ', 'is_capital' => true, 'latitude' => 30.0444, 'longitude' => 31.2357, 'population' => 20484965],
            ['code' => 'EG-ALX', 'slug' => 'alexandria', 'name' => ['lt' => 'Aleksandrija', 'en' => 'Alexandria'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 31.2001, 'longitude' => 29.9187, 'population' => 5200000],
            ['code' => 'EG-GIZ', 'slug' => 'giza', 'name' => ['lt' => 'Giza', 'en' => 'Giza'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 30.0131, 'longitude' => 31.2089, 'population' => 3240000],
            ['code' => 'EG-SUE', 'slug' => 'suez', 'name' => ['lt' => 'Suezas', 'en' => 'Suez'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 29.9668, 'longitude' => 32.5498, 'population' => 750000],
            ['code' => 'EG-LUX', 'slug' => 'luxor', 'name' => ['lt' => 'Luksoras', 'en' => 'Luxor'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 25.6872, 'longitude' => 32.6396, 'population' => 506588],
            ['code' => 'EG-ASW', 'slug' => 'aswan', 'name' => ['lt' => 'Asuanas', 'en' => 'Aswan'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 24.0889, 'longitude' => 32.8998, 'population' => 350000],
            ['code' => 'EG-ASN', 'slug' => 'aswan', 'name' => ['lt' => 'Asiutas', 'en' => 'Asyut'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 27.1828, 'longitude' => 31.1828, 'population' => 389307],
            ['code' => 'EG-ISM', 'slug' => 'ismailia', 'name' => ['lt' => 'Ismailija', 'en' => 'Ismailia'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 30.6043, 'longitude' => 32.2723, 'population' => 750000],
            ['code' => 'EG-FAY', 'slug' => 'fayyum', 'name' => ['lt' => 'Fajumas', 'en' => 'Fayyum'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 29.3084, 'longitude' => 30.8428, 'population' => 350000],
            ['code' => 'EG-POR', 'slug' => 'port-said', 'name' => ['lt' => 'Port Said', 'en' => 'Port Said'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 31.2653, 'longitude' => 32.3019, 'population' => 750000],
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

        $this->command->info('Egypt cities seeded successfully.');
    }
}
