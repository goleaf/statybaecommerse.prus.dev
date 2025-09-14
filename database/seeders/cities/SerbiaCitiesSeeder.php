<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class SerbiaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'RS')->first();
        if (!$country) {
            $this->command->warn('Serbia country not found. Please run CountrySeeder first.');
            return;
        }

        $cities = [
            ['code' => 'RS-BEG', 'slug' => 'belgrade', 'name' => ['lt' => 'Belgradas', 'en' => 'Belgrade'], 'description' => ['lt' => 'Serbijos sostinė', 'en' => 'Capital of Serbia'], 'is_capital' => true, 'latitude' => 44.7866, 'longitude' => 20.4489, 'population' => 1378682],
            ['code' => 'RS-NOV', 'slug' => 'novi-sad', 'name' => ['lt' => 'Novi Sadas', 'en' => 'Novi Sad'], 'description' => ['lt' => 'Antras pagal dydį Serbijos miestas', 'en' => 'Second largest city in Serbia'], 'is_capital' => false, 'latitude' => 45.2671, 'longitude' => 19.8335, 'population' => 277522],
            ['code' => 'RS-NIS', 'slug' => 'nis', 'name' => ['lt' => 'Nišas', 'en' => 'Niš'], 'description' => ['lt' => 'Pietų Serbijos miestas', 'en' => 'Southern Serbian city'], 'is_capital' => false, 'latitude' => 43.3209, 'longitude' => 21.8958, 'population' => 183164],
            ['code' => 'RS-KRA', 'slug' => 'kragujevac', 'name' => ['lt' => 'Kragujevacas', 'en' => 'Kragujevac'], 'description' => ['lt' => 'Centrinės Serbijos miestas', 'en' => 'Central Serbian city'], 'is_capital' => false, 'latitude' => 44.0167, 'longitude' => 20.9167, 'population' => 150835],
            ['code' => 'RS-SUB', 'slug' => 'subotica', 'name' => ['lt' => 'Subotica', 'en' => 'Subotica'], 'description' => ['lt' => 'Šiaurės Serbijos miestas', 'en' => 'Northern Serbian city'], 'is_capital' => false, 'latitude' => 46.1000, 'longitude' => 19.6667, 'population' => 97000],
            ['code' => 'RS-ZRE', 'slug' => 'zrenjanin', 'name' => ['lt' => 'Zrenjaninas', 'en' => 'Zrenjanin'], 'description' => ['lt' => 'Vojvodinos miestas', 'en' => 'Vojvodina city'], 'is_capital' => false, 'latitude' => 45.3833, 'longitude' => 20.3833, 'population' => 76000],
            ['code' => 'RS-PAN', 'slug' => 'pancevo', 'name' => ['lt' => 'Pančevas', 'en' => 'Pančevo'], 'description' => ['lt' => 'Vojvodinos miestas', 'en' => 'Vojvodina city'], 'is_capital' => false, 'latitude' => 44.8667, 'longitude' => 20.6500, 'population' => 76000],
            ['code' => 'RS-CAC', 'slug' => 'cacak', 'name' => ['lt' => 'Čačakas', 'en' => 'Čačak'], 'description' => ['lt' => 'Centrinės Serbijos miestas', 'en' => 'Central Serbian city'], 'is_capital' => false, 'latitude' => 43.8914, 'longitude' => 20.3497, 'population' => 73000],
            ['code' => 'RS-NOV', 'slug' => 'novi-pazar', 'name' => ['lt' => 'Novi Pazaras', 'en' => 'Novi Pazar'], 'description' => ['lt' => 'Pietvakarių Serbijos miestas', 'en' => 'Southwestern Serbian city'], 'is_capital' => false, 'latitude' => 43.1367, 'longitude' => 20.5122, 'population' => 66000],
            ['code' => 'RS-KRA', 'slug' => 'kraljevo', 'name' => ['lt' => 'Kraljevas', 'en' => 'Kraljevo'], 'description' => ['lt' => 'Centrinės Serbijos miestas', 'en' => 'Central Serbian city'], 'is_capital' => false, 'latitude' => 43.7258, 'longitude' => 20.6894, 'population' => 64000],
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

        $this->command->info('Serbia cities seeded successfully.');
    }
}
