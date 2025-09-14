<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class TurkeyCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'TR')->first();
        if (!$country) {
            $this->command->warn('Turkey country not found. Please run CountrySeeder first.');
            return;
        }

        $cities = [
            ['code' => 'TR-IST', 'slug' => 'istanbul', 'name' => ['lt' => 'Stambulas', 'en' => 'Istanbul'], 'description' => ['lt' => 'Didžiausias Turkijos miestas', 'en' => 'Largest city in Turkey'], 'is_capital' => false, 'latitude' => 41.0082, 'longitude' => 28.9784, 'population' => 15519267],
            ['code' => 'TR-ANK', 'slug' => 'ankara', 'name' => ['lt' => 'Ankara', 'en' => 'Ankara'], 'description' => ['lt' => 'Turkijos sostinė', 'en' => 'Capital of Turkey'], 'is_capital' => true, 'latitude' => 39.9334, 'longitude' => 32.8597, 'population' => 5503985],
            ['code' => 'TR-IZM', 'slug' => 'izmir', 'name' => ['lt' => 'Izmiras', 'en' => 'İzmir'], 'description' => ['lt' => 'Antras pagal dydį Turkijos miestas', 'en' => 'Second largest city in Turkey'], 'is_capital' => false, 'latitude' => 38.4192, 'longitude' => 27.1287, 'population' => 4367251],
            ['code' => 'TR-BUR', 'slug' => 'bursa', 'name' => ['lt' => 'Bursa', 'en' => 'Bursa'], 'description' => ['lt' => 'Marmaros regiono miestas', 'en' => 'Marmara region city'], 'is_capital' => false, 'latitude' => 40.1826, 'longitude' => 29.0665, 'population' => 3053331],
            ['code' => 'TR-ANT', 'slug' => 'antalya', 'name' => ['lt' => 'Antalija', 'en' => 'Antalya'], 'description' => ['lt' => 'Kurortinis miestas', 'en' => 'Resort city'], 'is_capital' => false, 'latitude' => 36.8969, 'longitude' => 30.7133, 'population' => 2426356],
            ['code' => 'TR-ADA', 'slug' => 'adana', 'name' => ['lt' => 'Adana', 'en' => 'Adana'], 'description' => ['lt' => 'Pietų Turkijos miestas', 'en' => 'Southern Turkish city'], 'is_capital' => false, 'latitude' => 37.0000, 'longitude' => 35.3213, 'population' => 2220125],
            ['code' => 'TR-KON', 'slug' => 'konya', 'name' => ['lt' => 'Konija', 'en' => 'Konya'], 'description' => ['lt' => 'Centrinės Turkijos miestas', 'en' => 'Central Turkish city'], 'is_capital' => false, 'latitude' => 37.8667, 'longitude' => 32.4833, 'population' => 2232374],
            ['code' => 'TR-GAZ', 'slug' => 'gaziantep', 'name' => ['lt' => 'Gaziantepas', 'en' => 'Gaziantep'], 'description' => ['lt' => 'Pietryčių Turkijos miestas', 'en' => 'Southeastern Turkish city'], 'is_capital' => false, 'latitude' => 37.0662, 'longitude' => 37.3833, 'population' => 2028563],
            ['code' => 'TR-MER', 'slug' => 'mersin', 'name' => ['lt' => 'Mersinas', 'en' => 'Mersin'], 'description' => ['lt' => 'Uostamiestis', 'en' => 'Port city'], 'is_capital' => false, 'latitude' => 36.8000, 'longitude' => 34.6333, 'population' => 1814468],
            ['code' => 'TR-DIA', 'slug' => 'diyarbakir', 'name' => ['lt' => 'Dijarbakiras', 'en' => 'Diyarbakır'], 'description' => ['lt' => 'Pietryčių Turkijos miestas', 'en' => 'Southeastern Turkish city'], 'is_capital' => false, 'latitude' => 37.9144, 'longitude' => 40.2306, 'population' => 1750000],
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

        $this->command->info('Turkey cities seeded successfully.');
    }
}
