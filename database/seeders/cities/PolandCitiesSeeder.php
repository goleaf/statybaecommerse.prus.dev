<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\Translations\CityTranslation;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class PolandCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $poland = Country::where('cca2', 'PL')->first();
        $euZone = Zone::where('code', 'EU')->first();

        // Get regions
        $mazowieckieRegion = Region::where('code', 'PL-MZ')->first();
        $malopolskieRegion = Region::where('code', 'PL-MA')->first();
        $slaskieRegion = Region::where('code', 'PL-SL')->first();
        $wielkopolskieRegion = Region::where('code', 'PL-WP')->first();
        $dolnoslaskieRegion = Region::where('code', 'PL-DS')->first();
        $lubelskieRegion = Region::where('code', 'PL-LU')->first();
        $podlaskieRegion = Region::where('code', 'PL-PD')->first();
        $lubuskieRegion = Region::where('code', 'PL-LB')->first();
        $zachodniopomorskieRegion = Region::where('code', 'PL-ZP')->first();
        $pomorskieRegion = Region::where('code', 'PL-PM')->first();
        $kujawskoPomorskieRegion = Region::where('code', 'PL-KP')->first();
        $warminskoMazurskieRegion = Region::where('code', 'PL-WN')->first();
        $podkarpackieRegion = Region::where('code', 'PL-PK')->first();
        $swietokrzyskieRegion = Region::where('code', 'PL-SK')->first();
        $lodzkieRegion = Region::where('code', 'PL-LD')->first();
        $opolskieRegion = Region::where('code', 'PL-OP')->first();

        $cities = [
            // Mazowieckie (Warsaw region)
            [
                'name' => 'Warsaw',
                'code' => 'PL-MZ-WAW',
                'is_capital' => true,
                'is_default' => true,
                'region_id' => $mazowieckieRegion?->id,
                'latitude' => 52.2297,
                'longitude' => 21.0122,
                'population' => 1790658,
                'postal_codes' => ['00-001', '00-002', '00-003'],
                'translations' => [
                    'lt' => ['name' => 'Varšuva', 'description' => 'Lenkijos sostinė'],
                    'en' => ['name' => 'Warsaw', 'description' => 'Capital of Poland'],
                ],
            ],
            [
                'name' => 'Radom',
                'code' => 'PL-MZ-RAD',
                'region_id' => $mazowieckieRegion?->id,
                'latitude' => 51.4025,
                'longitude' => 21.1471,
                'population' => 210000,
                'postal_codes' => ['26-600'],
                'translations' => [
                    'lt' => ['name' => 'Radomas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Radom', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Płock',
                'code' => 'PL-MZ-PLO',
                'region_id' => $mazowieckieRegion?->id,
                'latitude' => 52.5464,
                'longitude' => 19.7064,
                'population' => 120000,
                'postal_codes' => ['09-400'],
                'translations' => [
                    'lt' => ['name' => 'Płock', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Płock', 'description' => 'Historic city'],
                ],
            ],
            // Małopolskie (Krakow region)
            [
                'name' => 'Krakow',
                'code' => 'PL-MA-KRA',
                'region_id' => $malopolskieRegion?->id,
                'latitude' => 50.0647,
                'longitude' => 19.945,
                'population' => 779115,
                'postal_codes' => ['30-001', '30-002'],
                'translations' => [
                    'lt' => ['name' => 'Krokuva', 'description' => 'Kultūros sostinė'],
                    'en' => ['name' => 'Krakow', 'description' => 'Cultural capital'],
                ],
            ],
            [
                'name' => 'Tarnów',
                'code' => 'PL-MA-TAR',
                'region_id' => $malopolskieRegion?->id,
                'latitude' => 50.0128,
                'longitude' => 20.9864,
                'population' => 110000,
                'postal_codes' => ['33-100'],
                'translations' => [
                    'lt' => ['name' => 'Tarnuvas', 'description' => 'Chemijos pramonės centras'],
                    'en' => ['name' => 'Tarnów', 'description' => 'Chemical industry center'],
                ],
            ],
            [
                'name' => 'Nowy Sącz',
                'code' => 'PL-MA-NOW',
                'region_id' => $malopolskieRegion?->id,
                'latitude' => 49.6214,
                'longitude' => 20.6969,
                'population' => 83000,
                'postal_codes' => ['33-300'],
                'translations' => [
                    'lt' => ['name' => 'Nowy Sącz', 'description' => 'Pietų Lenkijos miestas'],
                    'en' => ['name' => 'Nowy Sącz', 'description' => 'Southern Poland city'],
                ],
            ],
            // Śląskie (Silesia region)
            [
                'name' => 'Katowice',
                'code' => 'PL-SL-KAT',
                'region_id' => $slaskieRegion?->id,
                'latitude' => 50.2649,
                'longitude' => 19.0238,
                'population' => 294510,
                'postal_codes' => ['40-001'],
                'translations' => [
                    'lt' => ['name' => 'Katovicai', 'description' => 'Šlonsko centras'],
                    'en' => ['name' => 'Katowice', 'description' => 'Center of Silesia'],
                ],
            ],
            [
                'name' => 'Częstochowa',
                'code' => 'PL-SL-CZE',
                'region_id' => $slaskieRegion?->id,
                'latitude' => 50.7969,
                'longitude' => 19.1242,
                'population' => 220000,
                'postal_codes' => ['42-200'],
                'translations' => [
                    'lt' => ['name' => 'Čenstakova', 'description' => 'Pilgrimų miestas'],
                    'en' => ['name' => 'Częstochowa', 'description' => 'Pilgrimage city'],
                ],
            ],
            [
                'name' => 'Sosnowiec',
                'code' => 'PL-SL-SOS',
                'region_id' => $slaskieRegion?->id,
                'latitude' => 50.2863,
                'longitude' => 19.104,
                'population' => 200000,
                'postal_codes' => ['41-200'],
                'translations' => [
                    'lt' => ['name' => 'Sosnoviecas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Sosnowiec', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Gliwice',
                'code' => 'PL-SL-GLI',
                'region_id' => $slaskieRegion?->id,
                'latitude' => 50.2945,
                'longitude' => 18.6714,
                'population' => 180000,
                'postal_codes' => ['44-100'],
                'translations' => [
                    'lt' => ['name' => 'Glicai', 'description' => 'Technologijų centras'],
                    'en' => ['name' => 'Gliwice', 'description' => 'Technology center'],
                ],
            ],
            [
                'name' => 'Zabrze',
                'code' => 'PL-SL-ZAB',
                'region_id' => $slaskieRegion?->id,
                'latitude' => 50.3249,
                'longitude' => 18.7857,
                'population' => 170000,
                'postal_codes' => ['41-800'],
                'translations' => [
                    'lt' => ['name' => 'Zabžė', 'description' => 'Anglies pramonės centras'],
                    'en' => ['name' => 'Zabrze', 'description' => 'Coal industry center'],
                ],
            ],
            [
                'name' => 'Bytom',
                'code' => 'PL-SL-BYT',
                'region_id' => $slaskieRegion?->id,
                'latitude' => 50.348,
                'longitude' => 18.9328,
                'population' => 160000,
                'postal_codes' => ['41-900'],
                'translations' => [
                    'lt' => ['name' => 'Bitomas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Bytom', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Ruda Śląska',
                'code' => 'PL-SL-RUD',
                'region_id' => $slaskieRegion?->id,
                'latitude' => 50.2923,
                'longitude' => 18.8563,
                'population' => 140000,
                'postal_codes' => ['41-700'],
                'translations' => [
                    'lt' => ['name' => 'Ruda Šlonska', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Ruda Śląska', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Rybnik',
                'code' => 'PL-SL-RYB',
                'region_id' => $slaskieRegion?->id,
                'latitude' => 50.1022,
                'longitude' => 18.5464,
                'population' => 140000,
                'postal_codes' => ['44-200'],
                'translations' => [
                    'lt' => ['name' => 'Ribnikas', 'description' => 'Anglies pramonės centras'],
                    'en' => ['name' => 'Rybnik', 'description' => 'Coal industry center'],
                ],
            ],
            [
                'name' => 'Tychy',
                'code' => 'PL-SL-TYC',
                'region_id' => $slaskieRegion?->id,
                'latitude' => 50.12,
                'longitude' => 19.1667,
                'population' => 130000,
                'postal_codes' => ['43-100'],
                'translations' => [
                    'lt' => ['name' => 'Tichai', 'description' => 'Alaus pramonės centras'],
                    'en' => ['name' => 'Tychy', 'description' => 'Beer industry center'],
                ],
            ],
            [
                'name' => 'Dąbrowa Górnicza',
                'code' => 'PL-SL-DAB',
                'region_id' => $slaskieRegion?->id,
                'latitude' => 50.3219,
                'longitude' => 19.1875,
                'population' => 120000,
                'postal_codes' => ['41-300'],
                'translations' => [
                    'lt' => ['name' => 'Dombrova Gurniča', 'description' => 'Anglies pramonės centras'],
                    'en' => ['name' => 'Dąbrowa Górnicza', 'description' => 'Coal industry center'],
                ],
            ],
            // Wielkopolskie (Poznań region)
            [
                'name' => 'Poznań',
                'code' => 'PL-WP-POZ',
                'region_id' => $wielkopolskieRegion?->id,
                'latitude' => 52.4064,
                'longitude' => 16.9252,
                'population' => 534813,
                'postal_codes' => ['60-001'],
                'translations' => [
                    'lt' => ['name' => 'Poznanė', 'description' => 'Vakarų Lenkijos centras'],
                    'en' => ['name' => 'Poznań', 'description' => 'Center of Western Poland'],
                ],
            ],
            [
                'name' => 'Kalisz',
                'code' => 'PL-WP-KAL',
                'region_id' => $wielkopolskieRegion?->id,
                'latitude' => 51.7619,
                'longitude' => 18.0911,
                'population' => 100000,
                'postal_codes' => ['62-800'],
                'translations' => [
                    'lt' => ['name' => 'Kališas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Kalisz', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Konin',
                'code' => 'PL-WP-KON',
                'region_id' => $wielkopolskieRegion?->id,
                'latitude' => 52.2233,
                'longitude' => 18.2511,
                'population' => 75000,
                'postal_codes' => ['62-500'],
                'translations' => [
                    'lt' => ['name' => 'Koninas', 'description' => 'Energetikos centras'],
                    'en' => ['name' => 'Konin', 'description' => 'Energy center'],
                ],
            ],
            // Dolnośląskie (Wrocław region)
            [
                'name' => 'Wrocław',
                'code' => 'PL-DS-WRO',
                'region_id' => $dolnoslaskieRegion?->id,
                'latitude' => 51.1079,
                'longitude' => 17.0385,
                'population' => 641607,
                'postal_codes' => ['50-001'],
                'translations' => [
                    'lt' => ['name' => 'Vroclavas', 'description' => 'Pietvakarių Lenkijos centras'],
                    'en' => ['name' => 'Wrocław', 'description' => 'Center of Southwestern Poland'],
                ],
            ],
            [
                'name' => 'Wałbrzych',
                'code' => 'PL-DS-WAL',
                'region_id' => $dolnoslaskieRegion?->id,
                'latitude' => 50.7708,
                'longitude' => 16.2844,
                'population' => 110000,
                'postal_codes' => ['58-300'],
                'translations' => [
                    'lt' => ['name' => 'Valbžichas', 'description' => 'Anglies pramonės centras'],
                    'en' => ['name' => 'Wałbrzych', 'description' => 'Coal industry center'],
                ],
            ],
            [
                'name' => 'Legnica',
                'code' => 'PL-DS-LEG',
                'region_id' => $dolnoslaskieRegion?->id,
                'latitude' => 51.21,
                'longitude' => 16.1619,
                'population' => 100000,
                'postal_codes' => ['59-220'],
                'translations' => [
                    'lt' => ['name' => 'Legnica', 'description' => 'Metalurgijos centras'],
                    'en' => ['name' => 'Legnica', 'description' => 'Metallurgy center'],
                ],
            ],
            // Lubelskie
            [
                'name' => 'Lublin',
                'code' => 'PL-LU-LUB',
                'region_id' => $lubelskieRegion?->id,
                'latitude' => 51.2465,
                'longitude' => 22.5684,
                'population' => 339784,
                'postal_codes' => ['20-001'],
                'translations' => [
                    'lt' => ['name' => 'Liublinas', 'description' => 'Rytų Lenkijos centras'],
                    'en' => ['name' => 'Lublin', 'description' => 'Center of Eastern Poland'],
                ],
            ],
            [
                'name' => 'Chełm',
                'code' => 'PL-LU-CHE',
                'region_id' => $lubelskieRegion?->id,
                'latitude' => 51.1333,
                'longitude' => 23.4833,
                'population' => 60000,
                'postal_codes' => ['22-100'],
                'translations' => [
                    'lt' => ['name' => 'Chelmas', 'description' => 'Sienos miestas'],
                    'en' => ['name' => 'Chełm', 'description' => 'Border city'],
                ],
            ],
            // Podlaskie
            [
                'name' => 'Białystok',
                'code' => 'PL-PD-BIA',
                'region_id' => $podlaskieRegion?->id,
                'latitude' => 53.1325,
                'longitude' => 23.1688,
                'population' => 297554,
                'postal_codes' => ['15-001'],
                'translations' => [
                    'lt' => ['name' => 'Balstogė', 'description' => 'Šiaurės rytų Lenkijos centras'],
                    'en' => ['name' => 'Białystok', 'description' => 'Center of Northeastern Poland'],
                ],
            ],
            [
                'name' => 'Suwałki',
                'code' => 'PL-PD-SUW',
                'region_id' => $podlaskieRegion?->id,
                'latitude' => 54.1019,
                'longitude' => 22.9308,
                'population' => 69000,
                'postal_codes' => ['16-400'],
                'translations' => [
                    'lt' => ['name' => 'Suvalkai', 'description' => 'Sienos miestas su Lietuva'],
                    'en' => ['name' => 'Suwałki', 'description' => 'Border city with Lithuania'],
                ],
            ],
            // Lubuskie
            [
                'name' => 'Zielona Góra',
                'code' => 'PL-LB-ZIE',
                'region_id' => $lubuskieRegion?->id,
                'latitude' => 51.9356,
                'longitude' => 15.5064,
                'population' => 140000,
                'postal_codes' => ['65-001'],
                'translations' => [
                    'lt' => ['name' => 'Zeliona Gura', 'description' => 'Vyno pramonės centras'],
                    'en' => ['name' => 'Zielona Góra', 'description' => 'Wine industry center'],
                ],
            ],
            [
                'name' => 'Gorzów Wielkopolski',
                'code' => 'PL-LB-GOR',
                'region_id' => $lubuskieRegion?->id,
                'latitude' => 52.7364,
                'longitude' => 15.2289,
                'population' => 120000,
                'postal_codes' => ['66-400'],
                'translations' => [
                    'lt' => ['name' => 'Goržuvas Velkopolskis', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Gorzów Wielkopolski', 'description' => 'Industrial city'],
                ],
            ],
            // Zachodniopomorskie
            [
                'name' => 'Szczecin',
                'code' => 'PL-ZP-SZC',
                'region_id' => $zachodniopomorskieRegion?->id,
                'latitude' => 53.4285,
                'longitude' => 14.5528,
                'population' => 400990,
                'postal_codes' => ['70-001'],
                'translations' => [
                    'lt' => ['name' => 'Ščecinas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Szczecin', 'description' => 'Port city'],
                ],
            ],
            [
                'name' => 'Koszalin',
                'code' => 'PL-ZP-KOS',
                'region_id' => $zachodniopomorskieRegion?->id,
                'latitude' => 54.1903,
                'longitude' => 16.1819,
                'population' => 107000,
                'postal_codes' => ['75-001'],
                'translations' => [
                    'lt' => ['name' => 'Košalinas', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'Koszalin', 'description' => 'Seaside city'],
                ],
            ],
            // Pomorskie
            [
                'name' => 'Gdańsk',
                'code' => 'PL-PM-GDA',
                'region_id' => $pomorskieRegion?->id,
                'latitude' => 54.352,
                'longitude' => 18.6466,
                'population' => 470907,
                'postal_codes' => ['80-001'],
                'translations' => [
                    'lt' => ['name' => 'Gdanskas', 'description' => 'Baltijos jūros uostas'],
                    'en' => ['name' => 'Gdańsk', 'description' => 'Baltic Sea port'],
                ],
            ],
            [
                'name' => 'Gdynia',
                'code' => 'PL-PM-GDY',
                'region_id' => $pomorskieRegion?->id,
                'latitude' => 54.5189,
                'longitude' => 18.5305,
                'population' => 245867,
                'postal_codes' => ['81-001'],
                'translations' => [
                    'lt' => ['name' => 'Gdinija', 'description' => 'Modernus uostamiesčis'],
                    'en' => ['name' => 'Gdynia', 'description' => 'Modern port city'],
                ],
            ],
            [
                'name' => 'Słupsk',
                'code' => 'PL-PM-SLU',
                'region_id' => $pomorskieRegion?->id,
                'latitude' => 54.4642,
                'longitude' => 17.0286,
                'population' => 90000,
                'postal_codes' => ['76-200'],
                'translations' => [
                    'lt' => ['name' => 'Slupskas', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'Słupsk', 'description' => 'Seaside city'],
                ],
            ],
            // Kujawsko-Pomorskie
            [
                'name' => 'Bydgoszcz',
                'code' => 'PL-KP-BYD',
                'region_id' => $kujawskoPomorskieRegion?->id,
                'latitude' => 53.1235,
                'longitude' => 18.0084,
                'population' => 346739,
                'postal_codes' => ['85-001'],
                'translations' => [
                    'lt' => ['name' => 'Bidgoščas', 'description' => 'Pramonės centras'],
                    'en' => ['name' => 'Bydgoszcz', 'description' => 'Industrial center'],
                ],
            ],
            [
                'name' => 'Toruń',
                'code' => 'PL-KP-TOR',
                'region_id' => $kujawskoPomorskieRegion?->id,
                'latitude' => 53.0138,
                'longitude' => 18.5984,
                'population' => 201106,
                'postal_codes' => ['87-100'],
                'translations' => [
                    'lt' => ['name' => 'Torunė', 'description' => 'Koperniko miestas'],
                    'en' => ['name' => 'Toruń', 'description' => 'Copernicus city'],
                ],
            ],
            [
                'name' => 'Włocławek',
                'code' => 'PL-KP-WLO',
                'region_id' => $kujawskoPomorskieRegion?->id,
                'latitude' => 52.6481,
                'longitude' => 19.0678,
                'population' => 110000,
                'postal_codes' => ['87-800'],
                'translations' => [
                    'lt' => ['name' => 'Vloclavekas', 'description' => 'Chemijos pramonės centras'],
                    'en' => ['name' => 'Włocławek', 'description' => 'Chemical industry center'],
                ],
            ],
            // Warmińsko-Mazurskie
            [
                'name' => 'Olsztyn',
                'code' => 'PL-WN-OLS',
                'region_id' => $warminskoMazurskieRegion?->id,
                'latitude' => 53.7784,
                'longitude' => 20.4801,
                'population' => 171979,
                'postal_codes' => ['10-001'],
                'translations' => [
                    'lt' => ['name' => 'Olštynas', 'description' => 'Šiaurės Lenkijos centras'],
                    'en' => ['name' => 'Olsztyn', 'description' => 'Center of Northern Poland'],
                ],
            ],
            [
                'name' => 'Elbląg',
                'code' => 'PL-WN-ELB',
                'region_id' => $warminskoMazurskieRegion?->id,
                'latitude' => 54.1561,
                'longitude' => 19.4047,
                'population' => 120000,
                'postal_codes' => ['82-300'],
                'translations' => [
                    'lt' => ['name' => 'Elblongas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Elbląg', 'description' => 'Historic city'],
                ],
            ],
            // Podkarpackie
            [
                'name' => 'Rzeszów',
                'code' => 'PL-PK-RZE',
                'region_id' => $podkarpackieRegion?->id,
                'latitude' => 50.0413,
                'longitude' => 21.9991,
                'population' => 196821,
                'postal_codes' => ['35-001'],
                'translations' => [
                    'lt' => ['name' => 'Žešuvas', 'description' => 'Pietryčių Lenkijos centras'],
                    'en' => ['name' => 'Rzeszów', 'description' => 'Center of Southeastern Poland'],
                ],
            ],
            [
                'name' => 'Przemyśl',
                'code' => 'PL-PK-PRZ',
                'region_id' => $podkarpackieRegion?->id,
                'latitude' => 49.7844,
                'longitude' => 22.7672,
                'population' => 60000,
                'postal_codes' => ['37-700'],
                'translations' => [
                    'lt' => ['name' => 'Pšemyslas', 'description' => 'Sienos miestas su Ukraina'],
                    'en' => ['name' => 'Przemyśl', 'description' => 'Border city with Ukraine'],
                ],
            ],
            // Świętokrzyskie
            [
                'name' => 'Kielce',
                'code' => 'PL-SK-KIE',
                'region_id' => $swietokrzyskieRegion?->id,
                'latitude' => 50.8661,
                'longitude' => 20.6286,
                'population' => 194852,
                'postal_codes' => ['25-001'],
                'translations' => [
                    'lt' => ['name' => 'Kelcai', 'description' => 'Centrinės Lenkijos centras'],
                    'en' => ['name' => 'Kielce', 'description' => 'Center of Central Poland'],
                ],
            ],
            // Łódzkie
            [
                'name' => 'Łódź',
                'code' => 'PL-LD-LOD',
                'region_id' => $lodzkieRegion?->id,
                'latitude' => 51.7592,
                'longitude' => 19.456,
                'population' => 677286,
                'postal_codes' => ['90-001'],
                'translations' => [
                    'lt' => ['name' => 'Lodzė', 'description' => 'Tekstilės pramonės centras'],
                    'en' => ['name' => 'Łódź', 'description' => 'Textile industry center'],
                ],
            ],
            [
                'name' => 'Piotrków Trybunalski',
                'code' => 'PL-LD-PIO',
                'region_id' => $lodzkieRegion?->id,
                'latitude' => 51.4056,
                'longitude' => 19.7031,
                'population' => 75000,
                'postal_codes' => ['97-300'],
                'translations' => [
                    'lt' => ['name' => 'Piotrkuvas Tribunalskis', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Piotrków Trybunalski', 'description' => 'Historic city'],
                ],
            ],
            // Opolskie
            [
                'name' => 'Opole',
                'code' => 'PL-OP-OPO',
                'region_id' => $opolskieRegion?->id,
                'latitude' => 50.6751,
                'longitude' => 17.9213,
                'population' => 128034,
                'postal_codes' => ['45-001'],
                'translations' => [
                    'lt' => ['name' => 'Opolė', 'description' => 'Pietų Lenkijos centras'],
                    'en' => ['name' => 'Opole', 'description' => 'Center of Southern Poland'],
                ],
            ],
            [
                'name' => 'Kędzierzyn-Koźle',
                'code' => 'PL-OP-KED',
                'region_id' => $opolskieRegion?->id,
                'latitude' => 50.3500,
                'longitude' => 18.2167,
                'population' => 60000,
                'postal_codes' => ['47-200'],
                'translations' => [
                    'lt' => ['name' => 'Kędzierzyn-Koźle', 'description' => 'Chemijos pramonės centras'],
                    'en' => ['name' => 'Kędzierzyn-Koźle', 'description' => 'Chemical industry center'],
                ],
            ],
            [
                'name' => 'Nysa',
                'code' => 'PL-OP-NYS',
                'region_id' => $opolskieRegion?->id,
                'latitude' => 50.4667,
                'longitude' => 17.3333,
                'population' => 45000,
                'postal_codes' => ['48-300'],
                'translations' => [
                    'lt' => ['name' => 'Nysa', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Nysa', 'description' => 'Historic city'],
                ],
            ],
            // Additional Mazowieckie cities
            [
                'name' => 'Siedlce',
                'code' => 'PL-MZ-SIE',
                'region_id' => $mazowieckieRegion?->id,
                'latitude' => 52.1667,
                'longitude' => 22.2833,
                'population' => 77000,
                'postal_codes' => ['08-100'],
                'translations' => [
                    'lt' => ['name' => 'Sedlce', 'description' => 'Rytų Mazovijos centras'],
                    'en' => ['name' => 'Siedlce', 'description' => 'Center of Eastern Mazovia'],
                ],
            ],
            [
                'name' => 'Pruszków',
                'code' => 'PL-MZ-PRU',
                'region_id' => $mazowieckieRegion?->id,
                'latitude' => 52.1667,
                'longitude' => 20.8000,
                'population' => 60000,
                'postal_codes' => ['05-800'],
                'translations' => [
                    'lt' => ['name' => 'Pruszków', 'description' => 'Varšuvos priemiestis'],
                    'en' => ['name' => 'Pruszków', 'description' => 'Warsaw suburb'],
                ],
            ],
            [
                'name' => 'Legionowo',
                'code' => 'PL-MZ-LEG',
                'region_id' => $mazowieckieRegion?->id,
                'latitude' => 52.4000,
                'longitude' => 20.9167,
                'population' => 54000,
                'postal_codes' => ['05-120'],
                'translations' => [
                    'lt' => ['name' => 'Legionowo', 'description' => 'Varšuvos priemiestis'],
                    'en' => ['name' => 'Legionowo', 'description' => 'Warsaw suburb'],
                ],
            ],
            [
                'name' => 'Ostrołęka',
                'code' => 'PL-MZ-OST',
                'region_id' => $mazowieckieRegion?->id,
                'latitude' => 53.0833,
                'longitude' => 21.5667,
                'population' => 52000,
                'postal_codes' => ['07-400'],
                'translations' => [
                    'lt' => ['name' => 'Ostrołęka', 'description' => 'Šiaurės Mazovijos centras'],
                    'en' => ['name' => 'Ostrołęka', 'description' => 'Center of Northern Mazovia'],
                ],
            ],
            [
                'name' => 'Ciechanów',
                'code' => 'PL-MZ-CIE',
                'region_id' => $mazowieckieRegion?->id,
                'latitude' => 52.8833,
                'longitude' => 20.6167,
                'population' => 45000,
                'postal_codes' => ['06-400'],
                'translations' => [
                    'lt' => ['name' => 'Ciechanów', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Ciechanów', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Żyrardów',
                'code' => 'PL-MZ-ZYR',
                'region_id' => $mazowieckieRegion?->id,
                'latitude' => 52.0500,
                'longitude' => 20.4333,
                'population' => 41000,
                'postal_codes' => ['96-300'],
                'translations' => [
                    'lt' => ['name' => 'Żyrardów', 'description' => 'Tekstilės pramonės miestas'],
                    'en' => ['name' => 'Żyrardów', 'description' => 'Textile industry city'],
                ],
            ],
            // Additional Małopolskie cities
            [
                'name' => 'Nowy Sącz',
                'code' => 'PL-MA-NOW',
                'region_id' => $malopolskieRegion?->id,
                'latitude' => 49.6167,
                'longitude' => 20.7167,
                'population' => 83000,
                'postal_codes' => ['33-300'],
                'translations' => [
                    'lt' => ['name' => 'Nowy Sącz', 'description' => 'Pietų Małopolskos centras'],
                    'en' => ['name' => 'Nowy Sącz', 'description' => 'Center of Southern Lesser Poland'],
                ],
            ],
            [
                'name' => 'Chrzanów',
                'code' => 'PL-MA-CHR',
                'region_id' => $malopolskieRegion?->id,
                'latitude' => 50.1333,
                'longitude' => 19.4000,
                'population' => 37000,
                'postal_codes' => ['32-500'],
                'translations' => [
                    'lt' => ['name' => 'Chrzanów', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Chrzanów', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Olkusz',
                'code' => 'PL-MA-OLK',
                'region_id' => $malopolskieRegion?->id,
                'latitude' => 50.2833,
                'longitude' => 19.5667,
                'population' => 36000,
                'postal_codes' => ['32-300'],
                'translations' => [
                    'lt' => ['name' => 'Olkusz', 'description' => 'Kasybos miestas'],
                    'en' => ['name' => 'Olkusz', 'description' => 'Mining city'],
                ],
            ],
            [
                'name' => 'Bochnia',
                'code' => 'PL-MA-BOC',
                'region_id' => $malopolskieRegion?->id,
                'latitude' => 49.9667,
                'longitude' => 20.4333,
                'population' => 30000,
                'postal_codes' => ['32-700'],
                'translations' => [
                    'lt' => ['name' => 'Bochnia', 'description' => 'Druskos kasybos miestas'],
                    'en' => ['name' => 'Bochnia', 'description' => 'Salt mining city'],
                ],
            ],
            [
                'name' => 'Gorlice',
                'code' => 'PL-MA-GOR',
                'region_id' => $malopolskieRegion?->id,
                'latitude' => 49.6500,
                'longitude' => 21.1667,
                'population' => 28000,
                'postal_codes' => ['38-300'],
                'translations' => [
                    'lt' => ['name' => 'Gorlice', 'description' => 'Naftos pramonės centras'],
                    'en' => ['name' => 'Gorlice', 'description' => 'Oil industry center'],
                ],
            ],
            [
                'name' => 'Zakopane',
                'code' => 'PL-MA-ZAK',
                'region_id' => $malopolskieRegion?->id,
                'latitude' => 49.3000,
                'longitude' => 19.9667,
                'population' => 27000,
                'postal_codes' => ['34-500'],
                'translations' => [
                    'lt' => ['name' => 'Zakopane', 'description' => 'Žiemos sporto miestas'],
                    'en' => ['name' => 'Zakopane', 'description' => 'Winter sports city'],
                ],
            ],
            // Additional Śląskie cities
            [
                'name' => 'Jaworzno',
                'code' => 'PL-SL-JAW',
                'region_id' => $slaskieRegion?->id,
                'latitude' => 50.2000,
                'longitude' => 19.2667,
                'population' => 92000,
                'postal_codes' => ['43-600'],
                'translations' => [
                    'lt' => ['name' => 'Jaworzno', 'description' => 'Anglies kasybos miestas'],
                    'en' => ['name' => 'Jaworzno', 'description' => 'Coal mining city'],
                ],
            ],
            [
                'name' => 'Jastrzębie-Zdrój',
                'code' => 'PL-SL-JAS',
                'region_id' => $slaskieRegion?->id,
                'latitude' => 49.9500,
                'longitude' => 18.5833,
                'population' => 90000,
                'postal_codes' => ['44-330'],
                'translations' => [
                    'lt' => ['name' => 'Jastrzębie-Zdrój', 'description' => 'Anglies ir kurortinis miestas'],
                    'en' => ['name' => 'Jastrzębie-Zdrój', 'description' => 'Coal and resort city'],
                ],
            ],
            [
                'name' => 'Mysłowice',
                'code' => 'PL-SL-MYS',
                'region_id' => $slaskieRegion?->id,
                'latitude' => 50.2333,
                'longitude' => 19.1333,
                'population' => 75000,
                'postal_codes' => ['41-400'],
                'translations' => [
                    'lt' => ['name' => 'Mysłowice', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Mysłowice', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Siemianowice Śląskie',
                'code' => 'PL-SL-SIE',
                'region_id' => $slaskieRegion?->id,
                'latitude' => 50.3000,
                'longitude' => 19.0333,
                'population' => 69000,
                'postal_codes' => ['41-100'],
                'translations' => [
                    'lt' => ['name' => 'Siemianowice Śląskie', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Siemianowice Śląskie', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Żory',
                'code' => 'PL-SL-ZOR',
                'region_id' => $slaskieRegion?->id,
                'latitude' => 50.0500,
                'longitude' => 18.7000,
                'population' => 62000,
                'postal_codes' => ['44-240'],
                'translations' => [
                    'lt' => ['name' => 'Żory', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Żory', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Piekary Śląskie',
                'code' => 'PL-SL-PIE',
                'region_id' => $slaskieRegion?->id,
                'latitude' => 50.3667,
                'longitude' => 18.9500,
                'population' => 55000,
                'postal_codes' => ['41-940'],
                'translations' => [
                    'lt' => ['name' => 'Piekary Śląskie', 'description' => 'Anglies kasybos miestas'],
                    'en' => ['name' => 'Piekary Śląskie', 'description' => 'Coal mining city'],
                ],
            ],
            [
                'name' => 'Ruda Śląska',
                'code' => 'PL-SL-RUD',
                'region_id' => $slaskieRegion?->id,
                'latitude' => 50.2667,
                'longitude' => 18.8667,
                'population' => 140000,
                'postal_codes' => ['41-700'],
                'translations' => [
                    'lt' => ['name' => 'Ruda Śląska', 'description' => 'Anglies kasybos miestas'],
                    'en' => ['name' => 'Ruda Śląska', 'description' => 'Coal mining city'],
                ],
            ],
            // Additional Wielkopolskie cities
            [
                'name' => 'Kalisz',
                'code' => 'PL-WP-KAL',
                'region_id' => $wielkopolskieRegion?->id,
                'latitude' => 51.7667,
                'longitude' => 18.0833,
                'population' => 100000,
                'postal_codes' => ['62-800'],
                'translations' => [
                    'lt' => ['name' => 'Kalisz', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Kalisz', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Konin',
                'code' => 'PL-WP-KON',
                'region_id' => $wielkopolskieRegion?->id,
                'latitude' => 52.2167,
                'longitude' => 18.2500,
                'population' => 75000,
                'postal_codes' => ['62-500'],
                'translations' => [
                    'lt' => ['name' => 'Konin', 'description' => 'Anglies kasybos miestas'],
                    'en' => ['name' => 'Konin', 'description' => 'Coal mining city'],
                ],
            ],
            [
                'name' => 'Piła',
                'code' => 'PL-WP-PIL',
                'region_id' => $wielkopolskieRegion?->id,
                'latitude' => 53.1500,
                'longitude' => 16.7333,
                'population' => 74000,
                'postal_codes' => ['64-920'],
                'translations' => [
                    'lt' => ['name' => 'Piła', 'description' => 'Šiaurės Wielkopolskos centras'],
                    'en' => ['name' => 'Piła', 'description' => 'Center of Northern Greater Poland'],
                ],
            ],
            [
                'name' => 'Ostrów Wielkopolski',
                'code' => 'PL-WP-OST',
                'region_id' => $wielkopolskieRegion?->id,
                'latitude' => 51.6500,
                'longitude' => 17.8167,
                'population' => 72000,
                'postal_codes' => ['63-400'],
                'translations' => [
                    'lt' => ['name' => 'Ostrów Wielkopolski', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Ostrów Wielkopolski', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Gniezno',
                'code' => 'PL-WP-GNI',
                'region_id' => $wielkopolskieRegion?->id,
                'latitude' => 52.5333,
                'longitude' => 17.6000,
                'population' => 69000,
                'postal_codes' => ['62-200'],
                'translations' => [
                    'lt' => ['name' => 'Gniezno', 'description' => 'Pirmoji Lenkijos sostinė'],
                    'en' => ['name' => 'Gniezno', 'description' => 'First capital of Poland'],
                ],
            ],
            [
                'name' => 'Leszno',
                'code' => 'PL-WP-LES',
                'region_id' => $wielkopolskieRegion?->id,
                'latitude' => 51.8333,
                'longitude' => 16.5667,
                'population' => 64000,
                'postal_codes' => ['64-100'],
                'translations' => [
                    'lt' => ['name' => 'Leszno', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Leszno', 'description' => 'Industrial city'],
                ],
            ],
            // Additional Dolnośląskie cities
            [
                'name' => 'Jelenia Góra',
                'code' => 'PL-DS-JEL',
                'region_id' => $dolnoslaskieRegion?->id,
                'latitude' => 50.9000,
                'longitude' => 15.7333,
                'population' => 80000,
                'postal_codes' => ['58-500'],
                'translations' => [
                    'lt' => ['name' => 'Jelenia Góra', 'description' => 'Karkonošų miestas'],
                    'en' => ['name' => 'Jelenia Góra', 'description' => 'Karkonosze city'],
                ],
            ],
            [
                'name' => 'Legnica',
                'code' => 'PL-DS-LEG',
                'region_id' => $dolnoslaskieRegion?->id,
                'latitude' => 51.2167,
                'longitude' => 16.1667,
                'population' => 100000,
                'postal_codes' => ['59-220'],
                'translations' => [
                    'lt' => ['name' => 'Legnica', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Legnica', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Głogów',
                'code' => 'PL-DS-GLO',
                'region_id' => $dolnoslaskieRegion?->id,
                'latitude' => 51.6667,
                'longitude' => 16.0833,
                'population' => 68000,
                'postal_codes' => ['67-200'],
                'translations' => [
                    'lt' => ['name' => 'Głogów', 'description' => 'Vario kasybos miestas'],
                    'en' => ['name' => 'Głogów', 'description' => 'Copper mining city'],
                ],
            ],
            [
                'name' => 'Świdnica',
                'code' => 'PL-DS-SWI',
                'region_id' => $dolnoslaskieRegion?->id,
                'latitude' => 50.8500,
                'longitude' => 16.4833,
                'population' => 57000,
                'postal_codes' => ['58-100'],
                'translations' => [
                    'lt' => ['name' => 'Świdnica', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Świdnica', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Bolesławiec',
                'code' => 'PL-DS-BOL',
                'region_id' => $dolnoslaskieRegion?->id,
                'latitude' => 51.2667,
                'longitude' => 15.5667,
                'population' => 40000,
                'postal_codes' => ['59-700'],
                'translations' => [
                    'lt' => ['name' => 'Bolesławiec', 'description' => 'Keramikos miestas'],
                    'en' => ['name' => 'Bolesławiec', 'description' => 'Ceramics city'],
                ],
            ],
            [
                'name' => 'Zgorzelec',
                'code' => 'PL-DS-ZGO',
                'region_id' => $dolnoslaskieRegion?->id,
                'latitude' => 51.1500,
                'longitude' => 15.0167,
                'population' => 32000,
                'postal_codes' => ['59-900'],
                'translations' => [
                    'lt' => ['name' => 'Zgorzelec', 'description' => 'Sienos miestas'],
                    'en' => ['name' => 'Zgorzelec', 'description' => 'Border city'],
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
                    'country_id' => $poland->id,
                    'zone_id' => $euZone?->id,
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
