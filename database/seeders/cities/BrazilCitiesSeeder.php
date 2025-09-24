<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class BrazilCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'BR')->first();
        if (! $country) {
            $this->command->warn('Brazil country not found. Please run CountrySeeder first.');

            return;
        }

        $cities = [
            ['code' => 'BR-SAO', 'slug' => 'sao-paulo', 'name' => ['lt' => 'San Paulas', 'en' => 'São Paulo'], 'description' => 'Largest city in Brazil', 'is_capital' => false, 'latitude' => -23.5505, 'longitude' => -46.6333, 'population' => 12396372],
            ['code' => 'BR-RIO', 'slug' => 'rio-de-janeiro', 'name' => ['lt' => 'Rio de Žaneiras', 'en' => 'Rio de Janeiro'], 'description' => 'Second largest city in Brazil', 'is_capital' => false, 'latitude' => -22.9068, 'longitude' => -43.1729, 'population' => 6747815],
            ['code' => 'BR-BRA', 'slug' => 'brasilia', 'name' => ['lt' => 'Brazilija', 'en' => 'Brasília'], 'description' => 'Capital of Brazil', 'is_capital' => true, 'latitude' => -15.7801, 'longitude' => -47.9292, 'population' => 3015268],
            ['code' => 'BR-SAL', 'slug' => 'salvador', 'name' => ['lt' => 'Salvadoras', 'en' => 'Salvador'], 'description' => 'Capital of Bahia', 'is_capital' => false, 'latitude' => -12.9777, 'longitude' => -38.5016, 'population' => 2886698],
            ['code' => 'BR-FOR', 'slug' => 'fortaleza', 'name' => ['lt' => 'Fortaleza', 'en' => 'Fortaleza'], 'description' => 'Capital of Ceará', 'is_capital' => false, 'latitude' => -3.7319, 'longitude' => -38.5267, 'population' => 2703391],
            ['code' => 'BR-BEL', 'slug' => 'belo-horizonte', 'name' => ['lt' => 'Belo Horizontė', 'en' => 'Belo Horizonte'], 'description' => 'Capital of Minas Gerais', 'is_capital' => false, 'latitude' => -19.9167, 'longitude' => -43.9345, 'population' => 2530701],
            ['code' => 'BR-MAN', 'slug' => 'manaus', 'name' => ['lt' => 'Manausas', 'en' => 'Manaus'], 'description' => 'Capital of Amazonas', 'is_capital' => false, 'latitude' => -3.1190, 'longitude' => -60.0217, 'population' => 2255903],
            ['code' => 'BR-CUR', 'slug' => 'curitiba', 'name' => ['lt' => 'Kuritiba', 'en' => 'Curitiba'], 'description' => 'Capital of Paraná', 'is_capital' => false, 'latitude' => -25.4244, 'longitude' => -49.2654, 'population' => 1963726],
            ['code' => 'BR-REC', 'slug' => 'recife', 'name' => ['lt' => 'Resifė', 'en' => 'Recife'], 'description' => 'Capital of Pernambuco', 'is_capital' => false, 'latitude' => -8.0476, 'longitude' => -34.8770, 'population' => 1653461],
            ['code' => 'BR-POR', 'slug' => 'porto-alegre', 'name' => ['lt' => 'Porto Alegrė', 'en' => 'Porto Alegre'], 'description' => 'Capital of Rio Grande do Sul', 'is_capital' => false, 'latitude' => -30.0346, 'longitude' => -51.2177, 'population' => 1492530],
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

        $this->command->info('Brazil cities seeded successfully.');
    }
}
