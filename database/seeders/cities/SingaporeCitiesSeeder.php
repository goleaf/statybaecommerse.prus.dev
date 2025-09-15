<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class SingaporeCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'SG')->first();
        if (!$country) {
            $this->command->warn('Singapore country not found. Please run CountrySeeder first.');
            return;
        }

        $cities = [
            ['code' => 'SG-SIN', 'slug' => 'singapore', 'name' => ['lt' => 'Singapūras', 'en' => 'Singapore'], 'description' => 'Capital and largest city of Singapore', 'is_capital' => true, 'latitude' => 1.3521, 'longitude' => 103.8198, 'population' => 5453600],
            ['code' => 'SG-JUR', 'slug' => 'jurong-east', 'name' => ['lt' => 'Džurong Rytai', 'en' => 'Jurong East'], 'description' => 'Western Singapore district', 'is_capital' => false, 'latitude' => 1.3329, 'longitude' => 103.7436, 'population' => 250000],
            ['code' => 'SG-TAM', 'slug' => 'tampines', 'name' => ['lt' => 'Tampinesas', 'en' => 'Tampines'], 'description' => 'Eastern Singapore district', 'is_capital' => false, 'latitude' => 1.3496, 'longitude' => 103.9568, 'population' => 256000],
            ['code' => 'SG-BED', 'slug' => 'bedok', 'name' => ['lt' => 'Bedokas', 'en' => 'Bedok'], 'description' => 'Eastern Singapore district', 'is_capital' => false, 'latitude' => 1.3240, 'longitude' => 103.9302, 'population' => 200000],
            ['code' => 'SG-SER', 'slug' => 'sengkang', 'name' => ['lt' => 'Sengangas', 'en' => 'Sengkang'], 'description' => 'North-eastern Singapore district', 'is_capital' => false, 'latitude' => 1.3880, 'longitude' => 103.8954, 'population' => 240000],
            ['code' => 'SG-PUN', 'slug' => 'punggol', 'name' => ['lt' => 'Pungolas', 'en' => 'Punggol'], 'description' => 'North-eastern Singapore district', 'is_capital' => false, 'latitude' => 1.3984, 'longitude' => 103.9078, 'population' => 170000],
            ['code' => 'SG-HOU', 'slug' => 'hougang', 'name' => ['lt' => 'Hougangas', 'en' => 'Hougang'], 'description' => 'North-eastern Singapore district', 'is_capital' => false, 'latitude' => 1.3721, 'longitude' => 103.8974, 'population' => 220000],
            ['code' => 'SG-ANG', 'slug' => 'ang-mo-kio', 'name' => ['lt' => 'Ang Mo Kio', 'en' => 'Ang Mo Kio'], 'description' => 'North-central Singapore district', 'is_capital' => false, 'latitude' => 1.3691, 'longitude' => 103.8454, 'population' => 160000],
            ['code' => 'SG-BIS', 'slug' => 'bishan', 'name' => ['lt' => 'Bišanas', 'en' => 'Bishan'], 'description' => 'Central Singapore district', 'is_capital' => false, 'latitude' => 1.3521, 'longitude' => 103.8198, 'population' => 90000],
            ['code' => 'SG-CLE', 'slug' => 'clementi', 'name' => ['lt' => 'Klementis', 'en' => 'Clementi'], 'description' => 'Western Singapore district', 'is_capital' => false, 'latitude' => 1.3158, 'longitude' => 103.7649, 'population' => 90000],
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

        $this->command->info('Singapore cities seeded successfully.');
    }
}
