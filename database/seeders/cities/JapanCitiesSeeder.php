<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class JapanCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'JP')->first();
        if (!$country) {
            $this->command->warn('Japan country not found. Please run CountrySeeder first.');
            return;
        }

        $cities = [
            ['code' => 'JP-TOK', 'slug' => 'tokyo', 'name' => ['lt' => 'Tokijas', 'en' => 'Tokyo'], 'description' => ['lt' => 'Japonijos sostinė', 'en' => 'Capital of Japan'], 'is_capital' => true, 'latitude' => 35.6762, 'longitude' => 139.6503, 'population' => 13929286],
            ['code' => 'JP-YOK', 'slug' => 'yokohama', 'name' => ['lt' => 'Jokohama', 'en' => 'Yokohama'], 'description' => ['lt' => 'Antras pagal dydį Japonijos miestas', 'en' => 'Second largest city in Japan'], 'is_capital' => false, 'latitude' => 35.4437, 'longitude' => 139.6380, 'population' => 3757630],
            ['code' => 'JP-OSA', 'slug' => 'osaka', 'name' => ['lt' => 'Osaka', 'en' => 'Osaka'], 'description' => ['lt' => 'Kansajaus regiono centras', 'en' => 'Kansai region center'], 'is_capital' => false, 'latitude' => 34.6937, 'longitude' => 135.5023, 'population' => 2691185],
            ['code' => 'JP-NAG', 'slug' => 'nagoya', 'name' => ['lt' => 'Nagaja', 'en' => 'Nagoya'], 'description' => ['lt' => 'Čiubos regiono centras', 'en' => 'Chubu region center'], 'is_capital' => false, 'latitude' => 35.1815, 'longitude' => 136.9066, 'population' => 2295638],
            ['code' => 'JP-SAP', 'slug' => 'sapporo', 'name' => ['lt' => 'Saporo', 'en' => 'Sapporo'], 'description' => ['lt' => 'Hokaido sostinė', 'en' => 'Capital of Hokkaido'], 'is_capital' => false, 'latitude' => 43.0642, 'longitude' => 141.3469, 'population' => 1973115],
            ['code' => 'JP-FUK', 'slug' => 'fukuoka', 'name' => ['lt' => 'Fukuoka', 'en' => 'Fukuoka'], 'description' => ['lt' => 'Kiušiu regiono centras', 'en' => 'Kyushu region center'], 'is_capital' => false, 'latitude' => 33.5904, 'longitude' => 130.4017, 'population' => 1607681],
            ['code' => 'JP-KOB', 'slug' => 'kobe', 'name' => ['lt' => 'Kobė', 'en' => 'Kobe'], 'description' => ['lt' => 'Kansajaus regiono miestas', 'en' => 'Kansai region city'], 'is_capital' => false, 'latitude' => 34.6901, 'longitude' => 135.1956, 'population' => 1523928],
            ['code' => 'JP-KAW', 'slug' => 'kawasaki', 'name' => ['lt' => 'Kavasaki', 'en' => 'Kawasaki'], 'description' => ['lt' => 'Kanagavos prefektūros miestas', 'en' => 'Kanagawa prefecture city'], 'is_capital' => false, 'latitude' => 35.5307, 'longitude' => 139.7029, 'population' => 1536762],
            ['code' => 'JP-KYO', 'slug' => 'kyoto', 'name' => ['lt' => 'Kiotas', 'en' => 'Kyoto'], 'description' => ['lt' => 'Istorinis Japonijos miestas', 'en' => 'Historic Japanese city'], 'is_capital' => false, 'latitude' => 35.0116, 'longitude' => 135.7681, 'population' => 1464890],
            ['code' => 'JP-SAI', 'slug' => 'saitama', 'name' => ['lt' => 'Saitama', 'en' => 'Saitama'], 'description' => ['lt' => 'Saitamos prefektūros sostinė', 'en' => 'Capital of Saitama prefecture'], 'is_capital' => false, 'latitude' => 35.8617, 'longitude' => 139.6455, 'population' => 1329804],
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

        $this->command->info('Japan cities seeded successfully.');
    }
}
