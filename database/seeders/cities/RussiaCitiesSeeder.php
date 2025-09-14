<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class RussiaCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $russia = Country::where('cca2', 'RU')->first();
        $ruZone = Zone::where('code', 'RU')->first();

        // Regions are no longer used in the database schema
        
        $cities = [
            // Moscow
            [
                'name' => 'Moscow',
                'code' => 'RU-MOW-MOS',
                'is_capital' => true,
                'is_default' => true,
                'latitude' => 55.7558,
                'longitude' => 37.6176,
                'population' => 12615079,
                'postal_codes' => ['101000', '101001', '101002'],
                'translations' => [
                    'lt' => ['name' => 'Maskva', 'description' => 'Rusijos sostinė'],
                    'en' => ['name' => 'Moscow', 'description' => 'Capital of Russia']
            ],
            ],
            // Saint Petersburg
            [
                'name' => 'Saint Petersburg',
                'code' => 'RU-SPE-SPB',
                'latitude' => 59.9311,
                'longitude' => 30.3609,
                'population' => 5383890,
                'postal_codes' => ['190000', '190001', '190002'],
                'translations' => [
                    'lt' => ['name' => 'Sankt Peterburgas', 'description' => 'Kultūros sostinė'],
                    'en' => ['name' => 'Saint Petersburg', 'description' => 'Cultural capital']
            ],
            ],
            // Novosibirsk
            [
                'name' => 'Novosibirsk',
                'code' => 'RU-NVS-NOV',
                'latitude' => 55.0084,
                'longitude' => 82.9357,
                'population' => 1625631,
                'postal_codes' => ['630000'],
                'translations' => [
                    'lt' => ['name' => 'Novosibirskas', 'description' => 'Sibiro sostinė'],
                    'en' => ['name' => 'Novosibirsk', 'description' => 'Capital of Siberia']
            ],
            ],
            // Yekaterinburg
            [
                'name' => 'Yekaterinburg',
                'code' => 'RU-SVE-YEK',
                'latitude' => 56.8431,
                'longitude' => 60.6454,
                'population' => 1493749,
                'postal_codes' => ['620000'],
                'translations' => [
                    'lt' => ['name' => 'Jekaterinburgas', 'description' => 'Uralo sostinė'],
                    'en' => ['name' => 'Yekaterinburg', 'description' => 'Capital of Urals']
            ],
            ],
            // Kazan
            [
                'name' => 'Kazan',
                'code' => 'RU-TA-KAZ',
                'latitude' => 55.8304,
                'longitude' => 49.0661,
                'population' => 1257391,
                'postal_codes' => ['420000'],
                'translations' => [
                    'lt' => ['name' => 'Kazanė', 'description' => 'Tatarstano sostinė'],
                    'en' => ['name' => 'Kazan', 'description' => 'Capital of Tatarstan']
            ],
            ],
            // Nizhny Novgorod
            [
                'name' => 'Nizhny Novgorod',
                'code' => 'RU-NIZ-NIZ',
                'latitude' => 56.3269,
                'longitude' => 44.0075,
                'population' => 1250619,
                'postal_codes' => ['603000'],
                'translations' => [
                    'lt' => ['name' => 'Nižnij Novgorodas', 'description' => 'Automobilių pramonės centras'],
                    'en' => ['name' => 'Nizhny Novgorod', 'description' => 'Automotive industry center']
            ],
            ],
            // Chelyabinsk
            [
                'name' => 'Chelyabinsk',
                'code' => 'RU-CHE-CHE',
                'latitude' => 55.1644,
                'longitude' => 61.4368,
                'population' => 1202371,
                'postal_codes' => ['454000'],
                'translations' => [
                    'lt' => ['name' => 'Čeliabinskas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Chelyabinsk', 'description' => 'Industrial city']
            ],
            ],
            // Omsk
            [
                'name' => 'Omsk',
                'code' => 'RU-OMS-OMS',
                'latitude' => 54.9885,
                'longitude' => 73.3242,
                'population' => 1178391,
                'postal_codes' => ['644000'],
                'translations' => [
                    'lt' => ['name' => 'Omskas', 'description' => 'Sibiro miestas'],
                    'en' => ['name' => 'Omsk', 'description' => 'Siberian city']
            ],
            ],
            // Samara
            [
                'name' => 'Samara',
                'code' => 'RU-SAM-SAM',
                'latitude' => 53.2001,
                'longitude' => 50.15,
                'population' => 1164685,
                'postal_codes' => ['443000'],
                'translations' => [
                    'lt' => ['name' => 'Samara', 'description' => 'Volgos miestas'],
                    'en' => ['name' => 'Samara', 'description' => 'Volga city']
            ],
            ],
            // Rostov-on-Don
            [
                'name' => 'Rostov-on-Don',
                'code' => 'RU-ROS-ROS',
                'latitude' => 47.2357,
                'longitude' => 39.7015,
                'population' => 1125299,
                'postal_codes' => ['344000'],
                'translations' => [
                    'lt' => ['name' => 'Rostovas prie Dono', 'description' => 'Pietų Rusijos centras'],
                    'en' => ['name' => 'Rostov-on-Don', 'description' => 'Center of Southern Russia']
            ],
            ],
            // Additional major cities
            [
                'name' => 'Ufa',
                'code' => 'RU-BA-UFA',
                'region_id' => null,
                'latitude' => 54.7388,
                'longitude' => 55.9721,
                'population' => 1125699,
                'postal_codes' => ['450000'],
                'translations' => [
                    'lt' => ['name' => 'Ufa', 'description' => 'Baškirijos sostinė'],
                    'en' => ['name' => 'Ufa', 'description' => 'Capital of Bashkortostan']
            ],
            ],
            [
                'name' => 'Krasnoyarsk',
                'code' => 'RU-KYA-KRA',
                'region_id' => null,
                'latitude' => 56.0184,
                'longitude' => 92.8672,
                'population' => 1091551,
                'postal_codes' => ['660000'],
                'translations' => [
                    'lt' => ['name' => 'Krasnojarskas', 'description' => 'Sibiro miestas'],
                    'en' => ['name' => 'Krasnoyarsk', 'description' => 'Siberian city']
            ],
            ],
            [
                'name' => 'Perm',
                'code' => 'RU-PER-PER',
                'region_id' => null,
                'latitude' => 58.0105,
                'longitude' => 56.2502,
                'population' => 1053738,
                'postal_codes' => ['614000'],
                'translations' => [
                    'lt' => ['name' => 'Permė', 'description' => 'Uralo miestas'],
                    'en' => ['name' => 'Perm', 'description' => 'Ural city']
            ],
            ],
            [
                'name' => 'Voronezh',
                'code' => 'RU-VOR-VOR',
                'region_id' => null,
                'latitude' => 51.672,
                'longitude' => 39.1843,
                'population' => 1057681,
                'postal_codes' => ['394000'],
                'translations' => [
                    'lt' => ['name' => 'Voronežas', 'description' => 'Centrinės Rusijos miestas'],
                    'en' => ['name' => 'Voronezh', 'description' => 'Central Russian city']
            ],
            ],
            [
                'name' => 'Volgograd',
                'code' => 'RU-VGG-VOL',
                'region_id' => null,
                'latitude' => 48.708,
                'longitude' => 44.5133,
                'population' => 1015586,
                'postal_codes' => ['400000'],
                'translations' => [
                    'lt' => ['name' => 'Volgogradas', 'description' => 'Stalingrado miestas'],
                    'en' => ['name' => 'Volgograd', 'description' => 'Stalingrad city']
            ],
            ],
            [
                'name' => 'Krasnodar',
                'code' => 'RU-KDA-KRA',
                'region_id' => null,
                'latitude' => 45.0448,
                'longitude' => 38.976,
                'population' => 932629,
                'postal_codes' => ['350000'],
                'translations' => [
                    'lt' => ['name' => 'Krasnodaras', 'description' => 'Kubanės sostinė'],
                    'en' => ['name' => 'Krasnodar', 'description' => 'Capital of Kuban']
            ],
            ],
            [
                'name' => 'Saratov',
                'code' => 'RU-SAR-SAR',
                'region_id' => null,
                'latitude' => 51.5406,
                'longitude' => 46.0086,
                'population' => 838042,
                'postal_codes' => ['410000'],
                'translations' => [
                    'lt' => ['name' => 'Saratovas', 'description' => 'Volgos miestas'],
                    'en' => ['name' => 'Saratov', 'description' => 'Volga city']
            ],
            ],
            [
                'name' => 'Tyumen',
                'code' => 'RU-TYU-TYU',
                'region_id' => null,
                'latitude' => 57.1522,
                'longitude' => 65.5272,
                'population' => 807271,
                'postal_codes' => ['625000'],
                'translations' => [
                    'lt' => ['name' => 'Tiumenė', 'description' => 'Naftos pramonės centras'],
                    'en' => ['name' => 'Tyumen', 'description' => 'Oil industry center']
            ],
            ],
            [
                'name' => 'Tolyatti',
                'code' => 'RU-SAM-TOL',
                'latitude' => 53.5303,
                'longitude' => 49.3461,
                'population' => 707408,
                'postal_codes' => ['445000'],
                'translations' => [
                    'lt' => ['name' => 'Toljatis', 'description' => 'Automobilių pramonės centras'],
                    'en' => ['name' => 'Tolyatti', 'description' => 'Automotive industry center']
            ],
            ],
            // Additional major cities
            [
                'name' => 'Izhevsk',
                'code' => 'RU-UD-IZH',
                'region_id' => null,
                'latitude' => 56.8431,
                'longitude' => 53.2114,
                'population' => 646277,
                'postal_codes' => ['426000'],
                'translations' => [
                    'lt' => ['name' => 'Iževskas', 'description' => 'Udmurtijos sostinė'],
                    'en' => ['name' => 'Izhevsk', 'description' => 'Capital of Udmurtia']
            ],
            ],
            [
                'name' => 'Barnaul',
                'code' => 'RU-ALT-BAR',
                'region_id' => null,
                'latitude' => 53.3606,
                'longitude' => 83.7636,
                'population' => 632372,
                'postal_codes' => ['656000'],
                'translations' => [
                    'lt' => ['name' => 'Barnaule', 'description' => 'Altajaus krašto sostinė'],
                    'en' => ['name' => 'Barnaul', 'description' => 'Capital of Altai Krai']
            ],
            ],
            [
                'name' => 'Ulyanovsk',
                'code' => 'RU-ULY-ULY',
                'region_id' => null,
                'latitude' => 54.3142,
                'longitude' => 48.4032,
                'population' => 624518,
                'postal_codes' => ['432000'],
                'translations' => [
                    'lt' => ['name' => 'Uljanovskas', 'description' => 'Lenino gimimo vieta'],
                    'en' => ['name' => 'Ulyanovsk', 'description' => 'Birthplace of Lenin']
            ],
            ],
            [
                'name' => 'Vladivostok',
                'code' => 'RU-PRI-VLA',
                'region_id' => null,
                'latitude' => 43.1056,
                'longitude' => 131.8735,
                'population' => 606653,
                'postal_codes' => ['690000'],
                'translations' => [
                    'lt' => ['name' => 'Vladivostokas', 'description' => 'Rytų Rusijos sostinė'],
                    'en' => ['name' => 'Vladivostok', 'description' => 'Capital of Eastern Russia']
            ],
            ],
            [
                'name' => 'Yaroslavl',
                'code' => 'RU-YAR-YAR',
                'region_id' => null,
                'latitude' => 57.6261,
                'longitude' => 39.8845,
                'population' => 608079,
                'postal_codes' => ['150000'],
                'translations' => [
                    'lt' => ['name' => 'Jaroslavlis', 'description' => 'Aukso žiedo miestas'],
                    'en' => ['name' => 'Yaroslavl', 'description' => 'Golden Ring city']
            ],
            ],
            [
                'name' => 'Makhachkala',
                'code' => 'RU-DA-MAK',
                'region_id' => null,
                'latitude' => 42.9836,
                'longitude' => 47.5044,
                'population' => 604266,
                'postal_codes' => ['367000'],
                'translations' => [
                    'lt' => ['name' => 'Machachkala', 'description' => 'Dagestano sostinė'],
                    'en' => ['name' => 'Makhachkala', 'description' => 'Capital of Dagestan']
            ],
            ],
            [
                'name' => 'Tomsk',
                'code' => 'RU-TOM-TOM',
                'region_id' => null,
                'latitude' => 56.4886,
                'longitude' => 84.9522,
                'population' => 576624,
                'postal_codes' => ['634000'],
                'translations' => [
                    'lt' => ['name' => 'Tomskas', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Tomsk', 'description' => 'University city']
            ],
            ],
            [
                'name' => 'Ryazan',
                'code' => 'RU-RYA-RYA',
                'region_id' => null,
                'latitude' => 54.6253,
                'longitude' => 39.7353,
                'population' => 537622,
                'postal_codes' => ['390000'],
                'translations' => [
                    'lt' => ['name' => 'Riazanė', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Ryazan', 'description' => 'Historic city']
            ],
            ],
            [
                'name' => 'Astrakhan',
                'code' => 'RU-AST-AST',
                'region_id' => null,
                'latitude' => 46.3497,
                'longitude' => 48.0408,
                'population' => 532504,
                'postal_codes' => ['414000'],
                'translations' => [
                    'lt' => ['name' => 'Astrachanė', 'description' => 'Kaspijos jūros miestas'],
                    'en' => ['name' => 'Astrakhan', 'description' => 'Caspian Sea city']
            ],
            ],
            [
                'name' => 'Penza',
                'code' => 'RU-PNZ-PNZ',
                'region_id' => null,
                'latitude' => 53.2007,
                'longitude' => 45.0046,
                'population' => 520300,
                'postal_codes' => ['440000'],
                'translations' => [
                    'lt' => ['name' => 'Penza', 'description' => 'Centrinės Rusijos miestas'],
                    'en' => ['name' => 'Penza', 'description' => 'Central Russian city']
            ],
            ],
            [
                'name' => 'Lipetsk',
                'code' => 'RU-LIP-LIP',
                'region_id' => null,
                'latitude' => 52.6086,
                'longitude' => 39.5994,
                'population' => 508124,
                'postal_codes' => ['398000'],
                'translations' => [
                    'lt' => ['name' => 'Lipeckas', 'description' => 'Metalingų pramonės centras'],
                    'en' => ['name' => 'Lipetsk', 'description' => 'Metallurgy center']
            ],
            ],
            [
                'name' => 'Tula',
                'code' => 'RU-TUL-TUL',
                'region_id' => null,
                'latitude' => 54.1961,
                'longitude' => 37.6182,
                'population' => 467955,
                'postal_codes' => ['300000'],
                'translations' => [
                    'lt' => ['name' => 'Tula', 'description' => 'Ginklų pramonės centras'],
                    'en' => ['name' => 'Tula', 'description' => 'Arms industry center']
            ],
            ],
            [
                'name' => 'Kirov',
                'code' => 'RU-KIR-KIR',
                'region_id' => null,
                'latitude' => 58.6036,
                'longitude' => 49.6681,
                'population' => 501468,
                'postal_codes' => ['610000'],
                'translations' => [
                    'lt' => ['name' => 'Kirovas', 'description' => 'Kirovo srities sostinė'],
                    'en' => ['name' => 'Kirov', 'description' => 'Capital of Kirov Oblast']
            ],
            ],
            [
                'name' => 'Cheboksary',
                'code' => 'RU-CU-CHE',
                'region_id' => null,
                'latitude' => 56.1322,
                'longitude' => 47.2519,
                'population' => 495518,
                'postal_codes' => ['428000'],
                'translations' => [
                    'lt' => ['name' => 'Čeboksarai', 'description' => 'Čiuvašijos sostinė'],
                    'en' => ['name' => 'Cheboksary', 'description' => 'Capital of Chuvashia']
            ],
            ],
            [
                'name' => 'Kaliningrad',
                'code' => 'RU-KGD-KAL',
                'region_id' => null,
                'latitude' => 54.7065,
                'longitude' => 20.5110,
                'population' => 475056,
                'postal_codes' => ['236000'],
                'translations' => [
                    'lt' => ['name' => 'Kaliningradas', 'description' => 'Eksklavas prie Baltijos jūros'],
                    'en' => ['name' => 'Kaliningrad', 'description' => 'Baltic Sea exclave']
            ],
            ],
            [
                'name' => 'Bryansk',
                'code' => 'RU-BRY-BRY',
                'region_id' => null,
                'latitude' => 53.2434,
                'longitude' => 34.3654,
                'population' => 405723,
                'postal_codes' => ['241000'],
                'translations' => [
                    'lt' => ['name' => 'Brianske', 'description' => 'Pietvakarių Rusijos miestas'],
                    'en' => ['name' => 'Bryansk', 'description' => 'Southwestern Russian city']
            ],
            ],
            [
                'name' => 'Ivanovo',
                'code' => 'RU-IVA-IVA',
                'region_id' => null,
                'latitude' => 56.9972,
                'longitude' => 40.9714,
                'population' => 408330,
                'postal_codes' => ['153000'],
                'translations' => [
                    'lt' => ['name' => 'Ivanovas', 'description' => 'Tekstilės pramonės centras'],
                    'en' => ['name' => 'Ivanovo', 'description' => 'Textile industry center']
            ],
            ],
            [
                'name' => 'Magnitogorsk',
                'code' => 'RU-CHE-MAG',
                'latitude' => 53.4186,
                'longitude' => 59.0472,
                'population' => 413253,
                'postal_codes' => ['455000'],
                'translations' => [
                    'lt' => ['name' => 'Magnitogorskas', 'description' => 'Metalingų pramonės centras'],
                    'en' => ['name' => 'Magnitogorsk', 'description' => 'Metallurgy center']
            ],
            ],
            [
                'name' => 'Kurgan',
                'code' => 'RU-KGN-KUR',
                'region_id' => null,
                'latitude' => 55.4411,
                'longitude' => 65.3422,
                'population' => 309285,
                'postal_codes' => ['640000'],
                'translations' => [
                    'lt' => ['name' => 'Kurganas', 'description' => 'Sibiro miestas'],
                    'en' => ['name' => 'Kurgan', 'description' => 'Siberian city']
            ],
            ],
            [
                'name' => 'Orsk',
                'code' => 'RU-ORE-ORS',
                'region_id' => null,
                'latitude' => 51.2293,
                'longitude' => 58.5702,
                'population' => 229255,
                'postal_codes' => ['462400'],
                'translations' => [
                    'lt' => ['name' => 'Orskas', 'description' => 'Uralo miestas'],
                    'en' => ['name' => 'Orsk', 'description' => 'Ural city']
            ],
            ],
            [
                'name' => 'Sterlitamak',
                'code' => 'RU-BA-STER',
                'region_id' => null,
                'latitude' => 53.6333,
                'longitude' => 55.9500,
                'population' => 279382,
                'postal_codes' => ['453100'],
                'translations' => [
                    'lt' => ['name' => 'Sterlitamakas', 'description' => 'Baškirijos miestas'],
                    'en' => ['name' => 'Sterlitamak', 'description' => 'Bashkortostan city']
            ],
            ],
            [
                'name' => 'Angarsk',
                'code' => 'RU-IRK-ANG',
                'region_id' => null,
                'latitude' => 52.5444,
                'longitude' => 103.8881,
                'population' => 226374,
                'postal_codes' => ['665800'],
                'translations' => [
                    'lt' => ['name' => 'Angarskas', 'description' => 'Petrochemijos centras'],
                    'en' => ['name' => 'Angarsk', 'description' => 'Petrochemical center']
            ],
            ],
            [
                'name' => 'Balakovo',
                'code' => 'RU-SAR-BAL',
                'latitude' => 52.0383,
                'longitude' => 47.7822,
                'population' => 191260,
                'postal_codes' => ['413840'],
                'translations' => [
                    'lt' => ['name' => 'Balakovas', 'description' => 'Volgos miestas'],
                    'en' => ['name' => 'Balakovo', 'description' => 'Volga city']
            ],
            ],
            [
                'name' => 'Blagoveshchensk',
                'code' => 'RU-AMU-BLA',
                'region_id' => null,
                'latitude' => 50.2794,
                'longitude' => 127.5406,
                'population' => 241437,
                'postal_codes' => ['675000'],
                'translations' => [
                    'lt' => ['name' => 'Blagoveščenskas', 'description' => 'Kinijos sienos miestas'],
                    'en' => ['name' => 'Blagoveshchensk', 'description' => 'Chinese border city']
            ],
            ],
            [
                'name' => 'Pskov',
                'code' => 'RU-PSK-PSK',
                'region_id' => null,
                'latitude' => 57.8194,
                'longitude' => 28.3344,
                'population' => 209426,
                'postal_codes' => ['180000'],
                'translations' => [
                    'lt' => ['name' => 'Pskovas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Pskov', 'description' => 'Historic city']
            ],
            ],
            [
                'name' => 'Biysk',
                'code' => 'RU-ALT-BII',
                'region_id' => null,
                'latitude' => 52.5167,
                'longitude' => 85.1667,
                'population' => 200629,
                'postal_codes' => ['659300'],
                'translations' => [
                    'lt' => ['name' => 'Bijskas', 'description' => 'Altajaus miestas'],
                    'en' => ['name' => 'Biysk', 'description' => 'Altai city']
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
                    'country_id' => $russia->id,
                    'zone_id' => $ruZone?->id,
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
