<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class SwitzerlandCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'CH')->first();
        if (!$country) {
            $this->command->warn('Switzerland country not found. Please run CountrySeeder first.');
            return;
        }

        $cities = [
            ['code' => 'CH-ZUR', 'slug' => 'zurich', 'name' => ['lt' => 'Ciurichas', 'en' => 'Zurich'], 'description' => 'Largest city in Switzerland', 'is_capital' => false, 'latitude' => 47.3769, 'longitude' => 8.5417, 'population' => 421878],
            ['code' => 'CH-BER', 'slug' => 'bern', 'name' => ['lt' => 'Bernas', 'en' => 'Bern'], 'description' => 'Capital of Switzerland', 'is_capital' => true, 'latitude' => 46.9481, 'longitude' => 7.4474, 'population' => 133883],
            ['code' => 'CH-BAS', 'slug' => 'basel', 'name' => ['lt' => 'Bazelis', 'en' => 'Basel'], 'description' => 'Northwestern Swiss city', 'is_capital' => false, 'latitude' => 47.5596, 'longitude' => 7.5886, 'population' => 175940],
            ['code' => 'CH-GEN', 'slug' => 'geneva', 'name' => ['lt' => 'Ženeva', 'en' => 'Geneva'], 'description' => 'Center of international organizations', 'is_capital' => false, 'latitude' => 46.2044, 'longitude' => 6.1432, 'population' => 203951],
            ['code' => 'CH-LAU', 'slug' => 'lausanne', 'name' => ['lt' => 'Lozana', 'en' => 'Lausanne'], 'description' => 'Olympic Games city', 'is_capital' => false, 'latitude' => 46.5197, 'longitude' => 6.6323, 'population' => 140202],
            ['code' => 'CH-LUC', 'slug' => 'lucerne', 'name' => ['lt' => 'Liucerna', 'en' => 'Lucerne'], 'description' => 'Tourist city by the lake', 'is_capital' => false, 'latitude' => 47.0502, 'longitude' => 8.3093, 'population' => 81691],
            ['code' => 'CH-STG', 'slug' => 'st-gallen', 'name' => ['lt' => 'Šv. Galenas', 'en' => 'St. Gallen'], 'description' => 'Eastern Swiss city', 'is_capital' => false, 'latitude' => 47.4245, 'longitude' => 9.3767, 'population' => 75833],
            ['code' => 'CH-LUG', 'slug' => 'lugano', 'name' => ['lt' => 'Luganas', 'en' => 'Lugano'], 'description' => 'Southern Swiss city', 'is_capital' => false, 'latitude' => 46.0101, 'longitude' => 8.9600, 'population' => 63000],
            ['code' => 'CH-BIE', 'slug' => 'biel', 'name' => ['lt' => 'Bielas', 'en' => 'Biel'], 'description' => 'Bilingual city', 'is_capital' => false, 'latitude' => 47.1371, 'longitude' => 7.2471, 'population' => 55000],
            ['code' => 'CH-THU', 'slug' => 'thun', 'name' => ['lt' => 'Tunas', 'en' => 'Thun'], 'description' => 'City by Lake Thun', 'is_capital' => false, 'latitude' => 46.7580, 'longitude' => 7.6280, 'population' => 45000],
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

        $this->command->info('Switzerland cities seeded successfully.');
    }
}
