<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Illuminate\Database\Seeder;

final class HungaryCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('cca2', 'HU')->first();
        if (! $country) {
            $this->command->warn('Hungary country not found. Please run CountrySeeder first.');

            return;
        }

        $cities = [
            ['code' => 'HU-BUD', 'slug' => 'budapest', 'name' => ['lt' => 'Budapeštas', 'en' => 'Budapest'], 'description' => 'Capital of ', 'is_capital' => true, 'latitude' => 47.4979, 'longitude' => 19.0402, 'population' => 1752286],
            ['code' => 'HU-DEB', 'slug' => 'debrecen', 'name' => ['lt' => 'Debrecenas', 'en' => 'Debrecen'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 47.5316, 'longitude' => 21.6273, 'population' => 201432],
            ['code' => 'HU-SZE', 'slug' => 'szeged', 'name' => ['lt' => 'Segedas', 'en' => 'Szeged'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 46.2530, 'longitude' => 20.1414, 'population' => 160258],
            ['code' => 'HU-MIS', 'slug' => 'miskolc', 'name' => ['lt' => 'Miškolcas', 'en' => 'Miskolc'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 48.1034, 'longitude' => 20.7784, 'population' => 154521],
            ['code' => 'HU-PEC', 'slug' => 'pecs', 'name' => ['lt' => 'Pečas', 'en' => 'Pécs'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 46.0727, 'longitude' => 18.2328, 'population' => 142873],
            ['code' => 'HU-GYO', 'slug' => 'gyor', 'name' => ['lt' => 'Gioras', 'en' => 'Győr'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 47.6875, 'longitude' => 17.6504, 'population' => 132038],
            ['code' => 'HU-NYG', 'slug' => 'nyiregyhaza', 'name' => ['lt' => 'Niregihaza', 'en' => 'Nyíregyháza'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 47.9554, 'longitude' => 21.7169, 'population' => 116799],
            ['code' => 'HU-KEC', 'slug' => 'kecskemet', 'name' => ['lt' => 'Kečkemetas', 'en' => 'Kecskemét'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 46.9076, 'longitude' => 19.6917, 'population' => 109651],
            ['code' => 'HU-SZE', 'slug' => 'szekesfehervar', 'name' => ['lt' => 'Sekesfehervaras', 'en' => 'Székesfehérvár'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 47.1925, 'longitude' => 18.4106, 'population' => 96400],
            ['code' => 'HU-SZO', 'slug' => 'szombathely', 'name' => ['lt' => 'Sombathely', 'en' => 'Szombathely'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 47.2307, 'longitude' => 16.6219, 'population' => 78000],
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

        $this->command->info('Hungary cities seeded successfully.');
    }
}
