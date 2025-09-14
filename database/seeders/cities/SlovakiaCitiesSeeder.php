<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class SlovakiaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'SK')->first();
        if (!$country) {
            $this->command->warn('Slovakia country not found. Please run CountrySeeder first.');
            return;
        }

        $cities = [
            ['code' => 'SK-BRA', 'slug' => 'bratislava', 'name' => ['lt' => 'Bratislava', 'en' => 'Bratislava'], 'description' => ['lt' => 'Slovakijos sostinė', 'en' => 'Capital of Slovakia'], 'is_capital' => true, 'latitude' => 48.1486, 'longitude' => 17.1077, 'population' => 475503],
            ['code' => 'SK-KOS', 'slug' => 'kosice', 'name' => ['lt' => 'Košice', 'en' => 'Košice'], 'description' => ['lt' => 'Antras pagal dydį Slovakijos miestas', 'en' => 'Second largest city in Slovakia'], 'is_capital' => false, 'latitude' => 48.7164, 'longitude' => 21.2611, 'population' => 238593],
            ['code' => 'SK-PRE', 'slug' => 'presov', 'name' => ['lt' => 'Prešovas', 'en' => 'Prešov'], 'description' => ['lt' => 'Rytų Slovakijos miestas', 'en' => 'Eastern Slovak city'], 'is_capital' => false, 'latitude' => 49.0017, 'longitude' => 21.2394, 'population' => 88898],
            ['code' => 'SK-NIT', 'slug' => 'nitra', 'name' => ['lt' => 'Nitros', 'en' => 'Nitra'], 'description' => ['lt' => 'Vakarų Slovakijos miestas', 'en' => 'Western Slovak city'], 'is_capital' => false, 'latitude' => 48.3069, 'longitude' => 18.0845, 'population' => 78489],
            ['code' => 'SK-ZIL', 'slug' => 'zilina', 'name' => ['lt' => 'Žilina', 'en' => 'Žilina'], 'description' => ['lt' => 'Šiaurės Slovakijos miestas', 'en' => 'Northern Slovak city'], 'is_capital' => false, 'latitude' => 49.2231, 'longitude' => 18.7394, 'population' => 81515],
            ['code' => 'SK-BAN', 'slug' => 'banska-bystrica', 'name' => ['lt' => 'Banská Bystrica', 'en' => 'Banská Bystrica'], 'description' => ['lt' => 'Centrinės Slovakijos miestas', 'en' => 'Central Slovak city'], 'is_capital' => false, 'latitude' => 48.7353, 'longitude' => 19.1458, 'population' => 78455],
            ['code' => 'SK-TRE', 'slug' => 'trnava', 'name' => ['lt' => 'Trnava', 'en' => 'Trnava'], 'description' => ['lt' => 'Vakarų Slovakijos miestas', 'en' => 'Western Slovak city'], 'is_capital' => false, 'latitude' => 48.3764, 'longitude' => 17.5881, 'population' => 65000],
            ['code' => 'SK-MAR', 'slug' => 'martin', 'name' => ['lt' => 'Martinas', 'en' => 'Martin'], 'description' => ['lt' => 'Centrinės Slovakijos miestas', 'en' => 'Central Slovak city'], 'is_capital' => false, 'latitude' => 49.0667, 'longitude' => 18.9167, 'population' => 54000],
            ['code' => 'SK-TRE', 'slug' => 'trencin', 'name' => ['lt' => 'Trenčinas', 'en' => 'Trenčín'], 'description' => ['lt' => 'Vakarų Slovakijos miestas', 'en' => 'Western Slovak city'], 'is_capital' => false, 'latitude' => 48.8944, 'longitude' => 18.0406, 'population' => 55000],
            ['code' => 'SK-POP', 'slug' => 'poprad', 'name' => ['lt' => 'Popradas', 'en' => 'Poprad'], 'description' => ['lt' => 'Aukštutinės Tatrų miestas', 'en' => 'High Tatras city'], 'is_capital' => false, 'latitude' => 49.0614, 'longitude' => 20.2978, 'population' => 52000],
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

        $this->command->info('Slovakia cities seeded successfully.');
    }
}
