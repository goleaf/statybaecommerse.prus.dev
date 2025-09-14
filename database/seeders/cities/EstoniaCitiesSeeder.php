<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class EstoniaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $estonia = Country::where('cca2', 'EE')->first();
        $euZone = Zone::where('code', 'EU')->first();

        // Regions are no longer used in the database schema

        $cities = [
            // Harju County (Tallinn region)
            [
                'name' => 'Tallinn',
                'code' => 'EE-37-TAL',
                'is_capital' => true,
                'is_default' => true,
                'latitude' => 59.437,
                'longitude' => 24.7536,
                'population' => 437619,
                'postal_codes' => ['10111', '10112', '10113'],
                'translations' => [
                    'lt' => ['name' => 'Talinas', 'description' => 'Estijos sostinė'],
                    'en' => ['name' => 'Tallinn', 'description' => 'Capital of Estonia'],
                ],
            ],
            [
                'name' => 'Keila',
                'code' => 'EE-37-KEI',
                'latitude' => 59.3036,
                'longitude' => 24.4131,
                'population' => 10000,
                'postal_codes' => ['76601'],
                'translations' => [
                    'lt' => ['name' => 'Keila', 'description' => 'Mažas miestas Harju apskrityje'],
                    'en' => ['name' => 'Keila', 'description' => 'Small town in Harju County'],
                ],
            ],
            [
                'name' => 'Maardu',
                'code' => 'EE-37-MAA',
                'latitude' => 59.4764,
                'longitude' => 25.025,
                'population' => 17000,
                'postal_codes' => ['74111'],
                'translations' => [
                    'lt' => ['name' => 'Maardu', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Maardu', 'description' => 'Industrial city'],
                ],
            ],
            // Tartu County
            [
                'name' => 'Tartu',
                'code' => 'EE-78-TAR',
                'latitude' => 58.378,
                'longitude' => 26.729,
                'population' => 91407,
                'postal_codes' => ['50050', '50090'],
                'translations' => [
                    'lt' => ['name' => 'Tartu', 'description' => 'Estijos universiteto miestas'],
                    'en' => ['name' => 'Tartu', 'description' => 'University city of Estonia'],
                ],
            ],
            [
                'name' => 'Elva',
                'code' => 'EE-78-ELV',
                'latitude' => 58.2225,
                'longitude' => 26.4211,
                'population' => 5500,
                'postal_codes' => ['61501'],
                'translations' => [
                    'lt' => ['name' => 'Elva', 'description' => 'Kurortinis miestas'],
                    'en' => ['name' => 'Elva', 'description' => 'Resort town'],
                ],
            ],
            // Ida-Viru County
            [
                'name' => 'Narva',
                'code' => 'EE-44-NAR',
                'latitude' => 59.3753,
                'longitude' => 28.1903,
                'population' => 54024,
                'postal_codes' => ['20001'],
                'translations' => [
                    'lt' => ['name' => 'Narva', 'description' => 'Rusijos sienos miestas'],
                    'en' => ['name' => 'Narva', 'description' => 'City on Russian border'],
                ],
            ],
            [
                'name' => 'Kohtla-Järve',
                'code' => 'EE-44-KOH',
                'latitude' => 59.3986,
                'longitude' => 27.2731,
                'population' => 35000,
                'postal_codes' => ['30301'],
                'translations' => [
                    'lt' => ['name' => 'Kohtla-Järve', 'description' => 'Alyvos pramonės centras'],
                    'en' => ['name' => 'Kohtla-Järve', 'description' => 'Oil shale industry center'],
                ],
            ],
            [
                'name' => 'Sillamäe',
                'code' => 'EE-44-SIL',
                'latitude' => 59.3908,
                'longitude' => 27.7744,
                'population' => 12000,
                'postal_codes' => ['40231'],
                'translations' => [
                    'lt' => ['name' => 'Sillamäe', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'Sillamäe', 'description' => 'Seaside city'],
                ],
            ],
            // Pärnu County
            [
                'name' => 'Pärnu',
                'code' => 'EE-67-PAR',
                'latitude' => 58.3859,
                'longitude' => 24.4971,
                'population' => 39179,
                'postal_codes' => ['80010'],
                'translations' => [
                    'lt' => ['name' => 'Pärnu', 'description' => 'Estijos vasaros sostinė'],
                    'en' => ['name' => 'Pärnu', 'description' => 'Summer capital of Estonia'],
                ],
            ],
            // Lääne-Viru County
            [
                'name' => 'Rakvere',
                'code' => 'EE-59-RAK',
                'latitude' => 59.3464,
                'longitude' => 26.3558,
                'population' => 15000,
                'postal_codes' => ['44306'],
                'translations' => [
                    'lt' => ['name' => 'Rakvere', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Rakvere', 'description' => 'Historic city'],
                ],
            ],
            // Valga County
            [
                'name' => 'Valga',
                'code' => 'EE-82-VAL',
                'latitude' => 57.7778,
                'longitude' => 26.0472,
                'population' => 12000,
                'postal_codes' => ['68203'],
                'translations' => [
                    'lt' => ['name' => 'Valga', 'description' => 'Sienos miestas su Latvija'],
                    'en' => ['name' => 'Valga', 'description' => 'Border city with Latvia'],
                ],
            ],
            // Viljandi County
            [
                'name' => 'Viljandi',
                'code' => 'EE-84-VIL',
                'latitude' => 58.3639,
                'longitude' => 25.59,
                'population' => 17000,
                'postal_codes' => ['71020'],
                'translations' => [
                    'lt' => ['name' => 'Viljandi', 'description' => 'Kultūros miestas'],
                    'en' => ['name' => 'Viljandi', 'description' => 'Cultural city'],
                ],
            ],
            // Võru County
            [
                'name' => 'Võru',
                'code' => 'EE-86-VOR',
                'latitude' => 57.8333,
                'longitude' => 27.0167,
                'population' => 12000,
                'postal_codes' => ['65601'],
                'translations' => [
                    'lt' => ['name' => 'Võru', 'description' => 'Pietų Estijos centras'],
                    'en' => ['name' => 'Võru', 'description' => 'Center of Southern Estonia'],
                ],
            ],
            // Jõgeva County
            [
                'name' => 'Jõgeva',
                'code' => 'EE-49-JOG',
                'latitude' => 58.7469,
                'longitude' => 26.3939,
                'population' => 5000,
                'postal_codes' => ['48301'],
                'translations' => [
                    'lt' => ['name' => 'Jõgeva', 'description' => 'Žemės ūkio centras'],
                    'en' => ['name' => 'Jõgeva', 'description' => 'Agricultural center'],
                ],
            ],
            // Järva County
            [
                'name' => 'Paide',
                'code' => 'EE-51-PAI',
                'latitude' => 58.8856,
                'longitude' => 25.5572,
                'population' => 8000,
                'postal_codes' => ['72711'],
                'translations' => [
                    'lt' => ['name' => 'Paide', 'description' => 'Järva apskrities centras'],
                    'en' => ['name' => 'Paide', 'description' => 'Center of Järva County'],
                ],
            ],
            // Lääne County
            [
                'name' => 'Haapsalu',
                'code' => 'EE-57-HAA',
                'latitude' => 58.9431,
                'longitude' => 23.5414,
                'population' => 10000,
                'postal_codes' => ['90501'],
                'translations' => [
                    'lt' => ['name' => 'Haapsalu', 'description' => 'Kurortinis miestas'],
                    'en' => ['name' => 'Haapsalu', 'description' => 'Resort town'],
                ],
            ],
            // Põlva County
            [
                'name' => 'Põlva',
                'code' => 'EE-65-POL',
                'latitude' => 58.0531,
                'longitude' => 27.0519,
                'population' => 5000,
                'postal_codes' => ['63308'],
                'translations' => [
                    'lt' => ['name' => 'Põlva', 'description' => 'Põlva apskrities centras'],
                    'en' => ['name' => 'Põlva', 'description' => 'Center of Põlva County'],
                ],
            ],
            // Rapla County
            [
                'name' => 'Rapla',
                'code' => 'EE-70-RAP',
                'latitude' => 59.0072,
                'longitude' => 24.7928,
                'population' => 5000,
                'postal_codes' => ['79511'],
                'translations' => [
                    'lt' => ['name' => 'Rapla', 'description' => 'Rapla apskrities centras'],
                    'en' => ['name' => 'Rapla', 'description' => 'Center of Rapla County'],
                ],
            ],
            // Saare County
            [
                'name' => 'Kuressaare',
                'code' => 'EE-74-KUR',
                'latitude' => 58.2528,
                'longitude' => 22.4853,
                'population' => 13000,
                'postal_codes' => ['93813'],
                'translations' => [
                    'lt' => ['name' => 'Kuressaare', 'description' => 'Saare salos centras'],
                    'en' => ['name' => 'Kuressaare', 'description' => 'Center of Saare Island'],
                ],
            ],
            // Hiiu County
            [
                'name' => 'Kärdla',
                'code' => 'EE-39-KAR',
                'latitude' => 58.9978,
                'longitude' => 22.7492,
                'population' => 3000,
                'postal_codes' => ['92401'],
                'translations' => [
                    'lt' => ['name' => 'Kärdla', 'description' => 'Hiiu salos centras'],
                    'en' => ['name' => 'Kärdla', 'description' => 'Center of Hiiu Island'],
                ],
            ],
            // Additional Harju County cities
            [
                'name' => 'Saue',
                'code' => 'EE-37-SAU',
                'latitude' => 59.3167,
                'longitude' => 24.5667,
                'population' => 6000,
                'postal_codes' => ['76501'],
                'translations' => [
                    'lt' => ['name' => 'Saue', 'description' => 'Talino priemiestis'],
                    'en' => ['name' => 'Saue', 'description' => 'Tallinn suburb'],
                ],
            ],
            [
                'name' => 'Kiili',
                'code' => 'EE-37-KII',
                'latitude' => 59.3167,
                'longitude' => 24.8333,
                'population' => 2000,
                'postal_codes' => ['75301'],
                'translations' => [
                    'lt' => ['name' => 'Kiili', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Kiili', 'description' => 'Small town'],
                ],
            ],
            [
                'name' => 'Rae',
                'code' => 'EE-37-RAE',
                'latitude' => 59.3833,
                'longitude' => 24.9333,
                'population' => 2000,
                'postal_codes' => ['75312'],
                'translations' => [
                    'lt' => ['name' => 'Rae', 'description' => 'Talino priemiestis'],
                    'en' => ['name' => 'Rae', 'description' => 'Tallinn suburb'],
                ],
            ],
            [
                'name' => 'Viimsi',
                'code' => 'EE-37-VII',
                'latitude' => 59.5000,
                'longitude' => 24.8333,
                'population' => 20000,
                'postal_codes' => ['74001'],
                'translations' => [
                    'lt' => ['name' => 'Viimsi', 'description' => 'Pajūrio priemiestis'],
                    'en' => ['name' => 'Viimsi', 'description' => 'Seaside suburb'],
                ],
            ],
            [
                'name' => 'Harku',
                'code' => 'EE-37-HAR',
                'latitude' => 59.4167,
                'longitude' => 24.5833,
                'population' => 15000,
                'postal_codes' => ['76901'],
                'translations' => [
                    'lt' => ['name' => 'Harku', 'description' => 'Talino priemiestis'],
                    'en' => ['name' => 'Harku', 'description' => 'Tallinn suburb'],
                ],
            ],
            // Additional Tartu County cities
            [
                'name' => 'Kallaste',
                'code' => 'EE-78-KAL',
                'latitude' => 58.6667,
                'longitude' => 27.1667,
                'population' => 900,
                'postal_codes' => ['60101'],
                'translations' => [
                    'lt' => ['name' => 'Kallaste', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'Kallaste', 'description' => 'Seaside town'],
                ],
            ],
            [
                'name' => 'Mustvee',
                'code' => 'EE-78-MUS',
                'latitude' => 58.8333,
                'longitude' => 26.9333,
                'population' => 1300,
                'postal_codes' => ['49601'],
                'translations' => [
                    'lt' => ['name' => 'Mustvee', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'Mustvee', 'description' => 'Seaside town'],
                ],
            ],
            [
                'name' => 'Otepää',
                'code' => 'EE-78-OTE',
                'latitude' => 58.0500,
                'longitude' => 26.5000,
                'population' => 2000,
                'postal_codes' => ['67406'],
                'translations' => [
                    'lt' => ['name' => 'Otepää', 'description' => 'Žiemos sporto miestas'],
                    'en' => ['name' => 'Otepää', 'description' => 'Winter sports town'],
                ],
            ],
            [
                'name' => 'Põltsamaa',
                'code' => 'EE-78-POL',
                'latitude' => 58.6500,
                'longitude' => 25.9667,
                'population' => 4000,
                'postal_codes' => ['48003'],
                'translations' => [
                    'lt' => ['name' => 'Põltsamaa', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Põltsamaa', 'description' => 'Historic town'],
                ],
            ],
            [
                'name' => 'Põlva',
                'code' => 'EE-78-POV',
                'latitude' => 58.0531,
                'longitude' => 27.0519,
                'population' => 5000,
                'postal_codes' => ['63308'],
                'translations' => [
                    'lt' => ['name' => 'Põlva', 'description' => 'Põlva apskrities centras'],
                    'en' => ['name' => 'Põlva', 'description' => 'Center of Põlva County'],
                ],
            ],
            // Additional Ida-Viru County cities
            [
                'name' => 'Jõhvi',
                'code' => 'EE-44-JOH',
                'latitude' => 59.3592,
                'longitude' => 27.4211,
                'population' => 12000,
                'postal_codes' => ['41532'],
                'translations' => [
                    'lt' => ['name' => 'Jõhvi', 'description' => 'Ida-Viru apskrities centras'],
                    'en' => ['name' => 'Jõhvi', 'description' => 'Center of Ida-Viru County'],
                ],
            ],
            [
                'name' => 'Kiviõli',
                'code' => 'EE-44-KIV',
                'latitude' => 59.3528,
                'longitude' => 26.9708,
                'population' => 5000,
                'postal_codes' => ['43101'],
                'translations' => [
                    'lt' => ['name' => 'Kiviõli', 'description' => 'Alyvos pramonės miestas'],
                    'en' => ['name' => 'Kiviõli', 'description' => 'Oil shale industry town'],
                ],
            ],
            [
                'name' => 'Püssi',
                'code' => 'EE-44-PUS',
                'latitude' => 59.3600,
                'longitude' => 27.0500,
                'population' => 1000,
                'postal_codes' => ['43201'],
                'translations' => [
                    'lt' => ['name' => 'Püssi', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Püssi', 'description' => 'Small town'],
                ],
            ],
            [
                'name' => 'Toila',
                'code' => 'EE-44-TOI',
                'latitude' => 59.4167,
                'longitude' => 27.5000,
                'population' => 800,
                'postal_codes' => ['41701'],
                'translations' => [
                    'lt' => ['name' => 'Toila', 'description' => 'Kurortinis miestas'],
                    'en' => ['name' => 'Toila', 'description' => 'Resort town'],
                ],
            ],
            // Additional Pärnu County cities
            [
                'name' => 'Haapsalu',
                'code' => 'EE-67-HAA',
                'latitude' => 58.9431,
                'longitude' => 23.5414,
                'population' => 10000,
                'postal_codes' => ['90501'],
                'translations' => [
                    'lt' => ['name' => 'Haapsalu', 'description' => 'Kurortinis miestas'],
                    'en' => ['name' => 'Haapsalu', 'description' => 'Resort town'],
                ],
            ],
            [
                'name' => 'Kihnu',
                'code' => 'EE-67-KIH',
                'latitude' => 58.1333,
                'longitude' => 24.0000,
                'population' => 500,
                'postal_codes' => ['88001'],
                'translations' => [
                    'lt' => ['name' => 'Kihnu', 'description' => 'Salos miestas'],
                    'en' => ['name' => 'Kihnu', 'description' => 'Island town'],
                ],
            ],
            [
                'name' => 'Lihula',
                'code' => 'EE-67-LIH',
                'latitude' => 58.6833,
                'longitude' => 23.8333,
                'population' => 1500,
                'postal_codes' => ['90301'],
                'translations' => [
                    'lt' => ['name' => 'Lihula', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Lihula', 'description' => 'Historic town'],
                ],
            ],
            [
                'name' => 'Tori',
                'code' => 'EE-67-TOR',
                'latitude' => 58.4833,
                'longitude' => 24.8167,
                'population' => 1000,
                'postal_codes' => ['86801'],
                'translations' => [
                    'lt' => ['name' => 'Tori', 'description' => 'Arklų veisimo centras'],
                    'en' => ['name' => 'Tori', 'description' => 'Horse breeding center'],
                ],
            ],
            // Additional Lääne-Viru County cities
            [
                'name' => 'Tapa',
                'code' => 'EE-59-TAP',
                'latitude' => 59.2667,
                'longitude' => 25.9500,
                'population' => 5000,
                'postal_codes' => ['45101'],
                'translations' => [
                    'lt' => ['name' => 'Tapa', 'description' => 'Geležinkelio mazgas'],
                    'en' => ['name' => 'Tapa', 'description' => 'Railway junction'],
                ],
            ],
            [
                'name' => 'Kunda',
                'code' => 'EE-59-KUN',
                'latitude' => 59.5167,
                'longitude' => 26.5333,
                'population' => 3000,
                'postal_codes' => ['44101'],
                'translations' => [
                    'lt' => ['name' => 'Kunda', 'description' => 'Cemento pramonės centras'],
                    'en' => ['name' => 'Kunda', 'description' => 'Cement industry center'],
                ],
            ],
            [
                'name' => 'Väike-Maarja',
                'code' => 'EE-59-VAI',
                'latitude' => 59.1333,
                'longitude' => 26.2500,
                'population' => 2000,
                'postal_codes' => ['46201'],
                'translations' => [
                    'lt' => ['name' => 'Väike-Maarja', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Väike-Maarja', 'description' => 'Small town'],
                ],
            ],
            // Additional Valga County cities
            [
                'name' => 'Tõrva',
                'code' => 'EE-82-TOR',
                'latitude' => 58.0000,
                'longitude' => 25.9167,
                'population' => 2000,
                'postal_codes' => ['68601'],
                'translations' => [
                    'lt' => ['name' => 'Tõrva', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Tõrva', 'description' => 'Small town'],
                ],
            ],
            [
                'name' => 'Otepää',
                'code' => 'EE-82-OTE',
                'latitude' => 58.0500,
                'longitude' => 26.5000,
                'population' => 2000,
                'postal_codes' => ['67406'],
                'translations' => [
                    'lt' => ['name' => 'Otepää', 'description' => 'Žiemos sporto miestas'],
                    'en' => ['name' => 'Otepää', 'description' => 'Winter sports town'],
                ],
            ],
            // Additional Viljandi County cities
            [
                'name' => 'Suure-Jaani',
                'code' => 'EE-84-SUU',
                'latitude' => 58.5333,
                'longitude' => 25.4667,
                'population' => 1000,
                'postal_codes' => ['71501'],
                'translations' => [
                    'lt' => ['name' => 'Suure-Jaani', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Suure-Jaani', 'description' => 'Small town'],
                ],
            ],
            [
                'name' => 'Võhma',
                'code' => 'EE-84-VOH',
                'latitude' => 58.6333,
                'longitude' => 25.5500,
                'population' => 1300,
                'postal_codes' => ['79601'],
                'translations' => [
                    'lt' => ['name' => 'Võhma', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Võhma', 'description' => 'Small town'],
                ],
            ],
            [
                'name' => 'Mõisaküla',
                'code' => 'EE-84-MOI',
                'latitude' => 58.0833,
                'longitude' => 25.1833,
                'population' => 800,
                'postal_codes' => ['69301'],
                'translations' => [
                    'lt' => ['name' => 'Mõisaküla', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Mõisaküla', 'description' => 'Small town'],
                ],
            ],
            // Additional Võru County cities
            [
                'name' => 'Antsla',
                'code' => 'EE-86-ANT',
                'latitude' => 57.8333,
                'longitude' => 26.5333,
                'population' => 1400,
                'postal_codes' => ['66401'],
                'translations' => [
                    'lt' => ['name' => 'Antsla', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Antsla', 'description' => 'Small town'],
                ],
            ],
            [
                'name' => 'Rõuge',
                'code' => 'EE-86-ROU',
                'latitude' => 57.7333,
                'longitude' => 26.9167,
                'population' => 500,
                'postal_codes' => ['67301'],
                'translations' => [
                    'lt' => ['name' => 'Rõuge', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Rõuge', 'description' => 'Small town'],
                ],
            ],
            [
                'name' => 'Setomaa',
                'code' => 'EE-86-SET',
                'latitude' => 57.9333,
                'longitude' => 27.4667,
                'population' => 300,
                'postal_codes' => ['65501'],
                'translations' => [
                    'lt' => ['name' => 'Setomaa', 'description' => 'Setų kultūros centras'],
                    'en' => ['name' => 'Setomaa', 'description' => 'Seto culture center'],
                ],
            ],
            // Additional Jõgeva County cities
            [
                'name' => 'Mustvee',
                'code' => 'EE-49-MUS',
                'latitude' => 58.8333,
                'longitude' => 26.9333,
                'population' => 1300,
                'postal_codes' => ['49601'],
                'translations' => [
                    'lt' => ['name' => 'Mustvee', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'Mustvee', 'description' => 'Seaside town'],
                ],
            ],
            [
                'name' => 'Põltsamaa',
                'code' => 'EE-49-POL',
                'latitude' => 58.6500,
                'longitude' => 25.9667,
                'population' => 4000,
                'postal_codes' => ['48003'],
                'translations' => [
                    'lt' => ['name' => 'Põltsamaa', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Põltsamaa', 'description' => 'Historic town'],
                ],
            ],
            // Additional Järva County cities
            [
                'name' => 'Türi',
                'code' => 'EE-51-TUR',
                'latitude' => 58.8167,
                'longitude' => 25.4333,
                'population' => 5000,
                'postal_codes' => ['72201'],
                'translations' => [
                    'lt' => ['name' => 'Türi', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Türi', 'description' => 'Industrial town'],
                ],
            ],
            [
                'name' => 'Aravete',
                'code' => 'EE-51-ARA',
                'latitude' => 59.1333,
                'longitude' => 25.7667,
                'population' => 1000,
                'postal_codes' => ['73301'],
                'translations' => [
                    'lt' => ['name' => 'Aravete', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Aravete', 'description' => 'Small town'],
                ],
            ],
            // Additional Lääne County cities
            [
                'name' => 'Lihula',
                'code' => 'EE-57-LIH',
                'latitude' => 58.6833,
                'longitude' => 23.8333,
                'population' => 1500,
                'postal_codes' => ['90301'],
                'translations' => [
                    'lt' => ['name' => 'Lihula', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Lihula', 'description' => 'Historic town'],
                ],
            ],
            [
                'name' => 'Vormsi',
                'code' => 'EE-57-VOR',
                'latitude' => 59.0000,
                'longitude' => 23.2500,
                'population' => 400,
                'postal_codes' => ['91301'],
                'translations' => [
                    'lt' => ['name' => 'Vormsi', 'description' => 'Salos miestas'],
                    'en' => ['name' => 'Vormsi', 'description' => 'Island town'],
                ],
            ],
            // Additional Põlva County cities
            [
                'name' => 'Kanepi',
                'code' => 'EE-65-KAN',
                'latitude' => 58.0000,
                'longitude' => 26.7500,
                'population' => 500,
                'postal_codes' => ['63201'],
                'translations' => [
                    'lt' => ['name' => 'Kanepi', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Kanepi', 'description' => 'Small town'],
                ],
            ],
            [
                'name' => 'Räpina',
                'code' => 'EE-65-RAP',
                'latitude' => 58.1000,
                'longitude' => 27.4667,
                'population' => 2000,
                'postal_codes' => ['64501'],
                'translations' => [
                    'lt' => ['name' => 'Räpina', 'description' => 'Popieriaus pramonės centras'],
                    'en' => ['name' => 'Räpina', 'description' => 'Paper industry center'],
                ],
            ],
            // Additional Rapla County cities
            [
                'name' => 'Kehtna',
                'code' => 'EE-70-KEH',
                'latitude' => 58.9333,
                'longitude' => 24.8833,
                'population' => 1000,
                'postal_codes' => ['79001'],
                'translations' => [
                    'lt' => ['name' => 'Kehtna', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Kehtna', 'description' => 'Small town'],
                ],
            ],
            [
                'name' => 'Kohila',
                'code' => 'EE-70-KOH',
                'latitude' => 59.1667,
                'longitude' => 24.7500,
                'population' => 3000,
                'postal_codes' => ['79701'],
                'translations' => [
                    'lt' => ['name' => 'Kohila', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Kohila', 'description' => 'Small town'],
                ],
            ],
            [
                'name' => 'Märjamaa',
                'code' => 'EE-70-MAR',
                'latitude' => 58.9000,
                'longitude' => 24.4333,
                'population' => 1000,
                'postal_codes' => ['78301'],
                'translations' => [
                    'lt' => ['name' => 'Märjamaa', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Märjamaa', 'description' => 'Small town'],
                ],
            ],
            // Additional Saare County cities
            [
                'name' => 'Valjala',
                'code' => 'EE-74-VAL',
                'latitude' => 58.4167,
                'longitude' => 22.7833,
                'population' => 400,
                'postal_codes' => ['94301'],
                'translations' => [
                    'lt' => ['name' => 'Valjala', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Valjala', 'description' => 'Historic town'],
                ],
            ],
            [
                'name' => 'Orissaare',
                'code' => 'EE-74-ORI',
                'latitude' => 58.5667,
                'longitude' => 23.0833,
                'population' => 800,
                'postal_codes' => ['94601'],
                'translations' => [
                    'lt' => ['name' => 'Orissaare', 'description' => 'Mažasis miestas'],
                    'en' => ['name' => 'Orissaare', 'description' => 'Small town'],
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
                    'country_id' => $estonia->id,
                    'zone_id' => $euZone?->id,
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
