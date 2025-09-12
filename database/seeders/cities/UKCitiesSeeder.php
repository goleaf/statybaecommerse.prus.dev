<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Translations\CityTranslation;
use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class UKCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $uk = Country::where('cca2', 'GB')->first();
        $ukZone = Zone::where('code', 'UK')->first();
        $euZone = Zone::where('code', 'EU')->first();

        // Get regions
        $englandRegion = Region::where('code', 'GB-ENG')->first();
        $scotlandRegion = Region::where('code', 'GB-SCT')->first();
        $walesRegion = Region::where('code', 'GB-WLS')->first();
        $northernIrelandRegion = Region::where('code', 'GB-NIR')->first();

        $cities = [
            // England
            [
                'name' => 'London',
                'code' => 'GB-ENG-LON',
                'is_capital' => true,
                'is_default' => true,
                'region_id' => $englandRegion?->id,
                'latitude' => 51.5074,
                'longitude' => -0.1278,
                'population' => 8982000,
                'postal_codes' => ['SW1A 1AA', 'EC1A 1BB', 'W1A 0AX'],
                'translations' => [
                    'lt' => ['name' => 'Londonas', 'description' => 'Didžiosios Britanijos sostinė'],
                    'en' => ['name' => 'London', 'description' => 'Capital of United Kingdom'],
                ],
            ],
            [
                'name' => 'Birmingham',
                'code' => 'GB-ENG-BIR',
                'region_id' => $englandRegion?->id,
                'latitude' => 52.4862,
                'longitude' => -1.8904,
                'population' => 1141816,
                'postal_codes' => ['B1 1AA', 'B2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Birmingamas', 'description' => 'Antrasis didžiausias Anglijos miestas'],
                    'en' => ['name' => 'Birmingham', 'description' => 'Second largest city in England'],
                ],
            ],
            [
                'name' => 'Manchester',
                'code' => 'GB-ENG-MAN',
                'region_id' => $englandRegion?->id,
                'latitude' => 53.4808,
                'longitude' => -2.2426,
                'population' => 547627,
                'postal_codes' => ['M1 1AA', 'M2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Mančesteris', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Manchester', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Liverpool',
                'code' => 'GB-ENG-LIV',
                'region_id' => $englandRegion?->id,
                'latitude' => 53.4084,
                'longitude' => -2.9916,
                'population' => 498042,
                'postal_codes' => ['L1 1AA', 'L2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Liverpulis', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Liverpool', 'description' => 'Port city'],
                ],
            ],
            [
                'name' => 'Leeds',
                'code' => 'GB-ENG-LEE',
                'region_id' => $englandRegion?->id,
                'latitude' => 53.8008,
                'longitude' => -1.5491,
                'population' => 793139,
                'postal_codes' => ['LS1 1AA', 'LS2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Lidsas', 'description' => 'Pramonės centras'],
                    'en' => ['name' => 'Leeds', 'description' => 'Industrial center'],
                ],
            ],
            [
                'name' => 'Sheffield',
                'code' => 'GB-ENG-SHE',
                'region_id' => $englandRegion?->id,
                'latitude' => 53.3811,
                'longitude' => -1.4701,
                'population' => 582506,
                'postal_codes' => ['S1 1AA', 'S2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Šefildas', 'description' => 'Plieno pramonės centras'],
                    'en' => ['name' => 'Sheffield', 'description' => 'Steel industry center'],
                ],
            ],
            [
                'name' => 'Bristol',
                'code' => 'GB-ENG-BRI',
                'region_id' => $englandRegion?->id,
                'latitude' => 51.4545,
                'longitude' => -2.5879,
                'population' => 463400,
                'postal_codes' => ['BS1 1AA', 'BS2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Bristolis', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Bristol', 'description' => 'Port city'],
                ],
            ],
            [
                'name' => 'Newcastle',
                'code' => 'GB-ENG-NEW',
                'region_id' => $englandRegion?->id,
                'latitude' => 54.9783,
                'longitude' => -1.6178,
                'population' => 300196,
                'postal_codes' => ['NE1 1AA', 'NE2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Niukaslas', 'description' => 'Šiaurės Anglijos centras'],
                    'en' => ['name' => 'Newcastle', 'description' => 'Center of Northern England'],
                ],
            ],
            [
                'name' => 'Nottingham',
                'code' => 'GB-ENG-NOT',
                'region_id' => $englandRegion?->id,
                'latitude' => 52.9548,
                'longitude' => -1.1581,
                'population' => 321500,
                'postal_codes' => ['NG1 1AA', 'NG2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Notingamas', 'description' => 'Robin Gudas miestas'],
                    'en' => ['name' => 'Nottingham', 'description' => 'Robin Hood city'],
                ],
            ],
            [
                'name' => 'Leicester',
                'code' => 'GB-ENG-LEI',
                'region_id' => $englandRegion?->id,
                'latitude' => 52.6369,
                'longitude' => -1.1398,
                'population' => 329839,
                'postal_codes' => ['LE1 1AA', 'LE2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Lečesteris', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Leicester', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Coventry',
                'code' => 'GB-ENG-COV',
                'region_id' => $englandRegion?->id,
                'latitude' => 52.4068,
                'longitude' => -1.5197,
                'population' => 325949,
                'postal_codes' => ['CV1 1AA', 'CV2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Koventris', 'description' => 'Automobilių pramonės centras'],
                    'en' => ['name' => 'Coventry', 'description' => 'Automotive industry center'],
                ],
            ],
            [
                'name' => 'Bradford',
                'code' => 'GB-ENG-BRA',
                'region_id' => $englandRegion?->id,
                'latitude' => 53.796,
                'longitude' => -1.7594,
                'population' => 537173,
                'postal_codes' => ['BD1 1AA', 'BD2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Bredfordas', 'description' => 'Tekstilės pramonės centras'],
                    'en' => ['name' => 'Bradford', 'description' => 'Textile industry center'],
                ],
            ],
            [
                'name' => 'Plymouth',
                'code' => 'GB-ENG-PLY',
                'region_id' => $englandRegion?->id,
                'latitude' => 50.3755,
                'longitude' => -4.1427,
                'population' => 264200,
                'postal_codes' => ['PL1 1AA', 'PL2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Plimutas', 'description' => 'Karinis uostas'],
                    'en' => ['name' => 'Plymouth', 'description' => 'Naval port'],
                ],
            ],
            [
                'name' => 'Southampton',
                'code' => 'GB-ENG-SOU',
                'region_id' => $englandRegion?->id,
                'latitude' => 50.9097,
                'longitude' => -1.4044,
                'population' => 253651,
                'postal_codes' => ['SO14 1AA', 'SO15 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Sautamtonas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Southampton', 'description' => 'Port city'],
                ],
            ],
            [
                'name' => 'Portsmouth',
                'code' => 'GB-ENG-POR',
                'region_id' => $englandRegion?->id,
                'latitude' => 50.8198,
                'longitude' => -1.088,
                'population' => 238137,
                'postal_codes' => ['PO1 1AA', 'PO2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Portsmutas', 'description' => 'Karinis uostas'],
                    'en' => ['name' => 'Portsmouth', 'description' => 'Naval port'],
                ],
            ],
            [
                'name' => 'Brighton',
                'code' => 'GB-ENG-BRI',
                'region_id' => $englandRegion?->id,
                'latitude' => 50.8225,
                'longitude' => -0.1372,
                'population' => 290395,
                'postal_codes' => ['BN1 1AA', 'BN2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Braitonas', 'description' => 'Kurortinis miestas'],
                    'en' => ['name' => 'Brighton', 'description' => 'Resort city'],
                ],
            ],
            [
                'name' => 'Oxford',
                'code' => 'GB-ENG-OXF',
                'region_id' => $englandRegion?->id,
                'latitude' => 51.752,
                'longitude' => -1.2577,
                'population' => 152000,
                'postal_codes' => ['OX1 1AA', 'OX2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Oksfordas', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Oxford', 'description' => 'University city'],
                ],
            ],
            [
                'name' => 'Cambridge',
                'code' => 'GB-ENG-CAM',
                'region_id' => $englandRegion?->id,
                'latitude' => 52.2053,
                'longitude' => 0.1218,
                'population' => 145818,
                'postal_codes' => ['CB1 1AA', 'CB2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Kembridžas', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Cambridge', 'description' => 'University city'],
                ],
            ],
            [
                'name' => 'York',
                'code' => 'GB-ENG-YOR',
                'region_id' => $englandRegion?->id,
                'latitude' => 53.959,
                'longitude' => -1.0815,
                'population' => 208200,
                'postal_codes' => ['YO1 1AA', 'YO2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Jorkas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'York', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Bath',
                'code' => 'GB-ENG-BAT',
                'region_id' => $englandRegion?->id,
                'latitude' => 51.3811,
                'longitude' => -2.359,
                'population' => 94782,
                'postal_codes' => ['BA1 1AA', 'BA2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Batas', 'description' => 'UNESCO miestas'],
                    'en' => ['name' => 'Bath', 'description' => 'UNESCO city'],
                ],
            ],
            [
                'name' => 'Canterbury',
                'code' => 'GB-ENG-CAN',
                'region_id' => $englandRegion?->id,
                'latitude' => 51.2802,
                'longitude' => 1.0789,
                'population' => 55100,
                'postal_codes' => ['CT1 1AA', 'CT2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Kenterberis', 'description' => 'Arkivyskupijos miestas'],
                    'en' => ['name' => 'Canterbury', 'description' => 'Archbishopric city'],
                ],
            ],
            [
                'name' => 'Norwich',
                'code' => 'GB-ENG-NOR',
                'region_id' => $englandRegion?->id,
                'latitude' => 52.6309,
                'longitude' => 1.2974,
                'population' => 195000,
                'postal_codes' => ['NR1 1AA', 'NR2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Norvičas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Norwich', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Exeter',
                'code' => 'GB-ENG-EXE',
                'region_id' => $englandRegion?->id,
                'latitude' => 50.7184,
                'longitude' => -3.5339,
                'population' => 130428,
                'postal_codes' => ['EX1 1AA', 'EX2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Ekseteris', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Exeter', 'description' => 'University city'],
                ],
            ],
            [
                'name' => 'Gloucester',
                'code' => 'GB-ENG-GLO',
                'region_id' => $englandRegion?->id,
                'latitude' => 51.8642,
                'longitude' => -2.2381,
                'population' => 128488,
                'postal_codes' => ['GL1 1AA', 'GL2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Glosteris', 'description' => 'Katedros miestas'],
                    'en' => ['name' => 'Gloucester', 'description' => 'Cathedral city'],
                ],
            ],
            // Scotland
            [
                'name' => 'Edinburgh',
                'code' => 'GB-SCT-EDI',
                'region_id' => $scotlandRegion?->id,
                'latitude' => 55.9533,
                'longitude' => -3.1883,
                'population' => 506520,
                'postal_codes' => ['EH1 1AA', 'EH2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Edinburgas', 'description' => 'Škotijos sostinė'],
                    'en' => ['name' => 'Edinburgh', 'description' => 'Capital of Scotland'],
                ],
            ],
            [
                'name' => 'Glasgow',
                'code' => 'GB-SCT-GLA',
                'region_id' => $scotlandRegion?->id,
                'latitude' => 55.8642,
                'longitude' => -4.2518,
                'population' => 635640,
                'postal_codes' => ['G1 1AA', 'G2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Glazgas', 'description' => 'Didžiausias Škotijos miestas'],
                    'en' => ['name' => 'Glasgow', 'description' => 'Largest city in Scotland'],
                ],
            ],
            [
                'name' => 'Aberdeen',
                'code' => 'GB-SCT-ABE',
                'region_id' => $scotlandRegion?->id,
                'latitude' => 57.1497,
                'longitude' => -2.0943,
                'population' => 200680,
                'postal_codes' => ['AB10 1AA', 'AB11 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Aberdynas', 'description' => 'Naftos pramonės centras'],
                    'en' => ['name' => 'Aberdeen', 'description' => 'Oil industry center'],
                ],
            ],
            [
                'name' => 'Dundee',
                'code' => 'GB-SCT-DUN',
                'region_id' => $scotlandRegion?->id,
                'latitude' => 56.462,
                'longitude' => -2.9707,
                'population' => 148270,
                'postal_codes' => ['DD1 1AA', 'DD2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Dandis', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Dundee', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Stirling',
                'code' => 'GB-SCT-STI',
                'region_id' => $scotlandRegion?->id,
                'latitude' => 56.1165,
                'longitude' => -3.9369,
                'population' => 37010,
                'postal_codes' => ['FK7 1AA', 'FK8 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Sterlingas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Stirling', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Inverness',
                'code' => 'GB-SCT-INV',
                'region_id' => $scotlandRegion?->id,
                'latitude' => 57.4778,
                'longitude' => -4.2247,
                'population' => 47000,
                'postal_codes' => ['IV1 1AA', 'IV2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Invernesas', 'description' => 'Šiaurės Škotijos centras'],
                    'en' => ['name' => 'Inverness', 'description' => 'Center of Northern Scotland'],
                ],
            ],
            // Wales
            [
                'name' => 'Cardiff',
                'code' => 'GB-WLS-CAR',
                'region_id' => $walesRegion?->id,
                'latitude' => 51.4816,
                'longitude' => -3.1791,
                'population' => 366903,
                'postal_codes' => ['CF10 1AA', 'CF11 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Kardifas', 'description' => 'Velso sostinė'],
                    'en' => ['name' => 'Cardiff', 'description' => 'Capital of Wales'],
                ],
            ],
            [
                'name' => 'Swansea',
                'code' => 'GB-WLS-SWA',
                'region_id' => $walesRegion?->id,
                'latitude' => 51.6214,
                'longitude' => -3.9436,
                'population' => 245508,
                'postal_codes' => ['SA1 1AA', 'SA2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Svonsis', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Swansea', 'description' => 'Port city'],
                ],
            ],
            [
                'name' => 'Newport',
                'code' => 'GB-WLS-NEW',
                'region_id' => $walesRegion?->id,
                'latitude' => 51.5889,
                'longitude' => -2.9977,
                'population' => 151500,
                'postal_codes' => ['NP19 1AA', 'NP20 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Niuportas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Newport', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Wrexham',
                'code' => 'GB-WLS-WRE',
                'region_id' => $walesRegion?->id,
                'latitude' => 53.0466,
                'longitude' => -2.9926,
                'population' => 65092,
                'postal_codes' => ['LL11 1AA', 'LL12 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Reksamas', 'description' => 'Šiaurės Velso centras'],
                    'en' => ['name' => 'Wrexham', 'description' => 'Center of Northern Wales'],
                ],
            ],
            // Northern Ireland
            [
                'name' => 'Belfast',
                'code' => 'GB-NIR-BEL',
                'region_id' => $northernIrelandRegion?->id,
                'latitude' => 54.5973,
                'longitude' => -5.9301,
                'population' => 341877,
                'postal_codes' => ['BT1 1AA', 'BT2 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Belfastas', 'description' => 'Šiaurės Airijos sostinė'],
                    'en' => ['name' => 'Belfast', 'description' => 'Capital of Northern Ireland'],
                ],
            ],
            [
                'name' => 'Derry',
                'code' => 'GB-NIR-DER',
                'region_id' => $northernIrelandRegion?->id,
                'latitude' => 54.9966,
                'longitude' => -7.3086,
                'population' => 85016,
                'postal_codes' => ['BT47 1AA', 'BT48 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Deris', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Derry', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Lisburn',
                'code' => 'GB-NIR-LIS',
                'region_id' => $northernIrelandRegion?->id,
                'latitude' => 54.5124,
                'longitude' => -6.0319,
                'population' => 45030,
                'postal_codes' => ['BT27 1AA', 'BT28 2BB'],
                'translations' => [
                    'lt' => ['name' => 'Lisburnas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Lisburn', 'description' => 'Industrial city'],
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
                    'country_id' => $uk->id,
                    'zone_id' => $ukZone?->id ?? $euZone?->id,
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
