<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class IsraelCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'IL')->first();
        if (! $country) {
            $this->command->warn('Israel country not found. Please run CountrySeeder first.');

            return;
        }

        $cities = [
            ['code' => 'IL-JER', 'slug' => 'jerusalem', 'name' => ['lt' => 'Jeruzalė', 'en' => 'Jerusalem'], 'description' => 'Capital of ', 'is_capital' => true, 'latitude' => 31.7683, 'longitude' => 35.2137, 'population' => 936425],
            ['code' => 'IL-TEL', 'slug' => 'tel-aviv', 'name' => ['lt' => 'Tel Avivas', 'en' => 'Tel Aviv'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 32.0853, 'longitude' => 34.7818, 'population' => 460613],
            ['code' => 'IL-HAI', 'slug' => 'haifa', 'name' => ['lt' => 'Haifa', 'en' => 'Haifa'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 32.7940, 'longitude' => 34.9896, 'population' => 285316],
            ['code' => 'IL-RIS', 'slug' => 'rishon-lezion', 'name' => ['lt' => 'Rišon Lezionas', 'en' => 'Rishon LeZion'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 31.9640, 'longitude' => 34.8016, 'population' => 249860],
            ['code' => 'IL-PET', 'slug' => 'petah-tikva', 'name' => ['lt' => 'Petach Tikva', 'en' => 'Petah Tikva'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 32.0889, 'longitude' => 34.8861, 'population' => 244275],
            ['code' => 'IL-ASH', 'slug' => 'ashdod', 'name' => ['lt' => 'Ašdodas', 'en' => 'Ashdod'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 31.8044, 'longitude' => 34.6553, 'population' => 225939],
            ['code' => 'IL-NET', 'slug' => 'netanya', 'name' => ['lt' => 'Netanija', 'en' => 'Netanya'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 32.3215, 'longitude' => 34.8532, 'population' => 217244],
            ['code' => 'IL-BEE', 'slug' => 'beer-sheva', 'name' => ['lt' => 'Beer Ševa', 'en' => 'Beer Sheva'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 31.2518, 'longitude' => 34.7915, 'population' => 209687],
            ['code' => 'IL-HOL', 'slug' => 'holon', 'name' => ['lt' => 'Holonas', 'en' => 'Holon'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 32.0103, 'longitude' => 34.7792, 'population' => 194273],
            ['code' => 'IL-BAT', 'slug' => 'bat-yam', 'name' => ['lt' => 'Bat Jam', 'en' => 'Bat Yam'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 32.0131, 'longitude' => 34.7480, 'population' => 128655],
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

        $this->command->info('Israel cities seeded successfully.');
    }
}
