<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class BulgariaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'BG')->first();
        if (!$country) {
            $this->command->warn('Bulgaria country not found. Please run CountrySeeder first.');
            return;
        }

        $cities = [
            ['code' => 'BG-SOF', 'slug' => 'sofia', 'name' => ['lt' => 'Sofija', 'en' => 'Sofia'], 'description' => 'Capital of ', 'is_capital' => true, 'latitude' => 42.6977, 'longitude' => 23.3219, 'population' => 1241675],
            ['code' => 'BG-PLO', 'slug' => 'plovdiv', 'name' => ['lt' => 'Plovdivas', 'en' => 'Plovdiv'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 42.1354, 'longitude' => 24.7453, 'population' => 346893],
            ['code' => 'BG-VAR', 'slug' => 'varna', 'name' => ['lt' => 'Varna', 'en' => 'Varna'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 43.2141, 'longitude' => 27.9147, 'population' => 335177],
            ['code' => 'BG-BUR', 'slug' => 'burgas', 'name' => ['lt' => 'Burgasas', 'en' => 'Burgas'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 42.5048, 'longitude' => 27.4626, 'population' => 203017],
            ['code' => 'BG-RUS', 'slug' => 'ruse', 'name' => ['lt' => 'Rusė', 'en' => 'Ruse'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 43.8564, 'longitude' => 25.9564, 'population' => 144936],
            ['code' => 'BG-STA', 'slug' => 'stara-zagora', 'name' => ['lt' => 'Stara Zagora', 'en' => 'Stara Zagora'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 42.4258, 'longitude' => 25.6344, 'population' => 138272],
            ['code' => 'BG-PLE', 'slug' => 'pleven', 'name' => ['lt' => 'Plevenas', 'en' => 'Pleven'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 43.4170, 'longitude' => 24.6067, 'population' => 106954],
            ['code' => 'BG-SLI', 'slug' => 'sliven', 'name' => ['lt' => 'Slivenas', 'en' => 'Sliven'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 42.6858, 'longitude' => 26.3292, 'population' => 91000],
            ['code' => 'BG-DOB', 'slug' => 'dobrich', 'name' => ['lt' => 'Dobričas', 'en' => 'Dobrich'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 43.5726, 'longitude' => 27.8273, 'population' => 90000],
            ['code' => 'BG-SHU', 'slug' => 'shumen', 'name' => ['lt' => 'Šumenas', 'en' => 'Shumen'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 43.2706, 'longitude' => 26.9361, 'population' => 80000],
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

        $this->command->info('Bulgaria cities seeded successfully.');
    }
}
