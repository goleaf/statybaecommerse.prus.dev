<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class CanadaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $canada = Country::where('cca2', 'CA')->first();
        $naZone = Zone::where('code', 'NA')->first();

        // Regions are no longer used in the database schema

        $cities = [
            // Ontario
            [
                'name' => 'Toronto',
                'code' => 'CA-ON-TOR',
                'is_capital' => true,
                'is_default' => true,
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
                'latitude' => 46.2382,
                'longitude' => -63.1311,
                'population' => 40000,
                'postal_codes' => ['C1A 1A1', 'C1B 1A1', 'C1C 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Šarlotetaunas', 'description' => 'Princo Edvardo salos sostinė'],
                    'en' => ['name' => 'Charlottetown', 'description' => 'Capital of Prince Edward Island'],
                ],
            ],
            // Additional Ontario cities
            [
                'name' => 'Mississauga',
                'code' => 'CA-ON-MIS',
                'latitude' => 43.5890,
                'longitude' => -79.6441,
                'population' => 721599,
                'postal_codes' => ['L5A 1A1', 'L5B 1A1', 'L5C 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Misisauga', 'description' => 'Toronto priemiestis'],
                    'en' => ['name' => 'Mississauga', 'description' => 'Toronto suburb'],
                ],
            ],
            [
                'name' => 'Brampton',
                'code' => 'CA-ON-BRA',
                'latitude' => 43.6834,
                'longitude' => -79.7663,
                'population' => 656480,
                'postal_codes' => ['L6T 1A1', 'L6V 1A1', 'L6W 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Bramptonas', 'description' => 'Daugiakultūris miestas'],
                    'en' => ['name' => 'Brampton', 'description' => 'Multicultural city'],
                ],
            ],
            [
                'name' => 'Markham',
                'code' => 'CA-ON-MAR',
                'latitude' => 43.8668,
                'longitude' => -79.2663,
                'population' => 338503,
                'postal_codes' => ['L3R 1A1', 'L3S 1A1', 'L3T 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Markhamas', 'description' => 'Technologijų centras'],
                    'en' => ['name' => 'Markham', 'description' => 'Technology center'],
                ],
            ],
            [
                'name' => 'Vaughan',
                'code' => 'CA-ON-VAU',
                'latitude' => 43.8361,
                'longitude' => -79.4983,
                'population' => 323103,
                'postal_codes' => ['L4J 1A1', 'L4K 1A1', 'L4L 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Vaughanas', 'description' => 'Gyvenamasis rajonas'],
                    'en' => ['name' => 'Vaughan', 'description' => 'Residential area'],
                ],
            ],
            [
                'name' => 'Windsor',
                'code' => 'CA-ON-WIN',
                'latitude' => 42.3149,
                'longitude' => -83.0364,
                'population' => 229660,
                'postal_codes' => ['N8X 1A1', 'N8Y 1A1', 'N9A 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Vindsoras', 'description' => 'Automobilių pramonės centras'],
                    'en' => ['name' => 'Windsor', 'description' => 'Automotive industry center'],
                ],
            ],
            [
                'name' => 'Richmond Hill',
                'code' => 'CA-ON-RIC',
                'latitude' => 43.8828,
                'longitude' => -79.4403,
                'population' => 202022,
                'postal_codes' => ['L4B 1A1', 'L4C 1A1', 'L4E 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Ričmondo kalva', 'description' => 'Prabangus rajonas'],
                    'en' => ['name' => 'Richmond Hill', 'description' => 'Upscale area'],
                ],
            ],
            [
                'name' => 'Oakville',
                'code' => 'CA-ON-OAK',
                'latitude' => 43.4675,
                'longitude' => -79.6877,
                'population' => 213759,
                'postal_codes' => ['L6H 1A1', 'L6J 1A1', 'L6K 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Okvilis', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'Oakville', 'description' => 'Lakeside city'],
                ],
            ],
            // Additional Quebec cities
            [
                'name' => 'Gatineau',
                'code' => 'CA-QC-GAT',
                'latitude' => 45.4773,
                'longitude' => -75.7013,
                'population' => 287372,
                'postal_codes' => ['J8P 1A1', 'J8R 1A1', 'J8T 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Gatino', 'description' => 'Otavos priemiestis'],
                    'en' => ['name' => 'Gatineau', 'description' => 'Ottawa suburb'],
                ],
            ],
            [
                'name' => 'Longueuil',
                'code' => 'CA-QC-LON',
                'latitude' => 45.5312,
                'longitude' => -73.5188,
                'population' => 254349,
                'postal_codes' => ['J4G 1A1', 'J4H 1A1', 'J4J 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Longejilis', 'description' => 'Monrealio priemiestis'],
                    'en' => ['name' => 'Longueuil', 'description' => 'Montreal suburb'],
                ],
            ],
            [
                'name' => 'Sherbrooke',
                'code' => 'CA-QC-SHE',
                'latitude' => 45.4042,
                'longitude' => -71.8929,
                'population' => 172950,
                'postal_codes' => ['J1E 1A1', 'J1G 1A1', 'J1H 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Šerbrukas', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Sherbrooke', 'description' => 'University city'],
                ],
            ],
            [
                'name' => 'Saguenay',
                'code' => 'CA-QC-SAG',
                'latitude' => 48.4281,
                'longitude' => -71.0675,
                'population' => 145949,
                'postal_codes' => ['G7B 1A1', 'G7H 1A1', 'G7J 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Saguenė', 'description' => 'Aliuminio pramonės centras'],
                    'en' => ['name' => 'Saguenay', 'description' => 'Aluminum industry center'],
                ],
            ],
            // Additional British Columbia cities
            [
                'name' => 'Burnaby',
                'code' => 'CA-BC-BUR',
                'latitude' => 49.2488,
                'longitude' => -122.9805,
                'population' => 249197,
                'postal_codes' => ['V5A 1A1', 'V5B 1A1', 'V5C 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Bernabis', 'description' => 'Vankuverio priemiestis'],
                    'en' => ['name' => 'Burnaby', 'description' => 'Vancouver suburb'],
                ],
            ],
            [
                'name' => 'Richmond',
                'code' => 'CA-BC-RIC',
                'latitude' => 49.1666,
                'longitude' => -123.1336,
                'population' => 209937,
                'postal_codes' => ['V6V 1A1', 'V6W 1A1', 'V6X 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Ričmondas', 'description' => 'Azijiečių bendruomenės centras'],
                    'en' => ['name' => 'Richmond', 'description' => 'Asian community center'],
                ],
            ],
            [
                'name' => 'Abbotsford',
                'code' => 'CA-BC-ABB',
                'latitude' => 49.0504,
                'longitude' => -122.3045,
                'population' => 153524,
                'postal_codes' => ['V2S 1A1', 'V2T 1A1', 'V2V 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Abotfordas', 'description' => 'Žemės ūkio centras'],
                    'en' => ['name' => 'Abbotsford', 'description' => 'Agricultural center'],
                ],
            ],
            [
                'name' => 'Coquitlam',
                'code' => 'CA-BC-COQ',
                'latitude' => 49.2838,
                'longitude' => -122.7932,
                'population' => 148625,
                'postal_codes' => ['V3B 1A1', 'V3C 1A1', 'V3E 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Kokvitlamas', 'description' => 'Šeimyninis rajonas'],
                    'en' => ['name' => 'Coquitlam', 'description' => 'Family area'],
                ],
            ],
            // Additional Alberta cities
            [
                'name' => 'Red Deer',
                'code' => 'CA-AB-RED',
                'latitude' => 52.2681,
                'longitude' => -113.8112,
                'population' => 103588,
                'postal_codes' => ['T4N 1A1', 'T4P 1A1', 'T4R 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Raudonoji elnia', 'description' => 'Transporto mazgas'],
                    'en' => ['name' => 'Red Deer', 'description' => 'Transportation hub'],
                ],
            ],
            [
                'name' => 'Lethbridge',
                'code' => 'CA-AB-LET',
                'latitude' => 49.6939,
                'longitude' => -112.8418,
                'population' => 101482,
                'postal_codes' => ['T1H 1A1', 'T1J 1A1', 'T1K 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Letbridžas', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Lethbridge', 'description' => 'University city'],
                ],
            ],
            [
                'name' => 'St. Albert',
                'code' => 'CA-AB-STA',
                'latitude' => 53.6333,
                'longitude' => -113.6167,
                'population' => 66589,
                'postal_codes' => ['T8N 1A1', 'T8R 1A1', 'T8S 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Sent Albertas', 'description' => 'Edmonton priemiestis'],
                    'en' => ['name' => 'St. Albert', 'description' => 'Edmonton suburb'],
                ],
            ],
            // Additional Manitoba cities
            [
                'name' => 'Brandon',
                'code' => 'CA-MB-BRA',
                'latitude' => 49.8484,
                'longitude' => -99.9530,
                'population' => 48359,
                'postal_codes' => ['R7A 1A1', 'R7B 1A1', 'R7C 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Brandonas', 'description' => 'Žemės ūkio centras'],
                    'en' => ['name' => 'Brandon', 'description' => 'Agricultural center'],
                ],
            ],
            [
                'name' => 'Steinbach',
                'code' => 'CA-MB-STE',
                'latitude' => 49.5258,
                'longitude' => -96.6844,
                'population' => 17189,
                'postal_codes' => ['R5G 1A1', 'R5H 1A1', 'R5J 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Šteinbachas', 'description' => 'Mennonitų bendruomenės centras'],
                    'en' => ['name' => 'Steinbach', 'description' => 'Mennonite community center'],
                ],
            ],
            // Additional Saskatchewan cities
            [
                'name' => 'Prince Albert',
                'code' => 'CA-SK-PRA',
                'latitude' => 53.2048,
                'longitude' => -105.7531,
                'population' => 37036,
                'postal_codes' => ['S6V 1A1', 'S6W 1A1', 'S6X 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Princo Albertas', 'description' => 'Šiaurės centras'],
                    'en' => ['name' => 'Prince Albert', 'description' => 'Northern center'],
                ],
            ],
            [
                'name' => 'Moose Jaw',
                'code' => 'CA-SK-MOO',
                'latitude' => 50.3931,
                'longitude' => -105.5349,
                'population' => 33890,
                'postal_codes' => ['S6H 1A1', 'S6J 1A1', 'S6K 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Muso žandas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Moose Jaw', 'description' => 'Historic city'],
                ],
            ],
            // Additional Nova Scotia cities
            [
                'name' => 'Dartmouth',
                'code' => 'CA-NS-DAR',
                'latitude' => 44.6709,
                'longitude' => -63.5773,
                'population' => 101343,
                'postal_codes' => ['B2W 1A1', 'B2X 1A1', 'B2Y 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Dartmutas', 'description' => 'Halifakso priemiestis'],
                    'en' => ['name' => 'Dartmouth', 'description' => 'Halifax suburb'],
                ],
            ],
            [
                'name' => 'Sydney',
                'code' => 'CA-NS-SYD',
                'latitude' => 46.1368,
                'longitude' => -60.1942,
                'population' => 29804,
                'postal_codes' => ['B1P 1A1', 'B1R 1A1', 'B1S 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Sidnis', 'description' => 'Kep Bruetono centras'],
                    'en' => ['name' => 'Sydney', 'description' => 'Cape Breton center'],
                ],
            ],
            // Additional New Brunswick cities
            [
                'name' => 'Fredericton',
                'code' => 'CA-NB-FRE',
                'latitude' => 45.9636,
                'longitude' => -66.6431,
                'population' => 63116,
                'postal_codes' => ['E3A 1A1', 'E3B 1A1', 'E3C 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Frederiktonas', 'description' => 'Naujojo Brunsviko sostinė'],
                    'en' => ['name' => 'Fredericton', 'description' => 'Capital of New Brunswick'],
                ],
            ],
            [
                'name' => 'Dieppe',
                'code' => 'CA-NB-DIE',
                'latitude' => 46.0989,
                'longitude' => -64.7242,
                'population' => 28533,
                'postal_codes' => ['E1A 1A1', 'E1B 1A1', 'E1D 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Diepė', 'description' => 'Francų bendruomenės centras'],
                    'en' => ['name' => 'Dieppe', 'description' => 'French community center'],
                ],
            ],
            // Additional Newfoundland cities
            [
                'name' => 'Mount Pearl',
                'code' => 'CA-NL-MOU',
                'latitude' => 47.5189,
                'longitude' => -52.8050,
                'population' => 22957,
                'postal_codes' => ['A1N 1A1', 'A1P 1A1', 'A1R 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Perlo kalnas', 'description' => 'Sent Džonso priemiestis'],
                    'en' => ['name' => 'Mount Pearl', 'description' => 'St. John\'s suburb'],
                ],
            ],
            [
                'name' => 'Corner Brook',
                'code' => 'CA-NL-COR',
                'latitude' => 48.9469,
                'longitude' => -57.9689,
                'population' => 19916,
                'postal_codes' => ['A2H 1A1', 'A2J 1A1', 'A2K 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Korner brukas', 'description' => 'Vakarų Niufaundlando centras'],
                    'en' => ['name' => 'Corner Brook', 'description' => 'Western Newfoundland center'],
                ],
            ],
            // Additional Prince Edward Island cities
            [
                'name' => 'Summerside',
                'code' => 'CA-PE-SUM',
                'latitude' => 46.3939,
                'longitude' => -63.7892,
                'population' => 15000,
                'postal_codes' => ['C1N 1A1', 'C1P 1A1', 'C1R 1A1'],
                'translations' => [
                    'lt' => ['name' => 'Samersaidis', 'description' => 'Antrasis didžiausias miestas'],
                    'en' => ['name' => 'Summerside', 'description' => 'Second largest city'],
                ],
            ],
        ];

        foreach ($cities as $cityData) {
            $city = City::updateOrCreate(
                ['code' => $cityData['code']],
                [
                    'name' => $cityData['name'],
                    'slug' => \Str::slug($cityData['name'].'-'.$cityData['code']),
                    'is_enabled' => true,
                    'is_default' => $cityData['is_default'] ?? false,
                    'is_capital' => $cityData['is_capital'] ?? false,
                    'country_id' => $canada->id,
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
