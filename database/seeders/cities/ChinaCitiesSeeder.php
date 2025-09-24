<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class ChinaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'CN')->first();
        if (! $country) {
            $this->command->warn('China country not found. Please run CountrySeeder first.');

            return;
        }

        $cities = [
            ['code' => 'CN-BEI', 'slug' => 'beijing', 'name' => ['lt' => 'Pekinas', 'en' => 'Beijing'], 'description' => 'Capital of ', 'is_capital' => true, 'latitude' => 39.9042, 'longitude' => 116.4074, 'population' => 21540000],
            ['code' => 'CN-SHA', 'slug' => 'shanghai', 'name' => ['lt' => 'Šanchajus', 'en' => 'Shanghai'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 31.2304, 'longitude' => 121.4737, 'population' => 24870895],
            ['code' => 'CN-GUA', 'slug' => 'guangzhou', 'name' => ['lt' => 'Guangdžou', 'en' => 'Guangzhou'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 23.1291, 'longitude' => 113.2644, 'population' => 15305924],
            ['code' => 'CN-SHE', 'slug' => 'shenzhen', 'name' => ['lt' => 'Šendženas', 'en' => 'Shenzhen'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 22.5431, 'longitude' => 114.0579, 'population' => 17562841],
            ['code' => 'CN-TIA', 'slug' => 'tianjin', 'name' => ['lt' => 'Tiandzinas', 'en' => 'Tianjin'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 39.3434, 'longitude' => 117.3616, 'population' => 13866009],
            ['code' => 'CN-WUH', 'slug' => 'wuhan', 'name' => ['lt' => 'Vuhanas', 'en' => 'Wuhan'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 30.5928, 'longitude' => 114.3055, 'population' => 12326318],
            ['code' => 'CN-XIA', 'slug' => 'xian', 'name' => ['lt' => 'Sianas', 'en' => 'Xi\'an'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 34.3416, 'longitude' => 108.9398, 'population' => 12952948],
            ['code' => 'CN-CHO', 'slug' => 'chongqing', 'name' => ['lt' => 'Čongčingas', 'en' => 'Chongqing'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 29.4316, 'longitude' => 106.9123, 'population' => 32054159],
            ['code' => 'CN-NAN', 'slug' => 'nanjing', 'name' => ['lt' => 'Nandzingas', 'en' => 'Nanjing'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 32.0603, 'longitude' => 118.7969, 'population' => 9314685],
            ['code' => 'CN-CHN', 'slug' => 'chengdu', 'name' => ['lt' => 'Čengdu', 'en' => 'Chengdu'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 30.5728, 'longitude' => 104.0668, 'population' => 20937757],
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

        $this->command->info('China cities seeded successfully.');
    }
}
