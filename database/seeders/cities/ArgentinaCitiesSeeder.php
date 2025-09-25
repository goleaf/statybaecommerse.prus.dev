<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class ArgentinaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'AR')->first();
        if (! $country) {
            $this->command->warn('Argentina country not found. Please run CountrySeeder first.');

            return;
        }

        $cities = [
            ['code' => 'AR-BUE', 'slug' => 'buenos-aires', 'name' => ['lt' => 'Buenos Airės', 'en' => 'Buenos Aires'], 'description' => "Capital of \x01", 'is_capital' => true, 'latitude' => -34.6118, 'longitude' => -58.396, 'population' => 15594428],
            ['code' => 'AR-COR', 'slug' => 'cordoba', 'name' => ['lt' => 'Kordoba', 'en' => 'Córdoba'], 'description' => "Capital of \x01", 'is_capital' => false, 'latitude' => -31.4201, 'longitude' => -64.1888, 'population' => 1565112],
            ['code' => 'AR-ROS', 'slug' => 'rosario', 'name' => ['lt' => 'Rosarijas', 'en' => 'Rosario'], 'description' => "Capital of \x01", 'is_capital' => false, 'latitude' => -32.9442, 'longitude' => -60.6505, 'population' => 1344000],
            ['code' => 'AR-MEN', 'slug' => 'mendoza', 'name' => ['lt' => 'Mendosa', 'en' => 'Mendoza'], 'description' => "Capital of \x01", 'is_capital' => false, 'latitude' => -32.8908, 'longitude' => -68.8272, 'population' => 115041],
            ['code' => 'AR-TUC', 'slug' => 'san-miguel-de-tucuman', 'name' => ['lt' => 'San Migel de Tukumanas', 'en' => 'San Miguel de Tucumán'], 'description' => "Capital of \x01", 'is_capital' => false, 'latitude' => -26.8083, 'longitude' => -65.2176, 'population' => 548866],
            ['code' => 'AR-LAP', 'slug' => 'la-plata', 'name' => ['lt' => 'La Plata', 'en' => 'La Plata'], 'description' => "Capital of \x01", 'is_capital' => false, 'latitude' => -34.9214, 'longitude' => -57.9544, 'population' => 643133],
            ['code' => 'AR-MAR', 'slug' => 'mar-del-plata', 'name' => ['lt' => 'Mar del Plata', 'en' => 'Mar del Plata'], 'description' => "Capital of \x01", 'is_capital' => false, 'latitude' => -38.0023, 'longitude' => -57.5575, 'population' => 614350],
            ['code' => 'AR-SAL', 'slug' => 'salta', 'name' => ['lt' => 'Salta', 'en' => 'Salta'], 'description' => "Capital of \x01", 'is_capital' => false, 'latitude' => -24.7821, 'longitude' => -65.4232, 'population' => 535303],
            ['code' => 'AR-SAN', 'slug' => 'san-juan', 'name' => ['lt' => 'San Chuanas', 'en' => 'San Juan'], 'description' => "Capital of \x01", 'is_capital' => false, 'latitude' => -31.5375, 'longitude' => -68.5364, 'population' => 471389],
            ['code' => 'AR-RES', 'slug' => 'resistencia', 'name' => ['lt' => 'Resistensija', 'en' => 'Resistencia'], 'description' => "Capital of \x01", 'is_capital' => false, 'latitude' => -27.4514, 'longitude' => -58.9867, 'population' => 291720],
        ];

        foreach ($cities as $cityData) {
            // Check if city already exists to maintain idempotency
            $existingCity = City::where('code', $cityData['code'])->first();

            if ($existingCity) {
                // Update existing city
                $existingCity->update([
                    'name' => $cityData['name']['en'],
                    'slug' => $cityData['slug'],
                    'is_capital' => $cityData['is_capital'],
                    'latitude' => $cityData['latitude'],
                    'longitude' => $cityData['longitude'],
                    'population' => $cityData['population'],
                ]);
                $city = $existingCity;
            } else {
                // Create new city using factory
                $city = City::factory()
                    ->forCountry($country)
                    ->state([
                        'code' => $cityData['code'],
                        'slug' => $cityData['slug'],
                        'name' => $cityData['name']['en'],
                        'is_capital' => $cityData['is_capital'],
                        'latitude' => $cityData['latitude'],
                        'longitude' => $cityData['longitude'],
                        'population' => $cityData['population'],
                        'is_enabled' => true,
                        'is_default' => false,
                    ])
                    ->create();
            }

            // Update translations with specific content
            foreach (['lt', 'en'] as $locale) {
                CityTranslation::updateOrCreate([
                    'city_id' => $city->id,
                    'locale' => $locale,
                ], [
                    'name' => $cityData['name'][$locale] ?? $cityData['name']['en'],
                    'description' => $cityData['description'],
                ]);
            }
        }

        $this->command->info('Argentina cities seeded successfully.');
    }
}
