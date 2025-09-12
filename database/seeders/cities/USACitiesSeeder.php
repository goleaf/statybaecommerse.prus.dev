<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\Translations\CityTranslation;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class USACitiesSeeder extends Seeder
{
    public function run(): void
    {
        $usa = Country::where('cca2', 'US')->first();
        $naZone = Zone::where('code', 'NA')->first();

        // Get regions (states)
        $californiaRegion = Region::where('code', 'US-CA')->first();
        $texasRegion = Region::where('code', 'US-TX')->first();
        $floridaRegion = Region::where('code', 'US-FL')->first();
        $newYorkRegion = Region::where('code', 'US-NY')->first();
        $illinoisRegion = Region::where('code', 'US-IL')->first();
        $pennsylvaniaRegion = Region::where('code', 'US-PA')->first();
        $ohioRegion = Region::where('code', 'US-OH')->first();
        $georgiaRegion = Region::where('code', 'US-GA')->first();
        $northCarolinaRegion = Region::where('code', 'US-NC')->first();
        $michiganRegion = Region::where('code', 'US-MI')->first();

        $cities = [
            // California
            [
                'name' => 'Los Angeles',
                'code' => 'US-CA-LAX',
                'region_id' => $californiaRegion?->id,
                'latitude' => 34.0522,
                'longitude' => -118.2437,
                'population' => 3971883,
                'postal_codes' => ['90001', '90002', '90003'],
                'translations' => [
                    'lt' => ['name' => 'Los Andželas', 'description' => 'Kino pramonės centras'],
                    'en' => ['name' => 'Los Angeles', 'description' => 'Entertainment industry center'],
                ],
            ],
            [
                'name' => 'San Diego',
                'code' => 'US-CA-SAN',
                'region_id' => $californiaRegion?->id,
                'latitude' => 32.7157,
                'longitude' => -117.1611,
                'population' => 1423851,
                'postal_codes' => ['92101', '92102', '92103'],
                'translations' => [
                    'lt' => ['name' => 'San Diegas', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'San Diego', 'description' => 'Seaside city'],
                ],
            ],
            [
                'name' => 'San Jose',
                'code' => 'US-CA-SJO',
                'region_id' => $californiaRegion?->id,
                'latitude' => 37.3382,
                'longitude' => -121.8863,
                'population' => 1035317,
                'postal_codes' => ['95110', '95111', '95112'],
                'translations' => [
                    'lt' => ['name' => 'San Chosė', 'description' => 'Technologijų centras'],
                    'en' => ['name' => 'San Jose', 'description' => 'Technology center'],
                ],
            ],
            [
                'name' => 'San Francisco',
                'code' => 'US-CA-SFO',
                'region_id' => $californiaRegion?->id,
                'latitude' => 37.7749,
                'longitude' => -122.4194,
                'population' => 873965,
                'postal_codes' => ['94102', '94103', '94104'],
                'translations' => [
                    'lt' => ['name' => 'San Franciskas', 'description' => 'Finansų centras'],
                    'en' => ['name' => 'San Francisco', 'description' => 'Financial center'],
                ],
            ],
            [
                'name' => 'Fresno',
                'code' => 'US-CA-FRE',
                'region_id' => $californiaRegion?->id,
                'latitude' => 36.7378,
                'longitude' => -119.7871,
                'population' => 542107,
                'postal_codes' => ['93701', '93702', '93703'],
                'translations' => [
                    'lt' => ['name' => 'Fresnas', 'description' => 'Žemės ūkio centras'],
                    'en' => ['name' => 'Fresno', 'description' => 'Agricultural center'],
                ],
            ],
            // Texas
            [
                'name' => 'Houston',
                'code' => 'US-TX-HOU',
                'region_id' => $texasRegion?->id,
                'latitude' => 29.7604,
                'longitude' => -95.3698,
                'population' => 2320268,
                'postal_codes' => ['77001', '77002', '77003'],
                'translations' => [
                    'lt' => ['name' => 'Hjustonas', 'description' => 'Naftos pramonės centras'],
                    'en' => ['name' => 'Houston', 'description' => 'Oil industry center'],
                ],
            ],
            [
                'name' => 'San Antonio',
                'code' => 'US-TX-SAT',
                'region_id' => $texasRegion?->id,
                'latitude' => 29.4241,
                'longitude' => -98.4936,
                'population' => 1547253,
                'postal_codes' => ['78201', '78202', '78203'],
                'translations' => [
                    'lt' => ['name' => 'San Antonijas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'San Antonio', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Dallas',
                'code' => 'US-TX-DAL',
                'region_id' => $texasRegion?->id,
                'latitude' => 32.7767,
                'longitude' => -96.797,
                'population' => 1343573,
                'postal_codes' => ['75201', '75202', '75203'],
                'translations' => [
                    'lt' => ['name' => 'Dalasas', 'description' => 'Finansų centras'],
                    'en' => ['name' => 'Dallas', 'description' => 'Financial center'],
                ],
            ],
            [
                'name' => 'Austin',
                'code' => 'US-TX-AUS',
                'region_id' => $texasRegion?->id,
                'latitude' => 30.2672,
                'longitude' => -97.7431,
                'population' => 978908,
                'postal_codes' => ['73301', '73302', '73303'],
                'translations' => [
                    'lt' => ['name' => 'Ostinas', 'description' => 'Teksto sostinė'],
                    'en' => ['name' => 'Austin', 'description' => 'Music capital'],
                ],
            ],
            // Florida
            [
                'name' => 'Jacksonville',
                'code' => 'US-FL-JAX',
                'region_id' => $floridaRegion?->id,
                'latitude' => 30.3322,
                'longitude' => -81.6557,
                'population' => 949611,
                'postal_codes' => ['32099', '32201', '32202'],
                'translations' => [
                    'lt' => ['name' => 'Džeksonvilis', 'description' => 'Didžiausias Floridos miestas'],
                    'en' => ['name' => 'Jacksonville', 'description' => 'Largest city in Florida'],
                ],
            ],
            [
                'name' => 'Miami',
                'code' => 'US-FL-MIA',
                'region_id' => $floridaRegion?->id,
                'latitude' => 25.7617,
                'longitude' => -80.1918,
                'population' => 467963,
                'postal_codes' => ['33101', '33102', '33103'],
                'translations' => [
                    'lt' => ['name' => 'Majamis', 'description' => 'Kurortinis miestas'],
                    'en' => ['name' => 'Miami', 'description' => 'Resort city'],
                ],
            ],
            [
                'name' => 'Tampa',
                'code' => 'US-FL-TAM',
                'region_id' => $floridaRegion?->id,
                'latitude' => 27.9506,
                'longitude' => -82.4572,
                'population' => 384959,
                'postal_codes' => ['33601', '33602', '33603'],
                'translations' => [
                    'lt' => ['name' => 'Tampa', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Tampa', 'description' => 'Port city'],
                ],
            ],
            // New York
            [
                'name' => 'New York City',
                'code' => 'US-NY-NYC',
                'is_capital' => true,
                'is_default' => true,
                'region_id' => $newYorkRegion?->id,
                'latitude' => 40.7128,
                'longitude' => -74.006,
                'population' => 8336817,
                'postal_codes' => ['10001', '10002', '10003'],
                'translations' => [
                    'lt' => ['name' => 'Niujorkas', 'description' => 'Didžiausias JAV miestas'],
                    'en' => ['name' => 'New York City', 'description' => 'Largest city in USA'],
                ],
            ],
            [
                'name' => 'Buffalo',
                'code' => 'US-NY-BUF',
                'region_id' => $newYorkRegion?->id,
                'latitude' => 42.8864,
                'longitude' => -78.8784,
                'population' => 278349,
                'postal_codes' => ['14201', '14202', '14203'],
                'translations' => [
                    'lt' => ['name' => 'Bufalas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Buffalo', 'description' => 'Industrial city'],
                ],
            ],
            // Illinois
            [
                'name' => 'Chicago',
                'code' => 'US-IL-CHI',
                'region_id' => $illinoisRegion?->id,
                'latitude' => 41.8781,
                'longitude' => -87.6298,
                'population' => 2746388,
                'postal_codes' => ['60601', '60602', '60603'],
                'translations' => [
                    'lt' => ['name' => 'Čikaga', 'description' => 'Vėjo miestas'],
                    'en' => ['name' => 'Chicago', 'description' => 'Windy City'],
                ],
            ],
            // Pennsylvania
            [
                'name' => 'Philadelphia',
                'code' => 'US-PA-PHI',
                'region_id' => $pennsylvaniaRegion?->id,
                'latitude' => 39.9526,
                'longitude' => -75.1652,
                'population' => 1584064,
                'postal_codes' => ['19101', '19102', '19103'],
                'translations' => [
                    'lt' => ['name' => 'Filadelfija', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Philadelphia', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Pittsburgh',
                'code' => 'US-PA-PIT',
                'region_id' => $pennsylvaniaRegion?->id,
                'latitude' => 40.4406,
                'longitude' => -79.9959,
                'population' => 302971,
                'postal_codes' => ['15201', '15202', '15203'],
                'translations' => [
                    'lt' => ['name' => 'Pitsburgas', 'description' => 'Plieno miestas'],
                    'en' => ['name' => 'Pittsburgh', 'description' => 'Steel city'],
                ],
            ],
            // Ohio
            [
                'name' => 'Columbus',
                'code' => 'US-OH-COL',
                'region_id' => $ohioRegion?->id,
                'latitude' => 39.9612,
                'longitude' => -82.9988,
                'population' => 905748,
                'postal_codes' => ['43201', '43202', '43203'],
                'translations' => [
                    'lt' => ['name' => 'Kolumbas', 'description' => 'Ohajo sostinė'],
                    'en' => ['name' => 'Columbus', 'description' => 'Capital of Ohio'],
                ],
            ],
            [
                'name' => 'Cleveland',
                'code' => 'US-OH-CLE',
                'region_id' => $ohioRegion?->id,
                'latitude' => 41.4993,
                'longitude' => -81.6944,
                'population' => 383793,
                'postal_codes' => ['44101', '44102', '44103'],
                'translations' => [
                    'lt' => ['name' => 'Klivlandas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Cleveland', 'description' => 'Industrial city'],
                ],
            ],
            // Georgia
            [
                'name' => 'Atlanta',
                'code' => 'US-GA-ATL',
                'region_id' => $georgiaRegion?->id,
                'latitude' => 33.749,
                'longitude' => -84.388,
                'population' => 498715,
                'postal_codes' => ['30301', '30302', '30303'],
                'translations' => [
                    'lt' => ['name' => 'Atlanta', 'description' => 'Džordžijos sostinė'],
                    'en' => ['name' => 'Atlanta', 'description' => 'Capital of Georgia'],
                ],
            ],
            // North Carolina
            [
                'name' => 'Charlotte',
                'code' => 'US-NC-CHA',
                'region_id' => $northCarolinaRegion?->id,
                'latitude' => 35.2271,
                'longitude' => -80.8431,
                'population' => 885708,
                'postal_codes' => ['28201', '28202', '28203'],
                'translations' => [
                    'lt' => ['name' => 'Šarlotė', 'description' => 'Finansų centras'],
                    'en' => ['name' => 'Charlotte', 'description' => 'Financial center'],
                ],
            ],
            // Michigan
            [
                'name' => 'Detroit',
                'code' => 'US-MI-DET',
                'region_id' => $michiganRegion?->id,
                'latitude' => 42.3314,
                'longitude' => -83.0458,
                'population' => 639111,
                'postal_codes' => ['48201', '48202', '48203'],
                'translations' => [
                    'lt' => ['name' => 'Detroitas', 'description' => 'Automobilių pramonės centras'],
                    'en' => ['name' => 'Detroit', 'description' => 'Automotive industry center'],
                ],
            ],
        ];

        foreach ($cities as $cityData) {
            $city = City::updateOrCreate(
                ['code' => $cityData['code']],
                [
                    'name' => $cityData['name'],
                    'slug' => \Str::slug($cityData['name']),
                    'is_enabled' => true,
                    'is_default' => $cityData['is_default'] ?? false,
                    'is_capital' => $cityData['is_capital'] ?? false,
                    'country_id' => $usa->id,
                    'zone_id' => $naZone?->id,
                    'region_id' => $cityData['region_id'],
                    'level' => 1,
                    'latitude' => $cityData['latitude'],
                    'longitude' => $cityData['longitude'],
                    'population' => $cityData['population'],
                    'postal_codes' => $cityData['postal_codes'],
                    'sort_order' => 0,
                ]
            );

            // Create translations
            foreach ($cityData['translations'] as $locale => $translation) {
                CityTranslation::updateOrCreate(
                    [
                        'city_id' => $city->id,
                        'locale' => $locale,
                    ],
                    [
                        'name' => $translation['name'],
                        'description' => $translation['description'],
                    ]
                );
            }
        }
    }
}
