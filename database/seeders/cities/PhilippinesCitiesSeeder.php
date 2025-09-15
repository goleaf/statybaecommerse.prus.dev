<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class PhilippinesCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'PH')->first();
        if (!$country) {
            $this->command->warn('Philippines country not found. Please run CountrySeeder first.');
            return;
        }

        $cities = [
            ['code' => 'PH-MNL', 'slug' => 'manila', 'name' => ['lt' => 'Manila', 'en' => 'Manila'], 'description' => 'Capital of Philippines', 'is_capital' => true, 'latitude' => 14.5995, 'longitude' => 120.9842, 'population' => 1780148],
            ['code' => 'PH-QUE', 'slug' => 'quezon-city', 'name' => ['lt' => 'Kezon miestas', 'en' => 'Quezon City'], 'description' => 'Largest city in Philippines', 'is_capital' => false, 'latitude' => 14.6760, 'longitude' => 121.0437, 'population' => 2960048],
            ['code' => 'PH-CEB', 'slug' => 'cebu-city', 'name' => ['lt' => 'Cebu miestas', 'en' => 'Cebu City'], 'description' => 'Second largest city in Philippines', 'is_capital' => false, 'latitude' => 10.3157, 'longitude' => 123.8854, 'population' => 922611],
            ['code' => 'PH-DAV', 'slug' => 'davao-city', 'name' => ['lt' => 'Davao miestas', 'en' => 'Davao City'], 'description' => 'Third largest city in Philippines', 'is_capital' => false, 'latitude' => 7.0731, 'longitude' => 125.6128, 'population' => 1632991],
            ['code' => 'PH-ZAM', 'slug' => 'zamboanga-city', 'name' => ['lt' => 'Zamboanga miestas', 'en' => 'Zamboanga City'], 'description' => 'Zamboanga Peninsula city', 'is_capital' => false, 'latitude' => 6.9214, 'longitude' => 122.0790, 'population' => 861799],
            ['code' => 'PH-ANT', 'slug' => 'antipolo', 'name' => ['lt' => 'Antipolo', 'en' => 'Antipolo'], 'description' => 'Rizal province city', 'is_capital' => false, 'latitude' => 14.5864, 'longitude' => 121.1753, 'population' => 887399],
            ['code' => 'PH-PAS', 'slug' => 'pasig', 'name' => ['lt' => 'Pasigas', 'en' => 'Pasig'], 'description' => 'Metro Manila city', 'is_capital' => false, 'latitude' => 14.5764, 'longitude' => 121.0851, 'population' => 803159],
            ['code' => 'PH-TAG', 'slug' => 'taguig', 'name' => ['lt' => 'Taguigas', 'en' => 'Taguig'], 'description' => 'Metro Manila city', 'is_capital' => false, 'latitude' => 14.5176, 'longitude' => 121.0509, 'population' => 886722],
            ['code' => 'PH-VAL', 'slug' => 'valenzuela', 'name' => ['lt' => 'Valenzuela', 'en' => 'Valenzuela'], 'description' => 'Metro Manila city', 'is_capital' => false, 'latitude' => 14.6969, 'longitude' => 120.9821, 'population' => 714978],
            ['code' => 'PH-PAR', 'slug' => 'paranaque', 'name' => ['lt' => 'Paranaque', 'en' => 'Parañaque'], 'description' => 'Metro Manila city', 'is_capital' => false, 'latitude' => 14.4793, 'longitude' => 121.0198, 'population' => 689992],
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

        $this->command->info('Philippines cities seeded successfully.');
    }
}
