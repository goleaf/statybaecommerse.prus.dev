<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class USACitiesSeeder extends Seeder
{
    public function run(): void
    {
        $usa = Country::where('cca2', 'US')->first();
        $naZone = Zone::where('code', 'NA')->first();

        // Regions are no longer used in the database schema
        
        $cities = [
            // California
            [
                'name' => 'Los Angeles',
                'code' => 'US-CA-LAX',
                'latitude' => 34.0522,
                'longitude' => -118.2437,
                'population' => 3971883,
                'postal_codes' => ['90001', '90002', '90003'],
                'translations' => [
                    'lt' => ['name' => 'Los Andželas', 'description' => 'Kino pramonės centras'],
                    'en' => ['name' => 'Los Angeles', 'description' => 'Entertainment industry center']
            ],
            ],
            [
                'name' => 'San Diego',
                'code' => 'US-CA-SAN',
                'latitude' => 32.7157,
                'longitude' => -117.1611,
                'population' => 1423851,
                'postal_codes' => ['92101', '92102', '92103'],
                'translations' => [
                    'lt' => ['name' => 'San Diegas', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'San Diego', 'description' => 'Seaside city']
            ],
            ],
            [
                'name' => 'San Jose',
                'code' => 'US-CA-SJO',
                'latitude' => 37.3382,
                'longitude' => -121.8863,
                'population' => 1035317,
                'postal_codes' => ['95110', '95111', '95112'],
                'translations' => [
                    'lt' => ['name' => 'San Chosė', 'description' => 'Technologijų centras'],
                    'en' => ['name' => 'San Jose', 'description' => 'Technology center']
            ],
            ],
            [
                'name' => 'San Francisco',
                'code' => 'US-CA-SFO',
                'latitude' => 37.7749,
                'longitude' => -122.4194,
                'population' => 873965,
                'postal_codes' => ['94102', '94103', '94104'],
                'translations' => [
                    'lt' => ['name' => 'San Franciskas', 'description' => 'Finansų centras'],
                    'en' => ['name' => 'San Francisco', 'description' => 'Financial center']
            ],
            ],
            [
                'name' => 'Fresno',
                'code' => 'US-CA-FRE',
                'latitude' => 36.7378,
                'longitude' => -119.7871,
                'population' => 542107,
                'postal_codes' => ['93701', '93702', '93703'],
                'translations' => [
                    'lt' => ['name' => 'Fresnas', 'description' => 'Žemės ūkio centras'],
                    'en' => ['name' => 'Fresno', 'description' => 'Agricultural center']
            ],
            ],
            // Texas
            [
                'name' => 'Houston',
                'code' => 'US-TX-HOU',
                'latitude' => 29.7604,
                'longitude' => -95.3698,
                'population' => 2320268,
                'postal_codes' => ['77001', '77002', '77003'],
                'translations' => [
                    'lt' => ['name' => 'Hjustonas', 'description' => 'Naftos pramonės centras'],
                    'en' => ['name' => 'Houston', 'description' => 'Oil industry center']
            ],
            ],
            [
                'name' => 'San Antonio',
                'code' => 'US-TX-SAT',
                'latitude' => 29.4241,
                'longitude' => -98.4936,
                'population' => 1547253,
                'postal_codes' => ['78201', '78202', '78203'],
                'translations' => [
                    'lt' => ['name' => 'San Antonijas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'San Antonio', 'description' => 'Historic city']
            ],
            ],
            [
                'name' => 'Dallas',
                'code' => 'US-TX-DAL',
                'latitude' => 32.7767,
                'longitude' => -96.797,
                'population' => 1343573,
                'postal_codes' => ['75201', '75202', '75203'],
                'translations' => [
                    'lt' => ['name' => 'Dalasas', 'description' => 'Finansų centras'],
                    'en' => ['name' => 'Dallas', 'description' => 'Financial center']
            ],
            ],
            [
                'name' => 'Austin',
                'code' => 'US-TX-AUS',
                'latitude' => 30.2672,
                'longitude' => -97.7431,
                'population' => 978908,
                'postal_codes' => ['73301', '73302', '73303'],
                'translations' => [
                    'lt' => ['name' => 'Ostinas', 'description' => 'Teksto sostinė'],
                    'en' => ['name' => 'Austin', 'description' => 'Music capital']
            ],
            ],
            // Florida
            [
                'name' => 'Jacksonville',
                'code' => 'US-FL-JAX',
                'latitude' => 30.3322,
                'longitude' => -81.6557,
                'population' => 949611,
                'postal_codes' => ['32099', '32201', '32202'],
                'translations' => [
                    'lt' => ['name' => 'Džeksonvilis', 'description' => 'Didžiausias Floridos miestas'],
                    'en' => ['name' => 'Jacksonville', 'description' => 'Largest city in Florida']
            ],
            ],
            [
                'name' => 'Miami',
                'code' => 'US-FL-MIA',
                'latitude' => 25.7617,
                'longitude' => -80.1918,
                'population' => 467963,
                'postal_codes' => ['33101', '33102', '33103'],
                'translations' => [
                    'lt' => ['name' => 'Majamis', 'description' => 'Kurortinis miestas'],
                    'en' => ['name' => 'Miami', 'description' => 'Resort city']
            ],
            ],
            [
                'name' => 'Tampa',
                'code' => 'US-FL-TAM',
                'latitude' => 27.9506,
                'longitude' => -82.4572,
                'population' => 384959,
                'postal_codes' => ['33601', '33602', '33603'],
                'translations' => [
                    'lt' => ['name' => 'Tampa', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Tampa', 'description' => 'Port city']
            ],
            ],
            // New York
            [
                'name' => 'New York City',
                'code' => 'US-NY-NYC',
                'is_capital' => true,
                'is_default' => true,
                'latitude' => 40.7128,
                'longitude' => -74.006,
                'population' => 8336817,
                'postal_codes' => ['10001', '10002', '10003'],
                'translations' => [
                    'lt' => ['name' => 'Niujorkas', 'description' => 'Didžiausias JAV miestas'],
                    'en' => ['name' => 'New York City', 'description' => 'Largest city in USA']
            ],
            ],
            [
                'name' => 'Buffalo',
                'code' => 'US-NY-BUF',
                'latitude' => 42.8864,
                'longitude' => -78.8784,
                'population' => 278349,
                'postal_codes' => ['14201', '14202', '14203'],
                'translations' => [
                    'lt' => ['name' => 'Bufalas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Buffalo', 'description' => 'Industrial city']
            ],
            ],
            // Illinois
            [
                'name' => 'Chicago',
                'code' => 'US-IL-CHI',
                'latitude' => 41.8781,
                'longitude' => -87.6298,
                'population' => 2746388,
                'postal_codes' => ['60601', '60602', '60603'],
                'translations' => [
                    'lt' => ['name' => 'Čikaga', 'description' => 'Vėjo miestas'],
                    'en' => ['name' => 'Chicago', 'description' => 'Windy City']
            ],
            ],
            // Pennsylvania
            [
                'name' => 'Philadelphia',
                'code' => 'US-PA-PHI',
                'latitude' => 39.9526,
                'longitude' => -75.1652,
                'population' => 1584064,
                'postal_codes' => ['19101', '19102', '19103'],
                'translations' => [
                    'lt' => ['name' => 'Filadelfija', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Philadelphia', 'description' => 'Historic city']
            ],
            ],
            [
                'name' => 'Pittsburgh',
                'code' => 'US-PA-PIT',
                'latitude' => 40.4406,
                'longitude' => -79.9959,
                'population' => 302971,
                'postal_codes' => ['15201', '15202', '15203'],
                'translations' => [
                    'lt' => ['name' => 'Pitsburgas', 'description' => 'Plieno miestas'],
                    'en' => ['name' => 'Pittsburgh', 'description' => 'Steel city']
            ],
            ],
            // Ohio
            [
                'name' => 'Columbus',
                'code' => 'US-OH-COL',
                'latitude' => 39.9612,
                'longitude' => -82.9988,
                'population' => 905748,
                'postal_codes' => ['43201', '43202', '43203'],
                'translations' => [
                    'lt' => ['name' => 'Kolumbas', 'description' => 'Ohajo sostinė'],
                    'en' => ['name' => 'Columbus', 'description' => 'Capital of Ohio']
            ],
            ],
            [
                'name' => 'Cleveland',
                'code' => 'US-OH-CLE',
                'latitude' => 41.4993,
                'longitude' => -81.6944,
                'population' => 383793,
                'postal_codes' => ['44101', '44102', '44103'],
                'translations' => [
                    'lt' => ['name' => 'Klivlandas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Cleveland', 'description' => 'Industrial city']
            ],
            ],
            // Georgia
            [
                'name' => 'Atlanta',
                'code' => 'US-GA-ATL',
                'latitude' => 33.749,
                'longitude' => -84.388,
                'population' => 498715,
                'postal_codes' => ['30301', '30302', '30303'],
                'translations' => [
                    'lt' => ['name' => 'Atlanta', 'description' => 'Džordžijos sostinė'],
                    'en' => ['name' => 'Atlanta', 'description' => 'Capital of Georgia']
            ],
            ],
            // North Carolina
            [
                'name' => 'Charlotte',
                'code' => 'US-NC-CHA',
                'latitude' => 35.2271,
                'longitude' => -80.8431,
                'population' => 885708,
                'postal_codes' => ['28201', '28202', '28203'],
                'translations' => [
                    'lt' => ['name' => 'Šarlotė', 'description' => 'Finansų centras'],
                    'en' => ['name' => 'Charlotte', 'description' => 'Financial center']
            ],
            ],
            // Michigan
            [
                'name' => 'Detroit',
                'code' => 'US-MI-DET',
                'latitude' => 42.3314,
                'longitude' => -83.0458,
                'population' => 639111,
                'postal_codes' => ['48201', '48202', '48203'],
                'translations' => [
                    'lt' => ['name' => 'Detroitas', 'description' => 'Automobilių pramonės centras'],
                    'en' => ['name' => 'Detroit', 'description' => 'Automotive industry center']
            ],
            ],
            // Additional California cities
            [
                'name' => 'Sacramento',
                'code' => 'US-CA-SAC',
                'latitude' => 38.5816,
                'longitude' => -121.4944,
                'population' => 524943,
                'postal_codes' => ['95814', '95815', '95816'],
                'translations' => [
                    'lt' => ['name' => 'Sakramentas', 'description' => 'Kalifornijos sostinė'],
                    'en' => ['name' => 'Sacramento', 'description' => 'Capital of California']
            ],
            ],
            [
                'name' => 'Long Beach',
                'code' => 'US-CA-LGB',
                'latitude' => 33.7701,
                'longitude' => -118.1937,
                'population' => 466742,
                'postal_codes' => ['90801', '90802', '90803'],
                'translations' => [
                    'lt' => ['name' => 'Long Beach', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Long Beach', 'description' => 'Port city']
            ],
            ],
            [
                'name' => 'Oakland',
                'code' => 'US-CA-OAK',
                'latitude' => 37.8044,
                'longitude' => -122.2712,
                'population' => 433823,
                'postal_codes' => ['94601', '94602', '94603'],
                'translations' => [
                    'lt' => ['name' => 'Oklandas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Oakland', 'description' => 'Port city']
            ],
            ],
            [
                'name' => 'Bakersfield',
                'code' => 'US-CA-BAK',
                'latitude' => 35.3733,
                'longitude' => -119.0187,
                'population' => 384145,
                'postal_codes' => ['93301', '93302', '93303'],
                'translations' => [
                    'lt' => ['name' => 'Bakersfieldas', 'description' => 'Naftos pramonės centras'],
                    'en' => ['name' => 'Bakersfield', 'description' => 'Oil industry center']
            ],
            ],
            [
                'name' => 'Anaheim',
                'code' => 'US-CA-ANA',
                'latitude' => 33.8366,
                'longitude' => -117.9143,
                'population' => 346824,
                'postal_codes' => ['92801', '92802', '92803'],
                'translations' => [
                    'lt' => ['name' => 'Anaheimas', 'description' => 'Disneyland miestas'],
                    'en' => ['name' => 'Anaheim', 'description' => 'Disneyland city']
            ],
            ],
            [
                'name' => 'Santa Ana',
                'code' => 'US-CA-SNA',
                'latitude' => 33.7455,
                'longitude' => -117.8677,
                'population' => 334227,
                'postal_codes' => ['92701', '92702', '92703'],
                'translations' => [
                    'lt' => ['name' => 'Santa Ana', 'description' => 'Pramonės centras'],
                    'en' => ['name' => 'Santa Ana', 'description' => 'Industrial center']
            ],
            ],
            [
                'name' => 'Riverside',
                'code' => 'US-CA-RIV',
                'latitude' => 33.9533,
                'longitude' => -117.3962,
                'population' => 314998,
                'postal_codes' => ['92501', '92502', '92503'],
                'translations' => [
                    'lt' => ['name' => 'Riversidas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Riverside', 'description' => 'Industrial city']
            ],
            ],
            [
                'name' => 'Stockton',
                'code' => 'US-CA-STO',
                'latitude' => 37.9577,
                'longitude' => -121.2908,
                'population' => 310496,
                'postal_codes' => ['95201', '95202', '95203'],
                'translations' => [
                    'lt' => ['name' => 'Stoktonas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Stockton', 'description' => 'Port city']
            ],
            ],
            [
                'name' => 'Irvine',
                'code' => 'US-CA-IRV',
                'latitude' => 33.6846,
                'longitude' => -117.8265,
                'population' => 307670,
                'postal_codes' => ['92602', '92603', '92604'],
                'translations' => [
                    'lt' => ['name' => 'Irvynas', 'description' => 'Technologijų centras'],
                    'en' => ['name' => 'Irvine', 'description' => 'Technology center']
            ],
            ],
            [
                'name' => 'Chula Vista',
                'code' => 'US-CA-CHU',
                'latitude' => 32.6401,
                'longitude' => -117.0842,
                'population' => 275487,
                'postal_codes' => ['91909', '91910', '91911'],
                'translations' => [
                    'lt' => ['name' => 'Chula Vista', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'Chula Vista', 'description' => 'Seaside city']
            ],
            ],
            // Additional Texas cities
            [
                'name' => 'Fort Worth',
                'code' => 'US-TX-FTW',
                'latitude' => 32.7555,
                'longitude' => -97.3308,
                'population' => 918915,
                'postal_codes' => ['76101', '76102', '76103'],
                'translations' => [
                    'lt' => ['name' => 'Fort Worth', 'description' => 'Pramonės centras'],
                    'en' => ['name' => 'Fort Worth', 'description' => 'Industrial center']
            ],
            ],
            [
                'name' => 'El Paso',
                'code' => 'US-TX-ELP',
                'latitude' => 31.7619,
                'longitude' => -106.4850,
                'population' => 681728,
                'postal_codes' => ['79901', '79902', '79903'],
                'translations' => [
                    'lt' => ['name' => 'El Paso', 'description' => 'Sienos miestas'],
                    'en' => ['name' => 'El Paso', 'description' => 'Border city']
            ],
            ],
            [
                'name' => 'Arlington',
                'code' => 'US-TX-ARL',
                'latitude' => 32.7357,
                'longitude' => -97.1081,
                'population' => 398854,
                'postal_codes' => ['76001', '76002', '76003'],
                'translations' => [
                    'lt' => ['name' => 'Arlingtonas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Arlington', 'description' => 'Industrial city']
            ],
            ],
            [
                'name' => 'Corpus Christi',
                'code' => 'US-TX-CC',
                'latitude' => 27.8006,
                'longitude' => -97.3964,
                'population' => 326586,
                'postal_codes' => ['78401', '78402', '78403'],
                'translations' => [
                    'lt' => ['name' => 'Corpus Christi', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Corpus Christi', 'description' => 'Port city']
            ],
            ],
            [
                'name' => 'Plano',
                'code' => 'US-TX-PLA',
                'latitude' => 33.0198,
                'longitude' => -96.6989,
                'population' => 285494,
                'postal_codes' => ['75023', '75024', '75025'],
                'translations' => [
                    'lt' => ['name' => 'Plano', 'description' => 'Technologijų centras'],
                    'en' => ['name' => 'Plano', 'description' => 'Technology center']
            ],
            ],
            [
                'name' => 'Lubbock',
                'code' => 'US-TX-LUB',
                'latitude' => 33.5779,
                'longitude' => -101.8552,
                'population' => 258862,
                'postal_codes' => ['79401', '79402', '79403'],
                'translations' => [
                    'lt' => ['name' => 'Lubokas', 'description' => 'Žemės ūkio centras'],
                    'en' => ['name' => 'Lubbock', 'description' => 'Agricultural center']
            ],
            ],
            [
                'name' => 'Laredo',
                'code' => 'US-TX-LAR',
                'latitude' => 27.5304,
                'longitude' => -99.4803,
                'population' => 255473,
                'postal_codes' => ['78040', '78041', '78042'],
                'translations' => [
                    'lt' => ['name' => 'Laredo', 'description' => 'Sienos miestas'],
                    'en' => ['name' => 'Laredo', 'description' => 'Border city']
            ],
            ],
            [
                'name' => 'Garland',
                'code' => 'US-TX-GAR',
                'latitude' => 32.9126,
                'longitude' => -96.6389,
                'population' => 246018,
                'postal_codes' => ['75040', '75041', '75042'],
                'translations' => [
                    'lt' => ['name' => 'Garlandas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Garland', 'description' => 'Industrial city']
            ],
            ],
            [
                'name' => 'Irving',
                'code' => 'US-TX-IRV',
                'latitude' => 32.8140,
                'longitude' => -96.9489,
                'population' => 256684,
                'postal_codes' => ['75038', '75039', '75040'],
                'translations' => [
                    'lt' => ['name' => 'Irvingas', 'description' => 'Verslo centras'],
                    'en' => ['name' => 'Irving', 'description' => 'Business center']
            ],
            ],
            [
                'name' => 'Amarillo',
                'code' => 'US-TX-AMA',
                'latitude' => 35.2220,
                'longitude' => -101.8313,
                'population' => 200393,
                'postal_codes' => ['79101', '79102', '79103'],
                'translations' => [
                    'lt' => ['name' => 'Amarilas', 'description' => 'Žemės ūkio centras'],
                    'en' => ['name' => 'Amarillo', 'description' => 'Agricultural center']
            ],
            ],
        ];

        foreach ($cities as $cityData) {
            $city = City::updateOrCreate(
                ['code' => $cityData['code']],
                [
                    'name' => $cityData['name'],
                    'slug' => \Str::slug($cityData['name'] . '-' . $cityData['code']),
                    'is_enabled' => true,
                    'is_default' => $cityData['is_default'] ?? false,
                    'is_capital' => $cityData['is_capital'] ?? false,
                    'country_id' => $usa->id,
                    'zone_id' => $naZone?->id,
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
                        'locale' => $locale
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
