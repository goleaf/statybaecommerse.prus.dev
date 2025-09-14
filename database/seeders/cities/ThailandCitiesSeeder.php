<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class ThailandCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'TH')->first();
        if (!$country) {
            $this->command->warn('Thailand country not found. Please run CountrySeeder first.');
            return;
        }

        $cities = [
            ['code' => 'TH-BKK', 'slug' => 'bangkok', 'name' => ['lt' => 'Bankokas', 'en' => 'Bangkok'], 'description' => ['lt' => 'Tailando sostinė', 'en' => 'Capital of Thailand'], 'is_capital' => true, 'latitude' => 13.7563, 'longitude' => 100.5018, 'population' => 10539000],
            ['code' => 'TH-NON', 'slug' => 'nonthaburi', 'name' => ['lt' => 'Nonthaburis', 'en' => 'Nonthaburi'], 'description' => ['lt' => 'Antras pagal dydį Tailando miestas', 'en' => 'Second largest city in Thailand'], 'is_capital' => false, 'latitude' => 13.8661, 'longitude' => 100.5169, 'population' => 270609],
            ['code' => 'TH-CHI', 'slug' => 'chiang-mai', 'name' => ['lt' => 'Čiang Majus', 'en' => 'Chiang Mai'], 'description' => ['lt' => 'Šiaurės Tailando miestas', 'en' => 'Northern Thai city'], 'is_capital' => false, 'latitude' => 18.7883, 'longitude' => 98.9853, 'population' => 127240],
            ['code' => 'TH-HAT', 'slug' => 'hat-yai', 'name' => ['lt' => 'Hat Jajus', 'en' => 'Hat Yai'], 'description' => ['lt' => 'Pietų Tailando miestas', 'en' => 'Southern Thai city'], 'is_capital' => false, 'latitude' => 7.0084, 'longitude' => 100.4767, 'population' => 159627],
            ['code' => 'TH-KHO', 'slug' => 'khon-kaen', 'name' => ['lt' => 'Khon Kaenas', 'en' => 'Khon Kaen'], 'description' => ['lt' => 'Šiaurės rytų Tailando miestas', 'en' => 'Northeastern Thai city'], 'is_capital' => false, 'latitude' => 16.4419, 'longitude' => 102.8358, 'population' => 115928],
            ['code' => 'TH-UDO', 'slug' => 'udon-thani', 'name' => ['lt' => 'Udon Tanis', 'en' => 'Udon Thani'], 'description' => ['lt' => 'Šiaurės rytų Tailando miestas', 'en' => 'Northeastern Thai city'], 'is_capital' => false, 'latitude' => 17.4138, 'longitude' => 102.7873, 'population' => 247231],
            ['code' => 'TH-PAT', 'slug' => 'pattaya', 'name' => ['lt' => 'Pataja', 'en' => 'Pattaya'], 'description' => ['lt' => 'Kurortinis miestas', 'en' => 'Resort city'], 'is_capital' => false, 'latitude' => 12.9236, 'longitude' => 100.8825, 'population' => 119532],
            ['code' => 'TH-NAK', 'slug' => 'nakhon-ratchasima', 'name' => ['lt' => 'Nakhon Račasima', 'en' => 'Nakhon Ratchasima'], 'description' => ['lt' => 'Šiaurės rytų Tailando miestas', 'en' => 'Northeastern Thai city'], 'is_capital' => false, 'latitude' => 14.9799, 'longitude' => 102.0978, 'population' => 174332],
            ['code' => 'TH-PHI', 'slug' => 'phitsanulok', 'name' => ['lt' => 'Pitsanulokas', 'en' => 'Phitsanulok'], 'description' => ['lt' => 'Centrinio Tailando miestas', 'en' => 'Central Thai city'], 'is_capital' => false, 'latitude' => 16.8211, 'longitude' => 100.2659, 'population' => 103427],
            ['code' => 'TH-SUR', 'slug' => 'surat-thani', 'name' => ['lt' => 'Surat Tanis', 'en' => 'Surat Thani'], 'description' => ['lt' => 'Pietų Tailando miestas', 'en' => 'Southern Thai city'], 'is_capital' => false, 'latitude' => 9.1382, 'longitude' => 99.3215, 'population' => 128179],
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

        $this->command->info('Thailand cities seeded successfully.');
    }
}
