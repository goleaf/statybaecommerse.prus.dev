<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class MexicoCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'MX')->first();
        if (! $country) {
            $this->command->warn('Mexico country not found. Please run CountrySeeder first.');

            return;
        }

        $cities = [
            ['code' => 'MX-MEX', 'slug' => 'mexico-city', 'name' => ['lt' => 'Meksiko miestas', 'en' => 'Mexico City'], 'description' => 'Capital of ', 'is_capital' => true, 'latitude' => 19.4326, 'longitude' => -99.1332, 'population' => 9209944],
            ['code' => 'MX-GUA', 'slug' => 'guadalajara', 'name' => ['lt' => 'Gvadalachara', 'en' => 'Guadalajara'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 20.6597, 'longitude' => -103.3496, 'population' => 1500800],
            ['code' => 'MX-MON', 'slug' => 'monterrey', 'name' => ['lt' => 'Monterėjus', 'en' => 'Monterrey'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 25.6866, 'longitude' => -100.3161, 'population' => 1135512],
            ['code' => 'MX-PUE', 'slug' => 'puebla', 'name' => ['lt' => 'Puebla', 'en' => 'Puebla'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 19.0414, 'longitude' => -98.2063, 'population' => 1543062],
            ['code' => 'MX-TIJ', 'slug' => 'tijuana', 'name' => ['lt' => 'Tichuana', 'en' => 'Tijuana'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 32.5149, 'longitude' => -117.0382, 'population' => 1810645],
            ['code' => 'MX-LEO', 'slug' => 'leon', 'name' => ['lt' => 'Leonas', 'en' => 'León'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 21.1250, 'longitude' => -101.6860, 'population' => 1579803],
            ['code' => 'MX-JUA', 'slug' => 'juarez', 'name' => ['lt' => 'Chuarez', 'en' => 'Juárez'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 31.6904, 'longitude' => -106.4244, 'population' => 1501551],
            ['code' => 'MX-TOR', 'slug' => 'torreon', 'name' => ['lt' => 'Torėjonas', 'en' => 'Torreón'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 25.5439, 'longitude' => -103.4190, 'population' => 690193],
            ['code' => 'MX-QUE', 'slug' => 'queretaro', 'name' => ['lt' => 'Keretaras', 'en' => 'Querétaro'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 20.5881, 'longitude' => -100.3881, 'population' => 1035364],
            ['code' => 'MX-SAN', 'slug' => 'san-luis-potosi', 'name' => ['lt' => 'San Luis Potosi', 'en' => 'San Luis Potosí'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 22.1565, 'longitude' => -100.9855, 'population' => 1080090],
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

        $this->command->info('Mexico cities seeded successfully.');
    }
}
