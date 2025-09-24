<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class MalaysiaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'MY')->first();
        if (! $country) {
            $this->command->warn('Malaysia country not found. Please run CountrySeeder first.');

            return;
        }

        $cities = [
            ['code' => 'MY-KUL', 'slug' => 'kuala-lumpur', 'name' => ['lt' => 'Kvala Lumpūras', 'en' => 'Kuala Lumpur'], 'description' => 'Capital of Malaysia', 'is_capital' => true, 'latitude' => 3.1390, 'longitude' => 101.6869, 'population' => 1588750],
            ['code' => 'MY-JOH', 'slug' => 'johor-bahru', 'name' => ['lt' => 'Johor Bahru', 'en' => 'Johor Bahru'], 'description' => 'Johor state capital', 'is_capital' => false, 'latitude' => 1.4927, 'longitude' => 103.7414, 'population' => 497067],
            ['code' => 'MY-PEN', 'slug' => 'george-town', 'name' => ['lt' => 'Džordž Taunas', 'en' => 'George Town'], 'description' => 'Penang state capital', 'is_capital' => false, 'latitude' => 5.4164, 'longitude' => 100.3327, 'population' => 708127],
            ['code' => 'MY-IPH', 'slug' => 'ipoh', 'name' => ['lt' => 'Ipochas', 'en' => 'Ipoh'], 'description' => 'Perak state capital', 'is_capital' => false, 'latitude' => 4.5841, 'longitude' => 101.0829, 'population' => 737861],
            ['code' => 'MY-SHA', 'slug' => 'shah-alam', 'name' => ['lt' => 'Šah Alamas', 'en' => 'Shah Alam'], 'description' => 'Selangor state capital', 'is_capital' => false, 'latitude' => 3.0733, 'longitude' => 101.5185, 'population' => 584340],
            ['code' => 'MY-KOT', 'slug' => 'kota-kinabalu', 'name' => ['lt' => 'Kota Kinabalu', 'en' => 'Kota Kinabalu'], 'description' => 'Sabah state capital', 'is_capital' => false, 'latitude' => 5.9804, 'longitude' => 116.0735, 'population' => 452058],
            ['code' => 'MY-KUC', 'slug' => 'kuching', 'name' => ['lt' => 'Kučingas', 'en' => 'Kuching'], 'description' => 'Sarawak state capital', 'is_capital' => false, 'latitude' => 1.5533, 'longitude' => 110.3592, 'population' => 325132],
            ['code' => 'MY-KUA', 'slug' => 'kuantan', 'name' => ['lt' => 'Kuantanas', 'en' => 'Kuantan'], 'description' => 'Pahang state capital', 'is_capital' => false, 'latitude' => 3.8077, 'longitude' => 103.3260, 'population' => 427515],
            ['code' => 'MY-MEL', 'slug' => 'melaka', 'name' => ['lt' => 'Malaka', 'en' => 'Melaka'], 'description' => 'Malacca state capital', 'is_capital' => false, 'latitude' => 2.1896, 'longitude' => 102.2501, 'population' => 455300],
            ['code' => 'MY-ALO', 'slug' => 'alor-setar', 'name' => ['lt' => 'Alor Setaras', 'en' => 'Alor Setar'], 'description' => 'Kedah state capital', 'is_capital' => false, 'latitude' => 6.1167, 'longitude' => 100.3667, 'population' => 217000],
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

        $this->command->info('Malaysia cities seeded successfully.');
    }
}
