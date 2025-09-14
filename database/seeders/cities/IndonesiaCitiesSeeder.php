<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class IndonesiaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'ID')->first();
        if (!$country) {
            $this->command->warn('Indonesia country not found. Please run CountrySeeder first.');
            return;
        }

        $cities = [
            ['code' => 'ID-JKT', 'slug' => 'jakarta', 'name' => ['lt' => 'Džakarta', 'en' => 'Jakarta'], 'description' => ['lt' => 'Indonezijos sostinė', 'en' => 'Capital of Indonesia'], 'is_capital' => true, 'latitude' => -6.2088, 'longitude' => 106.8456, 'population' => 10560000],
            ['code' => 'ID-SUR', 'slug' => 'surabaya', 'name' => ['lt' => 'Surabaja', 'en' => 'Surabaya'], 'description' => ['lt' => 'Antras pagal dydį Indonezijos miestas', 'en' => 'Second largest city in Indonesia'], 'is_capital' => false, 'latitude' => -7.2575, 'longitude' => 112.7521, 'population' => 2931000],
            ['code' => 'ID-BDG', 'slug' => 'bandung', 'name' => ['lt' => 'Bandungas', 'en' => 'Bandung'], 'description' => ['lt' => 'Vakarų Javos sostinė', 'en' => 'Capital of West Java'], 'is_capital' => false, 'latitude' => -6.9175, 'longitude' => 107.6191, 'population' => 2500000],
            ['code' => 'ID-MED', 'slug' => 'medan', 'name' => ['lt' => 'Medanas', 'en' => 'Medan'], 'description' => ['lt' => 'Šiaurės Sumatros sostinė', 'en' => 'Capital of North Sumatra'], 'is_capital' => false, 'latitude' => 3.5952, 'longitude' => 98.6722, 'population' => 2465000],
            ['code' => 'ID-SEM', 'slug' => 'semarang', 'name' => ['lt' => 'Semarangas', 'en' => 'Semarang'], 'description' => ['lt' => 'Centrinės Javos sostinė', 'en' => 'Capital of Central Java'], 'is_capital' => false, 'latitude' => -6.9667, 'longitude' => 110.4167, 'population' => 1621384],
            ['code' => 'ID-MAK', 'slug' => 'makassar', 'name' => ['lt' => 'Makasaras', 'en' => 'Makassar'], 'description' => ['lt' => 'Pietų Sulavesio sostinė', 'en' => 'Capital of South Sulawesi'], 'is_capital' => false, 'latitude' => -5.1477, 'longitude' => 119.4327, 'population' => 1440000],
            ['code' => 'ID-PAL', 'slug' => 'palembang', 'name' => ['lt' => 'Palembangas', 'en' => 'Palembang'], 'description' => ['lt' => 'Pietų Sumatros sostinė', 'en' => 'Capital of South Sumatra'], 'is_capital' => false, 'latitude' => -2.9761, 'longitude' => 104.7754, 'population' => 1650000],
            ['code' => 'ID-TAN', 'slug' => 'tangerang', 'name' => ['lt' => 'Tangerangas', 'en' => 'Tangerang'], 'description' => ['lt' => 'Banten provincijos miestas', 'en' => 'Banten province city'], 'is_capital' => false, 'latitude' => -6.1781, 'longitude' => 106.6300, 'population' => 1798601],
            ['code' => 'ID-DEP', 'slug' => 'depok', 'name' => ['lt' => 'Depokas', 'en' => 'Depok'], 'description' => ['lt' => 'Vakarų Javos miestas', 'en' => 'West Java city'], 'is_capital' => false, 'latitude' => -6.4000, 'longitude' => 106.8167, 'population' => 1738570],
            ['code' => 'ID-BEK', 'slug' => 'bekasi', 'name' => ['lt' => 'Bekasis', 'en' => 'Bekasi'], 'description' => ['lt' => 'Vakarų Javos miestas', 'en' => 'West Java city'], 'is_capital' => false, 'latitude' => -6.2333, 'longitude' => 106.9833, 'population' => 2381053],
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

        $this->command->info('Indonesia cities seeded successfully.');
    }
}
