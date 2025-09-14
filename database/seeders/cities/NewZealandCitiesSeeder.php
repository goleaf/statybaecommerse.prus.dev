<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class NewZealandCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'NZ')->first();
        if (!$country) {
            $this->command->warn('New Zealand country not found. Please run CountrySeeder first.');
            return;
        }

        $cities = [
            ['code' => 'NZ-AUC', 'slug' => 'auckland', 'name' => ['lt' => 'Oklendas', 'en' => 'Auckland'], 'description' => ['lt' => 'Didžiausias Naujosios Zelandijos miestas', 'en' => 'Largest city in New Zealand'], 'is_capital' => false, 'latitude' => -36.8485, 'longitude' => 174.7633, 'population' => 1657000],
            ['code' => 'NZ-WEL', 'slug' => 'wellington', 'name' => ['lt' => 'Velingtonas', 'en' => 'Wellington'], 'description' => ['lt' => 'Naujosios Zelandijos sostinė', 'en' => 'Capital of New Zealand'], 'is_capital' => true, 'latitude' => -41.2865, 'longitude' => 174.7762, 'population' => 212700],
            ['code' => 'NZ-CHC', 'slug' => 'christchurch', 'name' => ['lt' => 'Kristčerčas', 'en' => 'Christchurch'], 'description' => ['lt' => 'Antras pagal dydį Naujosios Zelandijos miestas', 'en' => 'Second largest city in New Zealand'], 'is_capital' => false, 'latitude' => -43.5321, 'longitude' => 172.6362, 'population' => 396200],
            ['code' => 'NZ-HAM', 'slug' => 'hamilton', 'name' => ['lt' => 'Hamiltonas', 'en' => 'Hamilton'], 'description' => ['lt' => 'Vakaru Kosto regiono centras', 'en' => 'Waikato region center'], 'is_capital' => false, 'latitude' => -37.7870, 'longitude' => 175.2793, 'population' => 176500],
            ['code' => 'NZ-TAU', 'slug' => 'tauranga', 'name' => ['lt' => 'Tauranga', 'en' => 'Tauranga'], 'description' => ['lt' => 'Baj of Plenty regiono miestas', 'en' => 'Bay of Plenty region city'], 'is_capital' => false, 'latitude' => -37.6878, 'longitude' => 176.1651, 'population' => 151300],
            ['code' => 'NZ-LOW', 'slug' => 'lower-hutt', 'name' => ['lt' => 'Žemesnysis Hatas', 'en' => 'Lower Hutt'], 'description' => ['lt' => 'Velingtono regiono miestas', 'en' => 'Wellington region city'], 'is_capital' => false, 'latitude' => -41.2167, 'longitude' => 174.9167, 'population' => 110700],
            ['code' => 'NZ-DUN', 'slug' => 'dunedin', 'name' => ['lt' => 'Dunedinas', 'en' => 'Dunedin'], 'description' => ['lt' => 'Otago regiono centras', 'en' => 'Otago region center'], 'is_capital' => false, 'latitude' => -45.8788, 'longitude' => 170.5028, 'population' => 130400],
            ['code' => 'NZ-PAL', 'slug' => 'palmerston-north', 'name' => ['lt' => 'Palmerston Nortas', 'en' => 'Palmerston North'], 'description' => ['lt' => 'Manavatu-Vanganui regiono centras', 'en' => 'Manawatu-Wanganui region center'], 'is_capital' => false, 'latitude' => -40.3523, 'longitude' => 175.6082, 'population' => 87000],
            ['code' => 'NZ-NAP', 'slug' => 'napier', 'name' => ['lt' => 'Neipieris', 'en' => 'Napier'], 'description' => ['lt' => 'Hokso Bėjaus regiono miestas', 'en' => 'Hawke\'s Bay region city'], 'is_capital' => false, 'latitude' => -39.4928, 'longitude' => 176.9120, 'population' => 64000],
            ['code' => 'NZ-ROT', 'slug' => 'rotorua', 'name' => ['lt' => 'Rotorua', 'en' => 'Rotorua'], 'description' => ['lt' => 'Geoterminis kurortinis miestas', 'en' => 'Geothermal resort city'], 'is_capital' => false, 'latitude' => -38.1368, 'longitude' => 176.2497, 'population' => 56000],
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

        $this->command->info('New Zealand cities seeded successfully.');
    }
}
