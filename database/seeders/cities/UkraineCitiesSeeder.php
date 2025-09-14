<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class UkraineCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'UA')->first();
        if (!$country) {
            $this->command->warn('Ukraine country not found. Please run CountrySeeder first.');
            return;
        }

        $cities = [
            ['code' => 'UA-KIE', 'slug' => 'kyiv', 'name' => ['lt' => 'Kijevas', 'en' => 'Kyiv'], 'description' => ['lt' => 'Ukrainos sostinė', 'en' => 'Capital of Ukraine'], 'is_capital' => true, 'latitude' => 50.4501, 'longitude' => 30.5234, 'population' => 2967360],
            ['code' => 'UA-KHA', 'slug' => 'kharkiv', 'name' => ['lt' => 'Charkivas', 'en' => 'Kharkiv'], 'description' => ['lt' => 'Antras pagal dydį Ukrainos miestas', 'en' => 'Second largest city in Ukraine'], 'is_capital' => false, 'latitude' => 49.9935, 'longitude' => 36.2304, 'population' => 1441057],
            ['code' => 'UA-ODS', 'slug' => 'odessa', 'name' => ['lt' => 'Odesa', 'en' => 'Odesa'], 'description' => ['lt' => 'Juodosios jūros uostamiestis', 'en' => 'Black Sea port city'], 'is_capital' => false, 'latitude' => 46.4825, 'longitude' => 30.7233, 'population' => 1015826],
            ['code' => 'UA-DON', 'slug' => 'dnipro', 'name' => ['lt' => 'Dniepropetrovskas', 'en' => 'Dnipro'], 'description' => ['lt' => 'Centrinės Ukrainos miestas', 'en' => 'Central Ukrainian city'], 'is_capital' => false, 'latitude' => 48.4647, 'longitude' => 35.0462, 'population' => 976525],
            ['code' => 'UA-DON', 'slug' => 'donetsk', 'name' => ['lt' => 'Doneckas', 'en' => 'Donetsk'], 'description' => ['lt' => 'Rytų Ukrainos miestas', 'en' => 'Eastern Ukrainian city'], 'is_capital' => false, 'latitude' => 48.0159, 'longitude' => 37.8028, 'population' => 905364],
            ['code' => 'UA-ZAP', 'slug' => 'zaporizhzhia', 'name' => ['lt' => 'Zaporožė', 'en' => 'Zaporizhzhia'], 'description' => ['lt' => 'Pietų Ukrainos miestas', 'en' => 'Southern Ukrainian city'], 'is_capital' => false, 'latitude' => 47.8388, 'longitude' => 35.1396, 'population' => 722713],
            ['code' => 'UA-LVI', 'slug' => 'lviv', 'name' => ['lt' => 'Lvovas', 'en' => 'Lviv'], 'description' => ['lt' => 'Vakarų Ukrainos miestas', 'en' => 'Western Ukrainian city'], 'is_capital' => false, 'latitude' => 49.8397, 'longitude' => 24.0297, 'population' => 717273],
            ['code' => 'UA-KRI', 'slug' => 'kryvyi-rih', 'name' => ['lt' => 'Kryvyj Rihas', 'en' => 'Kryvyi Rih'], 'description' => ['lt' => 'Centrinės Ukrainos miestas', 'en' => 'Central Ukrainian city'], 'is_capital' => false, 'latitude' => 47.9105, 'longitude' => 33.3918, 'population' => 612750],
            ['code' => 'UA-MYK', 'slug' => 'mykolaiv', 'name' => ['lt' => 'Mykolajivas', 'en' => 'Mykolaiv'], 'description' => ['lt' => 'Pietų Ukrainos miestas', 'en' => 'Southern Ukrainian city'], 'is_capital' => false, 'latitude' => 46.9750, 'longitude' => 31.9946, 'population' => 480080],
            ['code' => 'UA-MAR', 'slug' => 'mariupol', 'name' => ['lt' => 'Mariupolis', 'en' => 'Mariupol'], 'description' => ['lt' => 'Azovo jūros uostamiestis', 'en' => 'Sea of Azov port city'], 'is_capital' => false, 'latitude' => 47.0961, 'longitude' => 37.5562, 'population' => 431859],
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

        $this->command->info('Ukraine cities seeded successfully.');
    }
}
