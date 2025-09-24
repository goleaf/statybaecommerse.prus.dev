<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class SaudiArabiaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'SA')->first();
        if (! $country) {
            $this->command->warn('Saudi Arabia country not found. Please run CountrySeeder first.');

            return;
        }

        $cities = [
            ['code' => 'SA-RUH', 'slug' => 'riyadh', 'name' => ['lt' => 'Rijadas', 'en' => 'Riyadh'], 'description' => 'Capital of Saudi Arabia', 'is_capital' => true, 'latitude' => 24.7136, 'longitude' => 46.6753, 'population' => 7676654],
            ['code' => 'SA-JED', 'slug' => 'jeddah', 'name' => ['lt' => 'Džeda', 'en' => 'Jeddah'], 'description' => 'Second largest city in Saudi Arabia', 'is_capital' => false, 'latitude' => 21.4858, 'longitude' => 39.1925, 'population' => 4700000],
            ['code' => 'SA-MEC', 'slug' => 'mecca', 'name' => ['lt' => 'Meka', 'en' => 'Mecca'], 'description' => 'Holy city of Islam', 'is_capital' => false, 'latitude' => 21.3891, 'longitude' => 39.8579, 'population' => 2040000],
            ['code' => 'SA-MED', 'slug' => 'medina', 'name' => ['lt' => 'Medina', 'en' => 'Medina'], 'description' => 'Second holiest city in Islam', 'is_capital' => false, 'latitude' => 24.5247, 'longitude' => 39.5692, 'population' => 1180770],
            ['code' => 'SA-DAM', 'slug' => 'dammam', 'name' => ['lt' => 'Dammamas', 'en' => 'Dammam'], 'description' => 'Eastern Province capital', 'is_capital' => false, 'latitude' => 26.4207, 'longitude' => 50.0888, 'population' => 1273000],
            ['code' => 'SA-KHO', 'slug' => 'khobar', 'name' => ['lt' => 'Chobaras', 'en' => 'Khobar'], 'description' => 'Eastern Province city', 'is_capital' => false, 'latitude' => 26.2172, 'longitude' => 50.1971, 'population' => 165799],
            ['code' => 'SA-TAI', 'slug' => 'taif', 'name' => ['lt' => 'Taifas', 'en' => 'Taif'], 'description' => 'Makkah Province city', 'is_capital' => false, 'latitude' => 21.2703, 'longitude' => 40.4158, 'population' => 1200000],
            ['code' => 'SA-BUR', 'slug' => 'buraydah', 'name' => ['lt' => 'Buraida', 'en' => 'Buraydah'], 'description' => 'Qassim Province capital', 'is_capital' => false, 'latitude' => 26.3260, 'longitude' => 43.9750, 'population' => 614093],
            ['code' => 'SA-TAB', 'slug' => 'tabuk', 'name' => ['lt' => 'Tabukas', 'en' => 'Tabuk'], 'description' => 'Tabuk Province capital', 'is_capital' => false, 'latitude' => 28.3998, 'longitude' => 36.5700, 'population' => 569797],
            ['code' => 'SA-HAI', 'slug' => 'hail', 'name' => ['lt' => 'Hailas', 'en' => 'Hail'], 'description' => 'Hail Province capital', 'is_capital' => false, 'latitude' => 27.5114, 'longitude' => 41.6903, 'population' => 412758],
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

        $this->command->info('Saudi Arabia cities seeded successfully.');
    }
}
