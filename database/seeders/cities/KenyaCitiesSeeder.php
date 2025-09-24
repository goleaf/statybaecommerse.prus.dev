<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class KenyaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'KE')->first();
        if (! $country) {
            $this->command->warn('Kenya country not found. Please run CountrySeeder first.');

            return;
        }

        $cities = [
            ['code' => 'KE-NAI', 'slug' => 'nairobi', 'name' => ['lt' => 'Nairobis', 'en' => 'Nairobi'], 'description' => 'Capital of Kenya', 'is_capital' => true, 'latitude' => -1.2921, 'longitude' => 36.8219, 'population' => 4397073],
            ['code' => 'KE-MOM', 'slug' => 'mombasa', 'name' => ['lt' => 'Mombasa', 'en' => 'Mombasa'], 'description' => 'Second largest city in Kenya', 'is_capital' => false, 'latitude' => -4.0437, 'longitude' => 39.6682, 'population' => 1200000],
            ['code' => 'KE-KIS', 'slug' => 'kisumu', 'name' => ['lt' => 'Kisumu', 'en' => 'Kisumu'], 'description' => 'Third largest city in Kenya', 'is_capital' => false, 'latitude' => -0.0917, 'longitude' => 34.7680, 'population' => 610000],
            ['code' => 'KE-NAK', 'slug' => 'nakuru', 'name' => ['lt' => 'Nakuru', 'en' => 'Nakuru'], 'description' => 'Rift Valley city', 'is_capital' => false, 'latitude' => -0.3072, 'longitude' => 36.0800, 'population' => 570000],
            ['code' => 'KE-ELD', 'slug' => 'eldoret', 'name' => ['lt' => 'Eldoretas', 'en' => 'Eldoret'], 'description' => 'Uasin Gishu county capital', 'is_capital' => false, 'latitude' => 0.5143, 'longitude' => 35.2698, 'population' => 475000],
            ['code' => 'KE-KER', 'slug' => 'kericho', 'name' => ['lt' => 'Kericho', 'en' => 'Kericho'], 'description' => 'Tea growing region', 'is_capital' => false, 'latitude' => -0.3667, 'longitude' => 35.2833, 'population' => 150000],
            ['code' => 'KE-KAK', 'slug' => 'kakamega', 'name' => ['lt' => 'Kakamega', 'en' => 'Kakamega'], 'description' => 'Western Kenya city', 'is_capital' => false, 'latitude' => 0.2833, 'longitude' => 34.7500, 'population' => 120000],
            ['code' => 'KE-THU', 'slug' => 'thika', 'name' => ['lt' => 'Tika', 'en' => 'Thika'], 'description' => 'Industrial city', 'is_capital' => false, 'latitude' => -1.0500, 'longitude' => 37.0833, 'population' => 200000],
            ['code' => 'KE-MAL', 'slug' => 'malindi', 'name' => ['lt' => 'Malindis', 'en' => 'Malindi'], 'description' => 'Coastal resort city', 'is_capital' => false, 'latitude' => -3.2167, 'longitude' => 40.1167, 'population' => 120000],
            ['code' => 'KE-GAR', 'slug' => 'garissa', 'name' => ['lt' => 'Garisa', 'en' => 'Garissa'], 'description' => 'North Eastern Kenya city', 'is_capital' => false, 'latitude' => -0.4500, 'longitude' => 39.6500, 'population' => 100000],
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

        $this->command->info('Kenya cities seeded successfully.');
    }
}
