<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class SouthKoreaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'KR')->first();
        if (!$country) {
            $this->command->warn('South Korea country not found. Please run CountrySeeder first.');
            return;
        }

        $cities = [
            ['code' => 'KR-SEO', 'slug' => 'seoul', 'name' => ['lt' => 'Seulas', 'en' => 'Seoul'], 'description' => ['lt' => 'Pietų Korėjos sostinė', 'en' => 'Capital of South Korea'], 'is_capital' => true, 'latitude' => 37.5665, 'longitude' => 126.9780, 'population' => 9720846],
            ['code' => 'KR-BUS', 'slug' => 'busan', 'name' => ['lt' => 'Pusanas', 'en' => 'Busan'], 'description' => ['lt' => 'Antras pagal dydį Pietų Korėjos miestas', 'en' => 'Second largest city in South Korea'], 'is_capital' => false, 'latitude' => 35.1796, 'longitude' => 129.0756, 'population' => 3448737],
            ['code' => 'KR-INC', 'slug' => 'incheon', 'name' => ['lt' => 'Inčonas', 'en' => 'Incheon'], 'description' => ['lt' => 'Uostamiestis prie Seulo', 'en' => 'Port city near Seoul'], 'is_capital' => false, 'latitude' => 37.4563, 'longitude' => 126.7052, 'population' => 2954955],
            ['code' => 'KR-DAE', 'slug' => 'daegu', 'name' => ['lt' => 'Tegu', 'en' => 'Daegu'], 'description' => ['lt' => 'Centrinės Pietų Korėjos miestas', 'en' => 'Central South Korean city'], 'is_capital' => false, 'latitude' => 35.8714, 'longitude' => 128.6014, 'population' => 2413076],
            ['code' => 'KR-DAE', 'slug' => 'daejeon', 'name' => ['lt' => 'Tėdžonas', 'en' => 'Daejeon'], 'description' => ['lt' => 'Centrinės Pietų Korėjos miestas', 'en' => 'Central South Korean city'], 'is_capital' => false, 'latitude' => 36.3504, 'longitude' => 127.3845, 'population' => 1475221],
            ['code' => 'KR-GWA', 'slug' => 'gwangju', 'name' => ['lt' => 'Gvangdžu', 'en' => 'Gwangju'], 'description' => ['lt' => 'Pietvakarių Pietų Korėjos miestas', 'en' => 'Southwestern South Korean city'], 'is_capital' => false, 'latitude' => 35.1595, 'longitude' => 126.8526, 'population' => 1441970],
            ['code' => 'KR-ULS', 'slug' => 'ulsan', 'name' => ['lt' => 'Ulsanas', 'en' => 'Ulsan'], 'description' => ['lt' => 'Pramonės miestas', 'en' => 'Industrial city'], 'is_capital' => false, 'latitude' => 35.5384, 'longitude' => 129.3114, 'population' => 1166033],
            ['code' => 'KR-SUW', 'slug' => 'suwon', 'name' => ['lt' => 'Suvonas', 'en' => 'Suwon'], 'description' => ['lt' => 'Kiongi provincijos sostinė', 'en' => 'Capital of Gyeonggi province'], 'is_capital' => false, 'latitude' => 37.2636, 'longitude' => 127.0286, 'population' => 1234300],
            ['code' => 'KR-CHG', 'slug' => 'changwon', 'name' => ['lt' => 'Čangvonas', 'en' => 'Changwon'], 'description' => ['lt' => 'Pietų Korėjos miestas', 'en' => 'South Korean city'], 'is_capital' => false, 'latitude' => 35.2281, 'longitude' => 128.6811, 'population' => 1053551],
            ['code' => 'KR-GOY', 'slug' => 'goyang', 'name' => ['lt' => 'Gojangas', 'en' => 'Goyang'], 'description' => ['lt' => 'Kiongi provincijos miestas', 'en' => 'Gyeonggi province city'], 'is_capital' => false, 'latitude' => 37.6564, 'longitude' => 126.8350, 'population' => 1064091],
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

        $this->command->info('South Korea cities seeded successfully.');
    }
}
