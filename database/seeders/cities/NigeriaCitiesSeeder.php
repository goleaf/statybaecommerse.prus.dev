<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class NigeriaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'NG')->first();
        if (!$country) {
            $this->command->warn('Nigeria country not found. Please run CountrySeeder first.');
            return;
        }

        $cities = [
            ['code' => 'NG-LAG', 'slug' => 'lagos', 'name' => ['lt' => 'Lagosas', 'en' => 'Lagos'], 'description' => 'Largest city in Nigeria', 'is_capital' => false, 'latitude' => 6.5244, 'longitude' => 3.3792, 'population' => 15388000],
            ['code' => 'NG-ABU', 'slug' => 'abuja', 'name' => ['lt' => 'Abudža', 'en' => 'Abuja'], 'description' => 'Capital of Nigeria', 'is_capital' => true, 'latitude' => 9.0765, 'longitude' => 7.3986, 'population' => 356412],
            ['code' => 'NG-KAN', 'slug' => 'kano', 'name' => ['lt' => 'Kanas', 'en' => 'Kano'], 'description' => 'Second largest city in Nigeria', 'is_capital' => false, 'latitude' => 12.0022, 'longitude' => 8.5920, 'population' => 2828861],
            ['code' => 'NG-IBD', 'slug' => 'ibadan', 'name' => ['lt' => 'Ibadanas', 'en' => 'Ibadan'], 'description' => 'Third largest city in Nigeria', 'is_capital' => false, 'latitude' => 7.3964, 'longitude' => 3.9167, 'population' => 3160000],
            ['code' => 'NG-BEN', 'slug' => 'benin-city', 'name' => ['lt' => 'Benino miestas', 'en' => 'Benin City'], 'description' => 'Edo state capital', 'is_capital' => false, 'latitude' => 6.3350, 'longitude' => 5.6037, 'population' => 1495000],
            ['code' => 'NG-POR', 'slug' => 'port-harcourt', 'name' => ['lt' => 'Port Harcourt', 'en' => 'Port Harcourt'], 'description' => 'Rivers state capital', 'is_capital' => false, 'latitude' => 4.8156, 'longitude' => 7.0498, 'population' => 1865000],
            ['code' => 'NG-JOS', 'slug' => 'jos', 'name' => ['lt' => 'Džosas', 'en' => 'Jos'], 'description' => 'Plateau state capital', 'is_capital' => false, 'latitude' => 9.9167, 'longitude' => 8.9000, 'population' => 873000],
            ['code' => 'NG-ILO', 'slug' => 'ilorin', 'name' => ['lt' => 'Ilorinas', 'en' => 'Ilorin'], 'description' => 'Kwara state capital', 'is_capital' => false, 'latitude' => 8.5000, 'longitude' => 4.5500, 'population' => 814000],
            ['code' => 'NG-ABE', 'slug' => 'abeokuta', 'name' => ['lt' => 'Abeokuta', 'en' => 'Abeokuta'], 'description' => 'Ogun state capital', 'is_capital' => false, 'latitude' => 7.1500, 'longitude' => 3.3500, 'population' => 593100],
            ['code' => 'NG-ONI', 'slug' => 'onitsha', 'name' => ['lt' => 'Oniča', 'en' => 'Onitsha'], 'description' => 'Anambra state city', 'is_capital' => false, 'latitude' => 6.1667, 'longitude' => 6.7833, 'population' => 561000],
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

        $this->command->info('Nigeria cities seeded successfully.');
    }
}
