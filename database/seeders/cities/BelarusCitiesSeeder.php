<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class BelarusCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'BY')->first();
        if (!$country) {
            $this->command->warn('Belarus country not found. Please run CountrySeeder first.');
            return;
        }

        $cities = [
            ['code' => 'BY-MIN', 'slug' => 'minsk', 'name' => ['lt' => 'Minskas', 'en' => 'Minsk'], 'description' => ['lt' => 'Baltarusijos sostinė', 'en' => 'Capital of Belarus'], 'is_capital' => true, 'latitude' => 53.9006, 'longitude' => 27.5590, 'population' => 2009786],
            ['code' => 'BY-HOM', 'slug' => 'gomel', 'name' => ['lt' => 'Gomelis', 'en' => 'Gomel'], 'description' => ['lt' => 'Antras pagal dydį Baltarusijos miestas', 'en' => 'Second largest city in Belarus'], 'is_capital' => false, 'latitude' => 52.4412, 'longitude' => 30.9878, 'population' => 510000],
            ['code' => 'BY-MOG', 'slug' => 'mogilev', 'name' => ['lt' => 'Mogiliovas', 'en' => 'Mogilev'], 'description' => ['lt' => 'Rytų Baltarusijos miestas', 'en' => 'Eastern Belarusian city'], 'is_capital' => false, 'latitude' => 53.9006, 'longitude' => 30.3309, 'population' => 357100],
            ['code' => 'BY-VIT', 'slug' => 'vitebsk', 'name' => ['lt' => 'Vitebskas', 'en' => 'Vitebsk'], 'description' => ['lt' => 'Šiaurės Baltarusijos miestas', 'en' => 'Northern Belarusian city'], 'is_capital' => false, 'latitude' => 55.1904, 'longitude' => 30.2049, 'population' => 366299],
            ['code' => 'BY-GRO', 'slug' => 'grodno', 'name' => ['lt' => 'Gardinas', 'en' => 'Grodno'], 'description' => ['lt' => 'Vakarų Baltarusijos miestas', 'en' => 'Western Belarusian city'], 'is_capital' => false, 'latitude' => 53.6694, 'longitude' => 23.8131, 'population' => 356557],
            ['code' => 'BY-BRE', 'slug' => 'brest', 'name' => ['lt' => 'Brestas', 'en' => 'Brest'], 'description' => ['lt' => 'Pietvakarių Baltarusijos miestas', 'en' => 'Southwestern Belarusian city'], 'is_capital' => false, 'latitude' => 52.0975, 'longitude' => 23.7341, 'population' => 340318],
            ['code' => 'BY-BAB', 'slug' => 'bobruisk', 'name' => ['lt' => 'Babruiskas', 'en' => 'Bobruisk'], 'description' => ['lt' => 'Centrinės Baltarusijos miestas', 'en' => 'Central Belarusian city'], 'is_capital' => false, 'latitude' => 53.1500, 'longitude' => 29.2333, 'population' => 217000],
            ['code' => 'BY-BAR', 'slug' => 'baranovichi', 'name' => ['lt' => 'Baranovičiai', 'en' => 'Baranovichi'], 'description' => ['lt' => 'Centrinės Baltarusijos miestas', 'en' => 'Central Belarusian city'], 'is_capital' => false, 'latitude' => 53.1333, 'longitude' => 26.0167, 'population' => 179000],
            ['code' => 'BY-BOR', 'slug' => 'borisov', 'name' => ['lt' => 'Borisovas', 'en' => 'Borisov'], 'description' => ['lt' => 'Centrinės Baltarusijos miestas', 'en' => 'Central Belarusian city'], 'is_capital' => false, 'latitude' => 54.2333, 'longitude' => 28.5000, 'population' => 143000],
            ['code' => 'BY-PIN', 'slug' => 'pinsk', 'name' => ['lt' => 'Pinskas', 'en' => 'Pinsk'], 'description' => ['lt' => 'Pietvakarių Baltarusijos miestas', 'en' => 'Southwestern Belarusian city'], 'is_capital' => false, 'latitude' => 52.1167, 'longitude' => 26.1000, 'population' => 125000],
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

        $this->command->info('Belarus cities seeded successfully.');
    }
}
