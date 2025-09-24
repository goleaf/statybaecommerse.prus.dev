<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class LithuaniaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $lithuania = Country::where('cca2', 'LT')->first();
        $euZone = Zone::where('code', 'EU')->first();
        $ltZone = Zone::where('code', 'LT')->first();

        // Regions are no longer used in the database schema

        $cities = [
            // Vilnius County
            [
                'name' => 'Vilnius',
                'code' => 'LT-VLN',
                'is_capital' => true,
                'is_default' => true,
                'latitude' => 54.6872,
                'longitude' => 25.2797,
                'population' => 588412,
                'postal_codes' => ['01001-14001'],
                'translations' => [
                    'lt' => ['name' => 'Vilnius', 'description' => 'Lietuvos sostinė'],
                    'en' => ['name' => 'Vilnius', 'description' => 'Capital of Lithuania'],
                ],
            ],
            [
                'name' => 'Trakai',
                'code' => 'LT-VL-TRA',
                'latitude' => 54.6333,
                'longitude' => 24.9333,
                'population' => 5406,
                'postal_codes' => ['21142'],
                'translations' => [
                    'lt' => ['name' => 'Trakai', 'description' => 'Istorinis miestas su pilimi'],
                    'en' => ['name' => 'Trakai', 'description' => 'Historic town with castle'],
                ],
            ],
            [
                'name' => 'Elektrėnai',
                'code' => 'LT-VLN-ELE',
                'latitude' => 54.7833,
                'longitude' => 24.6667,
                'population' => 11000,
                'postal_codes' => ['26120'],
                'translations' => [
                    'lt' => ['name' => 'Elektrėnai', 'description' => 'Energetikos miestas'],
                    'en' => ['name' => 'Elektrėnai', 'description' => 'Energy city'],
                ],
            ],
            // Kaunas County
            [
                'name' => 'Kaunas',
                'code' => 'LT-KAU',
                'latitude' => 54.8985,
                'longitude' => 23.9036,
                'population' => 304097,
                'postal_codes' => ['44001-52001'],
                'translations' => [
                    'lt' => ['name' => 'Kaunas', 'description' => 'Antrasis didžiausias Lietuvos miestas'],
                    'en' => ['name' => 'Kaunas', 'description' => 'Second largest city in Lithuania'],
                ],
            ],
            [
                'name' => 'Jonava',
                'code' => 'LT-KAU-JON',
                'latitude' => 55.0833,
                'longitude' => 24.2833,
                'population' => 26000,
                'postal_codes' => ['55164'],
                'translations' => [
                    'lt' => ['name' => 'Jonava', 'description' => 'Chemijos pramonės centras'],
                    'en' => ['name' => 'Jonava', 'description' => 'Chemical industry center'],
                ],
            ],
            [
                'name' => 'Kėdainiai',
                'code' => 'LT-KA-KED',
                'latitude' => 55.2833,
                'longitude' => 23.9833,
                'population' => 23000,
                'postal_codes' => ['57150'],
                'translations' => [
                    'lt' => ['name' => 'Kėdainiai', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Kėdainiai', 'description' => 'Historic city'],
                ],
            ],
            // Klaipėda County
            [
                'name' => 'Klaipėda',
                'code' => 'LT-KLP',
                'latitude' => 55.7033,
                'longitude' => 21.1442,
                'population' => 152008,
                'postal_codes' => ['91001-95001'],
                'translations' => [
                    'lt' => ['name' => 'Klaipėda', 'description' => 'Pagrindinis Lietuvos uostas'],
                    'en' => ['name' => 'Klaipėda', 'description' => 'Main port of Lithuania'],
                ],
            ],
            [
                'name' => 'Šilutė',
                'code' => 'LT-KL-SIL',
                'latitude' => 55.35,
                'longitude' => 21.4833,
                'population' => 17000,
                'postal_codes' => ['99101'],
                'translations' => [
                    'lt' => ['name' => 'Šilutė', 'description' => 'Mažosios Lietuvos centras'],
                    'en' => ['name' => 'Šilutė', 'description' => 'Center of Little Lithuania'],
                ],
            ],
            // Šiauliai County
            [
                'name' => 'Šiauliai',
                'code' => 'LT-SIA',
                'latitude' => 55.9333,
                'longitude' => 23.3167,
                'population' => 101514,
                'postal_codes' => ['76001-80001'],
                'translations' => [
                    'lt' => ['name' => 'Šiauliai', 'description' => 'Šiaurės Lietuvos centras'],
                    'en' => ['name' => 'Šiauliai', 'description' => 'Center of Northern Lithuania'],
                ],
            ],
            [
                'name' => 'Radviliškis',
                'code' => 'LT-SA-RAD',
                'latitude' => 55.8167,
                'longitude' => 23.5333,
                'population' => 16000,
                'postal_codes' => ['82150'],
                'translations' => [
                    'lt' => ['name' => 'Radviliškis', 'description' => 'Geležinkelio mazgas'],
                    'en' => ['name' => 'Radviliškis', 'description' => 'Railway junction'],
                ],
            ],
            // Panevėžys County
            [
                'name' => 'Panevėžys',
                'code' => 'LT-PNV',
                'latitude' => 55.7333,
                'longitude' => 24.35,
                'population' => 87048,
                'postal_codes' => ['35001-39001'],
                'translations' => [
                    'lt' => ['name' => 'Panevėžys', 'description' => 'Aukštaitijos centras'],
                    'en' => ['name' => 'Panevėžys', 'description' => 'Center of Aukštaitija'],
                ],
            ],
            // Alytus County
            [
                'name' => 'Alytus',
                'code' => 'LT-ALT',
                'latitude' => 54.4,
                'longitude' => 24.05,
                'population' => 52000,
                'postal_codes' => ['62001-66001'],
                'translations' => [
                    'lt' => ['name' => 'Alytus', 'description' => 'Dzūkijos centras'],
                    'en' => ['name' => 'Alytus', 'description' => 'Center of Dzūkija'],
                ],
            ],
            // Marijampolė County
            [
                'name' => 'Marijampolė',
                'code' => 'LT-MRJ',
                'latitude' => 54.5667,
                'longitude' => 23.35,
                'population' => 35000,
                'postal_codes' => ['68001-72001'],
                'translations' => [
                    'lt' => ['name' => 'Marijampolė', 'description' => 'Suvalkijos centras'],
                    'en' => ['name' => 'Marijampolė', 'description' => 'Center of Suvalkija'],
                ],
            ],
            // Tauragė County
            [
                'name' => 'Tauragė',
                'code' => 'LT-TRG',
                'latitude' => 55.25,
                'longitude' => 22.2833,
                'population' => 22000,
                'postal_codes' => ['72001-76001'],
                'translations' => [
                    'lt' => ['name' => 'Tauragė', 'description' => 'Žemaitijos pietų centras'],
                    'en' => ['name' => 'Tauragė', 'description' => 'Southern Žemaitija center'],
                ],
            ],
            // Telšiai County
            [
                'name' => 'Telšiai',
                'code' => 'LT-TLS',
                'latitude' => 55.9833,
                'longitude' => 22.25,
                'population' => 22000,
                'postal_codes' => ['87001-91001'],
                'translations' => [
                    'lt' => ['name' => 'Telšiai', 'description' => 'Žemaitijos centras'],
                    'en' => ['name' => 'Telšiai', 'description' => 'Center of Žemaitija'],
                ],
            ],
            // Utena County
            [
                'name' => 'Utena',
                'code' => 'LT-UTN',
                'latitude' => 55.5,
                'longitude' => 25.6,
                'population' => 25000,
                'postal_codes' => ['28001-32001'],
                'translations' => [
                    'lt' => ['name' => 'Utena', 'description' => 'Aukštaitijos šiaurės centras'],
                    'en' => ['name' => 'Utena', 'description' => 'Northern Aukštaitija center'],
                ],
            ],
            [
                'name' => 'Visaginas',
                'code' => 'LT-UT-VIS',
                'latitude' => 55.6,
                'longitude' => 26.4167,
                'population' => 19000,
                'postal_codes' => ['31150'],
                'translations' => [
                    'lt' => ['name' => 'Visaginas', 'description' => 'Atominės elektrinės miestas'],
                    'en' => ['name' => 'Visaginas', 'description' => 'Nuclear power plant city'],
                ],
            ],
            [
                'name' => 'Anykščiai',
                'code' => 'LT-UT-ANY',
                'latitude' => 55.5333,
                'longitude' => 25.1,
                'population' => 9000,
                'postal_codes' => ['29100'],
                'translations' => [
                    'lt' => ['name' => 'Anykščiai', 'description' => 'Šilelio ir Anykščių šilelio miestas'],
                    'en' => ['name' => 'Anykščiai', 'description' => 'City of Šilelis and Anykščių šilelis'],
                ],
            ],
            [
                'name' => 'Ignalina',
                'code' => 'LT-UT-IGN',
                'latitude' => 55.35,
                'longitude' => 26.1667,
                'population' => 6000,
                'postal_codes' => ['30100'],
                'translations' => [
                    'lt' => ['name' => 'Ignalina', 'description' => 'Energetikos miestas'],
                    'en' => ['name' => 'Ignalina', 'description' => 'Energy city'],
                ],
            ],
            [
                'name' => 'Zarasai',
                'code' => 'LT-UT-ZAR',
                'latitude' => 55.7333,
                'longitude' => 26.25,
                'population' => 7000,
                'postal_codes' => ['32100'],
                'translations' => [
                    'lt' => ['name' => 'Zarasai', 'description' => 'Ežerų miestas'],
                    'en' => ['name' => 'Zarasai', 'description' => 'City of lakes'],
                ],
            ],
            [
                'name' => 'Molėtai',
                'code' => 'LT-UT-MOL',
                'latitude' => 55.2333,
                'longitude' => 25.4167,
                'population' => 6000,
                'postal_codes' => ['33100'],
                'translations' => [
                    'lt' => ['name' => 'Molėtai', 'description' => 'Ežerų kraštas'],
                    'en' => ['name' => 'Molėtai', 'description' => 'Lake district'],
                ],
            ],
            // Additional Vilnius County cities
            [
                'name' => 'Ukmergė',
                'code' => 'LT-VL-UKM',
                'latitude' => 55.25,
                'longitude' => 24.75,
                'population' => 22000,
                'postal_codes' => ['20100'],
                'translations' => [
                    'lt' => ['name' => 'Ukmergė', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Ukmergė', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Šalčininkai',
                'code' => 'LT-VL-SAL',
                'latitude' => 54.3167,
                'longitude' => 25.3833,
                'population' => 6000,
                'postal_codes' => ['17100'],
                'translations' => [
                    'lt' => ['name' => 'Šalčininkai', 'description' => 'Lenkų bendruomenės centras'],
                    'en' => ['name' => 'Šalčininkai', 'description' => 'Polish community center'],
                ],
            ],
            [
                'name' => 'Švenčionys',
                'code' => 'LT-VL-SVE',
                'latitude' => 55.1333,
                'longitude' => 26.1667,
                'population' => 5000,
                'postal_codes' => ['18100'],
                'translations' => [
                    'lt' => ['name' => 'Švenčionys', 'description' => 'Miškų miestas'],
                    'en' => ['name' => 'Švenčionys', 'description' => 'Forest city'],
                ],
            ],
            [
                'name' => 'Vievis',
                'code' => 'LT-VL-VIE',
                'latitude' => 54.7667,
                'longitude' => 24.8,
                'population' => 4000,
                'postal_codes' => ['23100'],
                'translations' => [
                    'lt' => ['name' => 'Vievis', 'description' => 'Elektrėnų priemiestis'],
                    'en' => ['name' => 'Vievis', 'description' => 'Elektrėnai suburb'],
                ],
            ],
            [
                'name' => 'Nemenčinė',
                'code' => 'LT-VL-NEM',
                'latitude' => 54.85,
                'longitude' => 25.4833,
                'population' => 5000,
                'postal_codes' => ['15100'],
                'translations' => [
                    'lt' => ['name' => 'Nemenčinė', 'description' => 'Vilniaus priemiestis'],
                    'en' => ['name' => 'Nemenčinė', 'description' => 'Vilnius suburb'],
                ],
            ],
            // Additional Kaunas County cities
            [
                'name' => 'Raseiniai',
                'code' => 'LT-KA-RAS',
                'latitude' => 55.3667,
                'longitude' => 23.1167,
                'population' => 11000,
                'postal_codes' => ['60150'],
                'translations' => [
                    'lt' => ['name' => 'Raseiniai', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Raseiniai', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Prienai',
                'code' => 'LT-KA-PRI',
                'latitude' => 54.6333,
                'longitude' => 23.95,
                'population' => 9000,
                'postal_codes' => ['59100'],
                'translations' => [
                    'lt' => ['name' => 'Prienai', 'description' => 'Nemuno slėnio miestas'],
                    'en' => ['name' => 'Prienai', 'description' => 'Nemunas valley city'],
                ],
            ],
            [
                'name' => 'Birštonas',
                'code' => 'LT-KA-BIR',
                'latitude' => 54.6167,
                'longitude' => 24.0333,
                'population' => 2500,
                'postal_codes' => ['59200'],
                'translations' => [
                    'lt' => ['name' => 'Birštonas', 'description' => 'Kurortinis miestas'],
                    'en' => ['name' => 'Birštonas', 'description' => 'Resort town'],
                ],
            ],
            [
                'name' => 'Jurbarkas',
                'code' => 'LT-KA-JUR',
                'latitude' => 55.0833,
                'longitude' => 22.7667,
                'population' => 10000,
                'postal_codes' => ['74100'],
                'translations' => [
                    'lt' => ['name' => 'Jurbarkas', 'description' => 'Nemuno uostamiesčis'],
                    'en' => ['name' => 'Jurbarkas', 'description' => 'Nemunas port town'],
                ],
            ],
            [
                'name' => 'Kaišiadorys',
                'code' => 'LT-KA-KAI',
                'latitude' => 54.8667,
                'longitude' => 24.45,
                'population' => 8000,
                'postal_codes' => ['56100'],
                'translations' => [
                    'lt' => ['name' => 'Kaišiadorys', 'description' => 'Geležinkelio mazgas'],
                    'en' => ['name' => 'Kaišiadorys', 'description' => 'Railway junction'],
                ],
            ],
            // Additional Klaipėda County cities
            [
                'name' => 'Neringa',
                'code' => 'LT-KL-NER',
                'latitude' => 55.3667,
                'longitude' => 21.0667,
                'population' => 3500,
                'postal_codes' => ['93001'],
                'translations' => [
                    'lt' => ['name' => 'Neringa', 'description' => 'Kuršių nerijos miestas'],
                    'en' => ['name' => 'Neringa', 'description' => 'Curonian Spit city'],
                ],
            ],
            [
                'name' => 'Kretinga',
                'code' => 'LT-KL-KRE',
                'latitude' => 55.9,
                'longitude' => 21.2333,
                'population' => 18000,
                'postal_codes' => ['97100'],
                'translations' => [
                    'lt' => ['name' => 'Kretinga', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Kretinga', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Plungė',
                'code' => 'LT-KL-PLU',
                'latitude' => 55.9167,
                'longitude' => 21.85,
                'population' => 17000,
                'postal_codes' => ['90100'],
                'translations' => [
                    'lt' => ['name' => 'Plungė', 'description' => 'Žemaitijos miestas'],
                    'en' => ['name' => 'Plungė', 'description' => 'Žemaitija city'],
                ],
            ],
            [
                'name' => 'Skuodas',
                'code' => 'LT-KL-SKU',
                'latitude' => 56.2667,
                'longitude' => 21.5333,
                'population' => 6000,
                'postal_codes' => ['98100'],
                'translations' => [
                    'lt' => ['name' => 'Skuodas', 'description' => 'Sienos miestas'],
                    'en' => ['name' => 'Skuodas', 'description' => 'Border town'],
                ],
            ],
            // Additional Šiauliai County cities
            [
                'name' => 'Joniškis',
                'code' => 'LT-SA-JON',
                'latitude' => 56.2333,
                'longitude' => 23.6167,
                'population' => 9000,
                'postal_codes' => ['84100'],
                'translations' => [
                    'lt' => ['name' => 'Joniškis', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Joniškis', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Kelmė',
                'code' => 'LT-SA-KEL',
                'latitude' => 55.6333,
                'longitude' => 22.9333,
                'population' => 8000,
                'postal_codes' => ['86100'],
                'translations' => [
                    'lt' => ['name' => 'Kelmė', 'description' => 'Žemaitijos miestas'],
                    'en' => ['name' => 'Kelmė', 'description' => 'Žemaitija city'],
                ],
            ],
            [
                'name' => 'Pakruojis',
                'code' => 'LT-SA-PAK',
                'latitude' => 56.0667,
                'longitude' => 23.8667,
                'population' => 5000,
                'postal_codes' => ['83100'],
                'translations' => [
                    'lt' => ['name' => 'Pakruojis', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Pakruojis', 'description' => 'Small town'],
                ],
            ],
            // Additional Panevėžys County cities
            [
                'name' => 'Biržai',
                'code' => 'LT-PN-BIR',
                'latitude' => 56.2,
                'longitude' => 24.75,
                'population' => 12000,
                'postal_codes' => ['41100'],
                'translations' => [
                    'lt' => ['name' => 'Biržai', 'description' => 'Alaus miestas'],
                    'en' => ['name' => 'Biržai', 'description' => 'Beer city'],
                ],
            ],
            [
                'name' => 'Kupiškis',
                'code' => 'LT-PN-KUP',
                'latitude' => 55.8333,
                'longitude' => 24.9667,
                'population' => 6000,
                'postal_codes' => ['40100'],
                'translations' => [
                    'lt' => ['name' => 'Kupiškis', 'description' => 'Aukštaitijos miestas'],
                    'en' => ['name' => 'Kupiškis', 'description' => 'Aukštaitija city'],
                ],
            ],
            [
                'name' => 'Pasvalys',
                'code' => 'LT-PN-PAS',
                'latitude' => 56.0667,
                'longitude' => 24.4,
                'population' => 7000,
                'postal_codes' => ['39100'],
                'translations' => [
                    'lt' => ['name' => 'Pasvalys', 'description' => 'Sienos miestas'],
                    'en' => ['name' => 'Pasvalys', 'description' => 'Border town'],
                ],
            ],
            [
                'name' => 'Rokiškis',
                'code' => 'LT-PN-ROK',
                'latitude' => 55.9667,
                'longitude' => 25.5833,
                'population' => 14000,
                'postal_codes' => ['42100'],
                'translations' => [
                    'lt' => ['name' => 'Rokiškis', 'description' => 'Sūrių miestas'],
                    'en' => ['name' => 'Rokiškis', 'description' => 'Cheese city'],
                ],
            ],
            // Additional Alytus County cities
            [
                'name' => 'Druskininkai',
                'code' => 'LT-AL-DRU',
                'latitude' => 54.0167,
                'longitude' => 23.9667,
                'population' => 13000,
                'postal_codes' => ['66160'],
                'translations' => [
                    'lt' => ['name' => 'Druskininkai', 'description' => 'Kurortinis miestas'],
                    'en' => ['name' => 'Druskininkai', 'description' => 'Resort town'],
                ],
            ],
            [
                'name' => 'Lazdijai',
                'code' => 'LT-AL-LAZ',
                'latitude' => 54.2333,
                'longitude' => 23.5167,
                'population' => 4000,
                'postal_codes' => ['67001'],
                'translations' => [
                    'lt' => ['name' => 'Lazdijai', 'description' => 'Sienos miestas'],
                    'en' => ['name' => 'Lazdijai', 'description' => 'Border town'],
                ],
            ],
            [
                'name' => 'Varėna',
                'code' => 'LT-AL-VAR',
                'latitude' => 54.2167,
                'longitude' => 24.5667,
                'population' => 9000,
                'postal_codes' => ['65101'],
                'translations' => [
                    'lt' => ['name' => 'Varėna', 'description' => 'Miškų miestas'],
                    'en' => ['name' => 'Varėna', 'description' => 'Forest town'],
                ],
            ],
            // Additional Marijampolė County cities
            [
                'name' => 'Kalvarija',
                'code' => 'LT-MR-KAL',
                'latitude' => 54.4167,
                'longitude' => 23.2333,
                'population' => 4000,
                'postal_codes' => ['69001'],
                'translations' => [
                    'lt' => ['name' => 'Kalvarija', 'description' => 'Piligrimų miestas'],
                    'en' => ['name' => 'Kalvarija', 'description' => 'Pilgrim town'],
                ],
            ],
            [
                'name' => 'Kazlų Rūda',
                'code' => 'LT-MR-KAZ',
                'latitude' => 54.75,
                'longitude' => 23.4833,
                'population' => 6000,
                'postal_codes' => ['61001'],
                'translations' => [
                    'lt' => ['name' => 'Kazlų Rūda', 'description' => 'Miškų miestas'],
                    'en' => ['name' => 'Kazlų Rūda', 'description' => 'Forest town'],
                ],
            ],
            [
                'name' => 'Vilkaviškis',
                'code' => 'LT-MR-VIL',
                'latitude' => 54.65,
                'longitude' => 23.0333,
                'population' => 10000,
                'postal_codes' => ['70001'],
                'translations' => [
                    'lt' => ['name' => 'Vilkaviškis', 'description' => 'Sienos miestas'],
                    'en' => ['name' => 'Vilkaviškis', 'description' => 'Border town'],
                ],
            ],
            // Additional Tauragė County cities
            [
                'name' => 'Jurbarkas',
                'code' => 'LT-TA-JUR',
                'latitude' => 55.0833,
                'longitude' => 22.7667,
                'population' => 10000,
                'postal_codes' => ['74100'],
                'translations' => [
                    'lt' => ['name' => 'Jurbarkas', 'description' => 'Nemuno uostamiesčis'],
                    'en' => ['name' => 'Jurbarkas', 'description' => 'Nemunas port town'],
                ],
            ],
            [
                'name' => 'Pagėgiai',
                'code' => 'LT-TA-PAG',
                'latitude' => 55.1333,
                'longitude' => 21.9167,
                'population' => 2000,
                'postal_codes' => ['99001'],
                'translations' => [
                    'lt' => ['name' => 'Pagėgiai', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Pagėgiai', 'description' => 'Small town'],
                ],
            ],
            [
                'name' => 'Šilalė',
                'code' => 'LT-TA-SIL',
                'latitude' => 55.4833,
                'longitude' => 22.1833,
                'population' => 5000,
                'postal_codes' => ['75100'],
                'translations' => [
                    'lt' => ['name' => 'Šilalė', 'description' => 'Žemaitijos miestas'],
                    'en' => ['name' => 'Šilalė', 'description' => 'Žemaitija city'],
                ],
            ],
            // Additional Telšiai County cities
            [
                'name' => 'Mažeikiai',
                'code' => 'LT-TE-MAZ',
                'latitude' => 56.3167,
                'longitude' => 22.3333,
                'population' => 35000,
                'postal_codes' => ['89001'],
                'translations' => [
                    'lt' => ['name' => 'Mažeikiai', 'description' => 'Naftos pramonės centras'],
                    'en' => ['name' => 'Mažeikiai', 'description' => 'Oil industry center'],
                ],
            ],
            [
                'name' => 'Plungė',
                'code' => 'LT-TE-PLU',
                'latitude' => 55.9167,
                'longitude' => 21.85,
                'population' => 17000,
                'postal_codes' => ['90100'],
                'translations' => [
                    'lt' => ['name' => 'Plungė', 'description' => 'Žemaitijos miestas'],
                    'en' => ['name' => 'Plungė', 'description' => 'Žemaitija city'],
                ],
            ],
            [
                'name' => 'Rietavas',
                'code' => 'LT-TE-RIE',
                'latitude' => 55.7167,
                'longitude' => 21.9333,
                'population' => 3000,
                'postal_codes' => ['90300'],
                'translations' => [
                    'lt' => ['name' => 'Rietavas', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Rietavas', 'description' => 'Small town'],
                ],
            ],
            [
                'name' => 'Skuodas',
                'code' => 'LT-TE-SKU',
                'latitude' => 56.2667,
                'longitude' => 21.5333,
                'population' => 6000,
                'postal_codes' => ['98100'],
                'translations' => [
                    'lt' => ['name' => 'Skuodas', 'description' => 'Sienos miestas'],
                    'en' => ['name' => 'Skuodas', 'description' => 'Border town'],
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
                    'country_id' => $lithuania->id,
                    'zone_id' => $ltZone?->id ?? $euZone?->id,
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
