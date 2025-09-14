<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class IndiaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'IN')->first();
        if (!$country) {
            $this->command->warn('India country not found. Please run CountrySeeder first.');
            return;
        }

        $cities = [
            ['code' => 'IN-DEL', 'slug' => 'new-delhi', 'name' => ['lt' => 'Naujasis Delis', 'en' => 'New Delhi'], 'description' => ['lt' => 'Indijos sostinė', 'en' => 'Capital of India'], 'is_capital' => true, 'latitude' => 28.6139, 'longitude' => 77.2090, 'population' => 32941000],
            ['code' => 'IN-MUM', 'slug' => 'mumbai', 'name' => ['lt' => 'Mumbajus', 'en' => 'Mumbai'], 'description' => ['lt' => 'Didžiausias Indijos miestas', 'en' => 'Largest city in India'], 'is_capital' => false, 'latitude' => 19.0760, 'longitude' => 72.8777, 'population' => 20411274],
            ['code' => 'IN-BAN', 'slug' => 'bangalore', 'name' => ['lt' => 'Bangalūras', 'en' => 'Bangalore'], 'description' => ['lt' => 'Technologijų centras', 'en' => 'Technology center'], 'is_capital' => false, 'latitude' => 12.9716, 'longitude' => 77.5946, 'population' => 12425304],
            ['code' => 'IN-HYD', 'slug' => 'hyderabad', 'name' => ['lt' => 'Haiderabadas', 'en' => 'Hyderabad'], 'description' => ['lt' => 'Telanganos sostinė', 'en' => 'Capital of Telangana'], 'is_capital' => false, 'latitude' => 17.3850, 'longitude' => 78.4867, 'population' => 10494000],
            ['code' => 'IN-AHM', 'slug' => 'ahmedabad', 'name' => ['lt' => 'Ahmadabadas', 'en' => 'Ahmedabad'], 'description' => ['lt' => 'Gudžarato miestas', 'en' => 'Gujarat city'], 'is_capital' => false, 'latitude' => 23.0225, 'longitude' => 72.5714, 'population' => 8000000],
            ['code' => 'IN-CHE', 'slug' => 'chennai', 'name' => ['lt' => 'Čenajus', 'en' => 'Chennai'], 'description' => ['lt' => 'Tamilnado sostinė', 'en' => 'Capital of Tamil Nadu'], 'is_capital' => false, 'latitude' => 13.0827, 'longitude' => 80.2707, 'population' => 11250000],
            ['code' => 'IN-KOL', 'slug' => 'kolkata', 'name' => ['lt' => 'Kalkuta', 'en' => 'Kolkata'], 'description' => ['lt' => 'Vakarų Bengalijos sostinė', 'en' => 'Capital of West Bengal'], 'is_capital' => false, 'latitude' => 22.5726, 'longitude' => 88.3639, 'population' => 14974073],
            ['code' => 'IN-SUR', 'slug' => 'surat', 'name' => ['lt' => 'Suratas', 'en' => 'Surat'], 'description' => ['lt' => 'Gudžarato miestas', 'en' => 'Gujarat city'], 'is_capital' => false, 'latitude' => 21.1702, 'longitude' => 72.8311, 'population' => 6000000],
            ['code' => 'IN-PUN', 'slug' => 'pune', 'name' => ['lt' => 'Punė', 'en' => 'Pune'], 'description' => ['lt' => 'Maharaštros miestas', 'en' => 'Maharashtra city'], 'is_capital' => false, 'latitude' => 18.5204, 'longitude' => 73.8567, 'population' => 7000000],
            ['code' => 'IN-JAI', 'slug' => 'jaipur', 'name' => ['lt' => 'Džaipuras', 'en' => 'Jaipur'], 'description' => ['lt' => 'Radžastano sostinė', 'en' => 'Capital of Rajasthan'], 'is_capital' => false, 'latitude' => 26.9124, 'longitude' => 75.7873, 'population' => 3073350],
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

        $this->command->info('India cities seeded successfully.');
    }
}
