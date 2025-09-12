<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Translations\CityTranslation;
use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class CanadaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $canada = Country::where('cca2', 'CA')->first();
        $naZone = Zone::where('code', 'NA')->first();

        // Get regions (provinces)
        $ontarioRegion = Region::where('code', 'CA-ON')->first();
        $quebecRegion = Region::where('code', 'CA-QC')->first();
        $britishColumbiaRegion = Region::where('code', 'CA-BC')->first();
        $albertaRegion = Region::where('code', 'CA-AB')->first();
        $manitobaRegion = Region::where('code', 'CA-MB')->first();
        $saskatchewanRegion = Region::where('code', 'CA-SK')->first();
        $novaScotiaRegion = Region::where('code', 'CA-NS')->first();
        $newBrunswickRegion = Region::where('code', 'CA-NB')->first();
        $newfoundlandRegion = Region::where('code', 'CA-NL')->first();
        $peiRegion = Region::where('code', 'CA-PE')->first();

        $cities = [
            // Ontario
            [
                'name' => 'Toronto',
                'code' => 'CA-ON-TOR',
                'is_capital' => true,
                'is_default' => true,
                'region_id' => $ontarioRegion?->id,
                'latitude' => 43.6532,
                'longitude' => -79.3832,
                'population' => 2930000,
                'postal_codes' => ['M5H 2N2', 'M5B 2C3', 'M5G 1X5'],
                'translations' => [
                    'lt' => ['name' => 'Torontas', 'description' => 'Kanados sostinė'],
                    'en' => ['name' => 'Toronto', 'description' => 'Capital of Canada'],
                ],
            ],
            [
                'name' => 'Ottawa',
                'code' => 'CA-ON-OTT',
                'region_id' => $ontarioRegion?->id,
                'latitude' => 45.4215,
                'longitude' => -75.6972,
                'population' => 1017449,
                'postal_codes' => ['K1A 0A6', 'K1P 1J1', 'K2P 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Otava', 'description' => 'Federacinė sostinė'],
                    'en' => ['name' => 'Ottawa', 'description' => 'Federal capital'],
                ],
            ],
            [
                'name' => 'Hamilton',
                'code' => 'CA-ON-HAM',
                'region_id' => $ontarioRegion?->id,
                'latitude' => 43.2557,
                'longitude' => -79.8711,
                'population' => 767000,
                'postal_codes' => ['L8P 4X3', 'L8L 4X3', 'L8M 1X3'],
                'translations' => [
                    'lt' => ['name' => 'Hamiltonas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Hamilton', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'London',
                'code' => 'CA-ON-LON',
                'region_id' => $ontarioRegion?->id,
                'latitude' => 42.9849,
                'longitude' => -81.2453,
                'population' => 422324,
                'postal_codes' => ['N6A 3K7', 'N6B 1A1', 'N6C 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Londonas', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'London', 'description' => 'University city'],
                ],
            ],
            [
                'name' => 'Kitchener',
                'code' => 'CA-ON-KIT',
                'region_id' => $ontarioRegion?->id,
                'latitude' => 43.4504,
                'longitude' => -80.4832,
                'population' => 256885,
                'postal_codes' => ['N2G 1A1', 'N2H 1A1', 'N2K 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Kitcheneris', 'description' => 'Technologijų centras'],
                    'en' => ['name' => 'Kitchener', 'description' => 'Technology center'],
                ],
            ],
            // Quebec
            [
                'name' => 'Montreal',
                'code' => 'CA-QC-MON',
                'region_id' => $quebecRegion?->id,
                'latitude' => 45.5017,
                'longitude' => -73.5673,
                'population' => 1780000,
                'postal_codes' => ['H1A 0A1', 'H2A 1A1', 'H3A 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Monrealis', 'description' => 'Kvebeko sostinė'],
                    'en' => ['name' => 'Montreal', 'description' => 'Capital of Quebec'],
                ],
            ],
            [
                'name' => 'Quebec City',
                'code' => 'CA-QC-QUE',
                'region_id' => $quebecRegion?->id,
                'latitude' => 46.8139,
                'longitude' => -71.208,
                'population' => 549459,
                'postal_codes' => ['G1A 1A1', 'G1B 1A1', 'G1C 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Kvebeko miestas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Quebec City', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Laval',
                'code' => 'CA-QC-LAV',
                'region_id' => $quebecRegion?->id,
                'latitude' => 45.6066,
                'longitude' => -73.7124,
                'population' => 438366,
                'postal_codes' => ['H7A 1A1', 'H7B 1A1', 'H7C 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Laval', 'description' => 'Monrealio priemiestis'],
                    'en' => ['name' => 'Laval', 'description' => 'Montreal suburb'],
                ],
            ],
            // British Columbia
            [
                'name' => 'Vancouver',
                'code' => 'CA-BC-VAN',
                'region_id' => $britishColumbiaRegion?->id,
                'latitude' => 49.2827,
                'longitude' => -123.1207,
                'population' => 675218,
                'postal_codes' => ['V6B 1A1', 'V6C 1A1', 'V6E 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Vankuveris', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'Vancouver', 'description' => 'Seaside city'],
                ],
            ],
            [
                'name' => 'Victoria',
                'code' => 'CA-BC-VIC',
                'region_id' => $britishColumbiaRegion?->id,
                'latitude' => 48.4284,
                'longitude' => -123.3656,
                'population' => 92000,
                'postal_codes' => ['V8W 1A1', 'V8V 1A1', 'V8T 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Viktorija', 'description' => 'Britų Kolumbijos sostinė'],
                    'en' => ['name' => 'Victoria', 'description' => 'Capital of British Columbia'],
                ],
            ],
            [
                'name' => 'Surrey',
                'code' => 'CA-BC-SUR',
                'region_id' => $britishColumbiaRegion?->id,
                'latitude' => 49.1913,
                'longitude' => -122.849,
                'population' => 598530,
                'postal_codes' => ['V3S 1A1', 'V3T 1A1', 'V3V 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Saris', 'description' => 'Vankuverio priemiestis'],
                    'en' => ['name' => 'Surrey', 'description' => 'Vancouver suburb'],
                ],
            ],
            // Alberta
            [
                'name' => 'Calgary',
                'code' => 'CA-AB-CAL',
                'region_id' => $albertaRegion?->id,
                'latitude' => 51.0447,
                'longitude' => -114.0719,
                'population' => 1306784,
                'postal_codes' => ['T2P 1A1', 'T2R 1A1', 'T2S 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Kalgaris', 'description' => 'Naftos pramonės centras'],
                    'en' => ['name' => 'Calgary', 'description' => 'Oil industry center'],
                ],
            ],
            [
                'name' => 'Edmonton',
                'code' => 'CA-AB-EDM',
                'region_id' => $albertaRegion?->id,
                'latitude' => 53.5461,
                'longitude' => -113.4938,
                'population' => 1010899,
                'postal_codes' => ['T5J 1A1', 'T5K 1A1', 'T5L 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Edmontonas', 'description' => 'Albertos sostinė'],
                    'en' => ['name' => 'Edmonton', 'description' => 'Capital of Alberta'],
                ],
            ],
            // Manitoba
            [
                'name' => 'Winnipeg',
                'code' => 'CA-MB-WIN',
                'region_id' => $manitobaRegion?->id,
                'latitude' => 49.8951,
                'longitude' => -97.1384,
                'population' => 749607,
                'postal_codes' => ['R3C 1A1', 'R3E 1A1', 'R3G 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Vinipegas', 'description' => 'Manitobos sostinė'],
                    'en' => ['name' => 'Winnipeg', 'description' => 'Capital of Manitoba'],
                ],
            ],
            // Saskatchewan
            [
                'name' => 'Saskatoon',
                'code' => 'CA-SK-SAS',
                'region_id' => $saskatchewanRegion?->id,
                'latitude' => 52.1579,
                'longitude' => -106.6702,
                'population' => 317480,
                'postal_codes' => ['S7K 1A1', 'S7L 1A1', 'S7M 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Saskatonas', 'description' => 'Didžiausias Saskatchevano miestas'],
                    'en' => ['name' => 'Saskatoon', 'description' => 'Largest city in Saskatchewan'],
                ],
            ],
            [
                'name' => 'Regina',
                'code' => 'CA-SK-REG',
                'region_id' => $saskatchewanRegion?->id,
                'latitude' => 50.4452,
                'longitude' => -104.6189,
                'population' => 236481,
                'postal_codes' => ['S4P 1A1', 'S4R 1A1', 'S4S 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Regina', 'description' => 'Saskatchevano sostinė'],
                    'en' => ['name' => 'Regina', 'description' => 'Capital of Saskatchewan'],
                ],
            ],
            // Nova Scotia
            [
                'name' => 'Halifax',
                'code' => 'CA-NS-HAL',
                'region_id' => $novaScotiaRegion?->id,
                'latitude' => 44.6488,
                'longitude' => -63.5752,
                'population' => 448544,
                'postal_codes' => ['B3H 1A1', 'B3J 1A1', 'B3K 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Halifaksas', 'description' => 'Naujosios Škotijos sostinė'],
                    'en' => ['name' => 'Halifax', 'description' => 'Capital of Nova Scotia'],
                ],
            ],
            // New Brunswick
            [
                'name' => 'Moncton',
                'code' => 'CA-NB-MON',
                'region_id' => $newBrunswickRegion?->id,
                'latitude' => 46.0878,
                'longitude' => -64.7782,
                'population' => 144810,
                'postal_codes' => ['E1C 1A1', 'E1E 1A1', 'E1G 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Monktonas', 'description' => 'Naujojo Brunsviko centras'],
                    'en' => ['name' => 'Moncton', 'description' => 'Center of New Brunswick'],
                ],
            ],
            [
                'name' => 'Saint John',
                'code' => 'CA-NB-SAJ',
                'region_id' => $newBrunswickRegion?->id,
                'latitude' => 45.2733,
                'longitude' => -66.0633,
                'population' => 70063,
                'postal_codes' => ['E2L 1A1', 'E2M 1A1', 'E2N 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Sent Džonas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Saint John', 'description' => 'Port city'],
                ],
            ],
            // Newfoundland and Labrador
            [
                'name' => "St. John's",
                'code' => 'CA-NL-STJ',
                'region_id' => $newfoundlandRegion?->id,
                'latitude' => 47.5615,
                'longitude' => -52.7126,
                'population' => 113948,
                'postal_codes' => ['A1A 1A1', 'A1B 1A1', 'A1C 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Sent Džonsas', 'description' => 'Niufaundlando sostinė'],
                    'en' => ['name' => "St. John's", 'description' => 'Capital of Newfoundland'],
                ],
            ],
            // Prince Edward Island
            [
                'name' => 'Charlottetown',
                'code' => 'CA-PE-CHA',
                'region_id' => $peiRegion?->id,
                'latitude' => 46.2382,
                'longitude' => -63.1311,
                'population' => 40000,
                'postal_codes' => ['C1A 1A1', 'C1B 1A1', 'C1C 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Šarlotetaunas', 'description' => 'Princo Edvardo salos sostinė'],
                    'en' => ['name' => 'Charlottetown', 'description' => 'Capital of Prince Edward Island'],
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
                    'country_id' => $canada->id,
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
