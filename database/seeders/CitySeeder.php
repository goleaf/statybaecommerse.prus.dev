<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Translations\CityTranslation;
use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        // Get countries, zones and regions
        $lithuania = Country::where('cca2', 'LT')->first();
        $latvia = Country::where('cca2', 'LV')->first();
        $estonia = Country::where('cca2', 'EE')->first();
        $poland = Country::where('cca2', 'PL')->first();
        $germany = Country::where('cca2', 'DE')->first();
        $france = Country::where('cca2', 'FR')->first();
        $spain = Country::where('cca2', 'ES')->first();
        $italy = Country::where('cca2', 'IT')->first();
        $usa = Country::where('cca2', 'US')->first();
        $canada = Country::where('cca2', 'CA')->first();
        $uk = Country::where('cca2', 'GB')->first();

        $euZone = Zone::where('code', 'EU')->first();
        $naZone = Zone::where('code', 'NA')->first();
        $ukZone = Zone::where('code', 'UK')->first();
        $ltZone = Zone::where('code', 'LT')->first();

        // Get regions
        $vilniusRegion = Region::where('code', 'LT-VL')->first();
        $kaunasRegion = Region::where('code', 'LT-KA')->first();
        $klaipedaRegion = Region::where('code', 'LT-KL')->first();
        $siauliaiRegion = Region::where('code', 'LT-SA')->first();
        $panevezysRegion = Region::where('code', 'LT-PN')->first();
        $alytusRegion = Region::where('code', 'LT-AL')->first();
        $marijampoleRegion = Region::where('code', 'LT-MR')->first();
        $taurageRegion = Region::where('code', 'LT-TA')->first();
        $telsiaiRegion = Region::where('code', 'LT-TE')->first();
        $utenaRegion = Region::where('code', 'LT-UT')->first();

        $rigaRegion = Region::where('code', 'LV-RI')->first();
        $tallinnRegion = Region::where('code', 'EE-37')->first();
        $warsawRegion = Region::where('code', 'PL-MZ')->first();
        $krakowRegion = Region::where('code', 'PL-MA')->first();
        $bavariaRegion = Region::where('code', 'DE-BY')->first();
        $nrwRegion = Region::where('code', 'DE-NW')->first();
        $parisRegion = Region::where('code', 'FR-IDF')->first();
        $marseilleRegion = Region::where('code', 'FR-PAC')->first();
        $madridRegion = Region::where('code', 'ES-MD')->first();
        $barcelonaRegion = Region::where('code', 'ES-CT')->first();
        $milanRegion = Region::where('code', 'IT-LO')->first();
        $romeRegion = Region::where('code', 'IT-LA')->first();
        $californiaRegion = Region::where('code', 'US-CA')->first();
        $newyorkRegion = Region::where('code', 'US-NY')->first();
        $texasRegion = Region::where('code', 'US-TX')->first();
        $ontarioRegion = Region::where('code', 'CA-ON')->first();
        $quebecRegion = Region::where('code', 'CA-QC')->first();
        $englandRegion = Region::where('code', 'GB-ENG')->first();
        $scotlandRegion = Region::where('code', 'GB-SCT')->first();

        $locales = $this->supportedLocales();

        $cities = [
            // Lithuania cities
            [
                'name' => ['lt' => 'Vilnius', 'en' => 'Vilnius'],
                'slug' => 'vilnius',
                'code' => 'LT-VLN',
                'description' => ['lt' => 'Lietuvos sostinė', 'en' => 'Capital of Lithuania'],
                'is_capital' => true,
                'country_id' => $lithuania?->id,
                'zone_id' => $ltZone?->id,
                'region_id' => $vilniusRegion?->id,
                'level' => 0,
                'latitude' => 54.6872,
                'longitude' => 25.2797,
                'population' => 588412,
                'postal_codes' => ['01001', '01002', '01003', '01004', '01005'],
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Kaunas', 'en' => 'Kaunas'],
                'slug' => 'kaunas',
                'code' => 'LT-KAU',
                'description' => ['lt' => 'Antras pagal dydį Lietuvos miestas', 'en' => 'Second largest city in Lithuania'],
                'is_capital' => false,
                'country_id' => $lithuania?->id,
                'zone_id' => $ltZone?->id,
                'region_id' => $kaunasRegion?->id,
                'level' => 0,
                'latitude' => 54.8985,
                'longitude' => 23.9036,
                'population' => 315993,
                'postal_codes' => ['44001', '44002', '44003', '44004', '44005'],
                'sort_order' => 2,
            ],
            [
                'name' => ['lt' => 'Klaipėda', 'en' => 'Klaipėda'],
                'slug' => 'klaipeda',
                'code' => 'LT-KLP',
                'description' => ['lt' => 'Pagrindinis Lietuvos uostamiestis', 'en' => 'Main port city of Lithuania'],
                'is_capital' => false,
                'country_id' => $lithuania?->id,
                'zone_id' => $ltZone?->id,
                'region_id' => $klaipedaRegion?->id,
                'level' => 0,
                'latitude' => 55.7033,
                'longitude' => 21.1443,
                'population' => 152008,
                'postal_codes' => ['91001', '91002', '91003', '91004', '91005'],
                'sort_order' => 3,
            ],
            [
                'name' => ['lt' => 'Šiauliai', 'en' => 'Šiauliai'],
                'slug' => 'siauliai',
                'code' => 'LT-SIA',
                'description' => ['lt' => 'Šiaurės Lietuvos centras', 'en' => 'Center of Northern Lithuania'],
                'is_capital' => false,
                'country_id' => $lithuania?->id,
                'zone_id' => $ltZone?->id,
                'region_id' => $siauliaiRegion?->id,
                'level' => 0,
                'latitude' => 55.9333,
                'longitude' => 23.3167,
                'population' => 107086,
                'postal_codes' => ['76001', '76002', '76003', '76004', '76005'],
                'sort_order' => 4,
            ],
            [
                'name' => ['lt' => 'Panevėžys', 'en' => 'Panevėžys'],
                'slug' => 'panevezys',
                'code' => 'LT-PNV',
                'description' => ['lt' => 'Aukštaitijos regiono centras', 'en' => 'Center of Aukštaitija region'],
                'is_capital' => false,
                'country_id' => $lithuania?->id,
                'zone_id' => $ltZone?->id,
                'region_id' => $panevezysRegion?->id,
                'level' => 0,
                'latitude' => 55.7333,
                'longitude' => 24.35,
                'population' => 87048,
                'postal_codes' => ['35001', '35002', '35003', '35004', '35005'],
                'sort_order' => 5,
            ],
            [
                'name' => ['lt' => 'Alytus', 'en' => 'Alytus'],
                'slug' => 'alytus',
                'code' => 'LT-ALT',
                'description' => ['lt' => 'Dzūkijos regiono centras', 'en' => 'Center of Dzūkija region'],
                'is_capital' => false,
                'country_id' => $lithuania?->id,
                'zone_id' => $ltZone?->id,
                'region_id' => $alytusRegion?->id,
                'level' => 0,
                'latitude' => 54.4,
                'longitude' => 24.05,
                'population' => 52000,
                'postal_codes' => ['62001', '62002', '62003', '62004', '62005'],
                'sort_order' => 6,
            ],
            [
                'name' => ['lt' => 'Marijampolė', 'en' => 'Marijampolė'],
                'slug' => 'marijampole',
                'code' => 'LT-MRJ',
                'description' => ['lt' => 'Suvalkijos regiono centras', 'en' => 'Center of Suvalkija region'],
                'is_capital' => false,
                'country_id' => $lithuania?->id,
                'zone_id' => $ltZone?->id,
                'region_id' => $marijampoleRegion?->id,
                'level' => 0,
                'latitude' => 54.5667,
                'longitude' => 23.35,
                'population' => 36000,
                'postal_codes' => ['68001', '68002', '68003', '68004', '68005'],
                'sort_order' => 7,
            ],
            [
                'name' => ['lt' => 'Tauragė', 'en' => 'Tauragė'],
                'slug' => 'taurage',
                'code' => 'LT-TRG',
                'description' => ['lt' => 'Žemaitijos regiono miestas', 'en' => 'City in Žemaitija region'],
                'is_capital' => false,
                'country_id' => $lithuania?->id,
                'zone_id' => $ltZone?->id,
                'region_id' => $taurageRegion?->id,
                'level' => 0,
                'latitude' => 55.25,
                'longitude' => 22.2833,
                'population' => 22000,
                'postal_codes' => ['72001', '72002', '72003', '72004', '72005'],
                'sort_order' => 8,
            ],
            [
                'name' => ['lt' => 'Telšiai', 'en' => 'Telšiai'],
                'slug' => 'telsiai',
                'code' => 'LT-TLS',
                'description' => ['lt' => 'Žemaitijos regiono centras', 'en' => 'Center of Žemaitija region'],
                'is_capital' => false,
                'country_id' => $lithuania?->id,
                'zone_id' => $ltZone?->id,
                'region_id' => $telsiaiRegion?->id,
                'level' => 0,
                'latitude' => 55.9833,
                'longitude' => 22.25,
                'population' => 25000,
                'postal_codes' => ['87001', '87002', '87003', '87004', '87005'],
                'sort_order' => 9,
            ],
            [
                'name' => ['lt' => 'Utena', 'en' => 'Utena'],
                'slug' => 'utena',
                'code' => 'LT-UTN',
                'description' => ['lt' => 'Aukštaitijos regiono miestas', 'en' => 'City in Aukštaitija region'],
                'is_capital' => false,
                'country_id' => $lithuania?->id,
                'zone_id' => $ltZone?->id,
                'region_id' => $utenaRegion?->id,
                'level' => 0,
                'latitude' => 55.5,
                'longitude' => 25.6,
                'population' => 28000,
                'postal_codes' => ['28001', '28002', '28003', '28004', '28005'],
                'sort_order' => 10,
            ],
            // Latvia cities
            [
                'name' => ['lt' => 'Ryga', 'en' => 'Riga'],
                'slug' => 'ryga',
                'code' => 'LV-RIG',
                'description' => ['lt' => 'Latvijos sostinė', 'en' => 'Capital of Latvia'],
                'is_capital' => true,
                'country_id' => $latvia?->id,
                'zone_id' => $euZone?->id,
                'region_id' => $rigaRegion?->id,
                'level' => 0,
                'latitude' => 56.9496,
                'longitude' => 24.1052,
                'population' => 605802,
                'postal_codes' => ['LV-1001', 'LV-1002', 'LV-1003', 'LV-1004', 'LV-1005'],
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Daugpilis', 'en' => 'Daugavpils'],
                'slug' => 'daugpilis',
                'code' => 'LV-DGP',
                'description' => ['lt' => 'Antras pagal dydį Latvijos miestas', 'en' => 'Second largest city in Latvia'],
                'is_capital' => false,
                'country_id' => $latvia?->id,
                'zone_id' => $euZone?->id,
                'region_id' => $rigaRegion?->id,
                'level' => 0,
                'latitude' => 55.8756,
                'longitude' => 26.5364,
                'population' => 82046,
                'postal_codes' => ['LV-5401', 'LV-5402', 'LV-5403', 'LV-5404', 'LV-5405'],
                'sort_order' => 2,
            ],
            // Estonia cities
            [
                'name' => ['lt' => 'Talinas', 'en' => 'Tallinn'],
                'slug' => 'talinas',
                'code' => 'EE-TLL',
                'description' => ['lt' => 'Estijos sostinė', 'en' => 'Capital of Estonia'],
                'is_capital' => true,
                'country_id' => $estonia?->id,
                'zone_id' => $euZone?->id,
                'region_id' => $tallinnRegion?->id,
                'level' => 0,
                'latitude' => 59.437,
                'longitude' => 24.7536,
                'population' => 437619,
                'postal_codes' => ['10111', '10112', '10113', '10114', '10115'],
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Tartu', 'en' => 'Tartu'],
                'slug' => 'tartu',
                'code' => 'EE-TRT',
                'description' => ['lt' => 'Estijos universiteto miestas', 'en' => 'University city of Estonia'],
                'is_capital' => false,
                'country_id' => $estonia?->id,
                'zone_id' => $euZone?->id,
                'region_id' => $tallinnRegion?->id,
                'level' => 0,
                'latitude' => 58.378,
                'longitude' => 26.729,
                'population' => 91407,
                'postal_codes' => ['50050', '50051', '50052', '50053', '50054'],
                'sort_order' => 2,
            ],
            // Poland cities
            [
                'name' => ['lt' => 'Varšuva', 'en' => 'Warsaw'],
                'slug' => 'varsuva',
                'code' => 'PL-WAW',
                'description' => ['lt' => 'Lenkijos sostinė', 'en' => 'Capital of Poland'],
                'is_capital' => true,
                'country_id' => $poland?->id,
                'zone_id' => $euZone?->id,
                'region_id' => $warsawRegion?->id,
                'level' => 0,
                'latitude' => 52.2297,
                'longitude' => 21.0122,
                'population' => 1793579,
                'postal_codes' => ['00-001', '00-002', '00-003', '00-004', '00-005'],
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Krokuva', 'en' => 'Krakow'],
                'slug' => 'krokuva',
                'code' => 'PL-KRK',
                'description' => ['lt' => 'Istorinis Lenkijos miestas', 'en' => 'Historic city of Poland'],
                'is_capital' => false,
                'country_id' => $poland?->id,
                'zone_id' => $euZone?->id,
                'region_id' => $krakowRegion?->id,
                'level' => 0,
                'latitude' => 50.0755,
                'longitude' => 19.9445,
                'population' => 779115,
                'postal_codes' => ['30-001', '30-002', '30-003', '30-004', '30-005'],
                'sort_order' => 2,
            ],
            // Germany cities
            [
                'name' => ['lt' => 'Berlynas', 'en' => 'Berlin'],
                'slug' => 'berlynas',
                'code' => 'DE-BER',
                'description' => ['lt' => 'Vokietijos sostinė', 'en' => 'Capital of Germany'],
                'is_capital' => true,
                'country_id' => $germany?->id,
                'zone_id' => $euZone?->id,
                'region_id' => $bavariaRegion?->id,
                'level' => 0,
                'latitude' => 52.52,
                'longitude' => 13.405,
                'population' => 3669491,
                'postal_codes' => ['10115', '10117', '10119', '10178', '10179'],
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Miunchenas', 'en' => 'Munich'],
                'slug' => 'miunchenas',
                'code' => 'DE-MUC',
                'description' => ['lt' => 'Bavarijos sostinė', 'en' => 'Capital of Bavaria'],
                'is_capital' => false,
                'country_id' => $germany?->id,
                'zone_id' => $euZone?->id,
                'region_id' => $bavariaRegion?->id,
                'level' => 0,
                'latitude' => 48.1351,
                'longitude' => 11.582,
                'population' => 1484226,
                'postal_codes' => ['80331', '80333', '80335', '80336', '80337'],
                'sort_order' => 2,
            ],
            // France cities
            [
                'name' => ['lt' => 'Paryžius', 'en' => 'Paris'],
                'slug' => 'paryzius',
                'code' => 'FR-PAR',
                'description' => ['lt' => 'Prancūzijos sostinė', 'en' => 'Capital of France'],
                'is_capital' => true,
                'country_id' => $france?->id,
                'zone_id' => $euZone?->id,
                'region_id' => $parisRegion?->id,
                'level' => 0,
                'latitude' => 48.8566,
                'longitude' => 2.3522,
                'population' => 2161000,
                'postal_codes' => ['75001', '75002', '75003', '75004', '75005'],
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Marselis', 'en' => 'Marseille'],
                'slug' => 'marselis',
                'code' => 'FR-MRS',
                'description' => ['lt' => 'Antras pagal dydį Prancūzijos miestas', 'en' => 'Second largest city in France'],
                'is_capital' => false,
                'country_id' => $france?->id,
                'zone_id' => $euZone?->id,
                'region_id' => $marseilleRegion?->id,
                'level' => 0,
                'latitude' => 43.2965,
                'longitude' => 5.3698,
                'population' => 870018,
                'postal_codes' => ['13001', '13002', '13003', '13004', '13005'],
                'sort_order' => 2,
            ],
            // Spain cities
            [
                'name' => ['lt' => 'Madridas', 'en' => 'Madrid'],
                'slug' => 'madridas',
                'code' => 'ES-MAD',
                'description' => ['lt' => 'Ispanijos sostinė', 'en' => 'Capital of Spain'],
                'is_capital' => true,
                'country_id' => $spain?->id,
                'zone_id' => $euZone?->id,
                'region_id' => $madridRegion?->id,
                'level' => 0,
                'latitude' => 40.4168,
                'longitude' => -3.7038,
                'population' => 3223334,
                'postal_codes' => ['28001', '28002', '28003', '28004', '28005'],
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Barselona', 'en' => 'Barcelona'],
                'slug' => 'barselona',
                'code' => 'ES-BCN',
                'description' => ['lt' => 'Katalonijos sostinė', 'en' => 'Capital of Catalonia'],
                'is_capital' => false,
                'country_id' => $spain?->id,
                'zone_id' => $euZone?->id,
                'region_id' => $barcelonaRegion?->id,
                'level' => 0,
                'latitude' => 41.3851,
                'longitude' => 2.1734,
                'population' => 1636762,
                'postal_codes' => ['08001', '08002', '08003', '08004', '08005'],
                'sort_order' => 2,
            ],
            // Italy cities
            [
                'name' => ['lt' => 'Roma', 'en' => 'Rome'],
                'slug' => 'roma',
                'code' => 'IT-ROM',
                'description' => ['lt' => 'Italijos sostinė', 'en' => 'Capital of Italy'],
                'is_capital' => true,
                'country_id' => $italy?->id,
                'zone_id' => $euZone?->id,
                'region_id' => $romeRegion?->id,
                'level' => 0,
                'latitude' => 41.9028,
                'longitude' => 12.4964,
                'population' => 2872800,
                'postal_codes' => ['00118', '00119', '00120', '00121', '00122'],
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Milanas', 'en' => 'Milan'],
                'slug' => 'milanas',
                'code' => 'IT-MIL',
                'description' => ['lt' => 'Lombardijos sostinė', 'en' => 'Capital of Lombardy'],
                'is_capital' => false,
                'country_id' => $italy?->id,
                'zone_id' => $euZone?->id,
                'region_id' => $milanRegion?->id,
                'level' => 0,
                'latitude' => 45.4642,
                'longitude' => 9.19,
                'population' => 1371498,
                'postal_codes' => ['20121', '20122', '20123', '20124', '20125'],
                'sort_order' => 2,
            ],
            // USA cities
            [
                'name' => ['lt' => 'Niujorkas', 'en' => 'New York'],
                'slug' => 'niujorkas',
                'code' => 'US-NYC',
                'description' => ['lt' => 'Didžiausias JAV miestas', 'en' => 'Largest city in USA'],
                'is_capital' => false,
                'country_id' => $usa?->id,
                'zone_id' => $naZone?->id,
                'region_id' => $newyorkRegion?->id,
                'level' => 0,
                'latitude' => 40.7128,
                'longitude' => -74.006,
                'population' => 8336817,
                'postal_codes' => ['10001', '10002', '10003', '10004', '10005'],
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Los Andželas', 'en' => 'Los Angeles'],
                'slug' => 'los-andzelas',
                'code' => 'US-LAX',
                'description' => ['lt' => 'Kalifornijos didžiausias miestas', 'en' => 'Largest city in California'],
                'is_capital' => false,
                'country_id' => $usa?->id,
                'zone_id' => $naZone?->id,
                'region_id' => $californiaRegion?->id,
                'level' => 0,
                'latitude' => 34.0522,
                'longitude' => -118.2437,
                'population' => 3971883,
                'postal_codes' => ['90001', '90002', '90003', '90004', '90005'],
                'sort_order' => 2,
            ],
            [
                'name' => ['lt' => 'Čikaga', 'en' => 'Chicago'],
                'slug' => 'cikaga',
                'code' => 'US-CHI',
                'description' => ['lt' => 'Ilinojaus didžiausias miestas', 'en' => 'Largest city in Illinois'],
                'is_capital' => false,
                'country_id' => $usa?->id,
                'zone_id' => $naZone?->id,
                'region_id' => $newyorkRegion?->id,
                'level' => 0,
                'latitude' => 41.8781,
                'longitude' => -87.6298,
                'population' => 2693976,
                'postal_codes' => ['60601', '60602', '60603', '60604', '60605'],
                'sort_order' => 3,
            ],
            // Canada cities
            [
                'name' => ['lt' => 'Torontas', 'en' => 'Toronto'],
                'slug' => 'torontas',
                'code' => 'CA-TOR',
                'description' => ['lt' => 'Kanados didžiausias miestas', 'en' => 'Largest city in Canada'],
                'is_capital' => false,
                'country_id' => $canada?->id,
                'zone_id' => $naZone?->id,
                'region_id' => $ontarioRegion?->id,
                'level' => 0,
                'latitude' => 43.6532,
                'longitude' => -79.3832,
                'population' => 2930000,
                'postal_codes' => ['M5A', 'M5B', 'M5C', 'M5D', 'M5E'],
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Montrealis', 'en' => 'Montreal'],
                'slug' => 'montrealis',
                'code' => 'CA-MTL',
                'description' => ['lt' => 'Kvebeko didžiausias miestas', 'en' => 'Largest city in Quebec'],
                'is_capital' => false,
                'country_id' => $canada?->id,
                'zone_id' => $naZone?->id,
                'region_id' => $quebecRegion?->id,
                'level' => 0,
                'latitude' => 45.5017,
                'longitude' => -73.5673,
                'population' => 1780000,
                'postal_codes' => ['H1A', 'H1B', 'H1C', 'H1D', 'H1E'],
                'sort_order' => 2,
            ],
            // UK cities
            [
                'name' => ['lt' => 'Londonas', 'en' => 'London'],
                'slug' => 'londonas',
                'code' => 'GB-LON',
                'description' => ['lt' => 'Jungtinės Karalystės sostinė', 'en' => 'Capital of United Kingdom'],
                'is_capital' => true,
                'country_id' => $uk?->id,
                'zone_id' => $ukZone?->id,
                'region_id' => $englandRegion?->id,
                'level' => 0,
                'latitude' => 51.5074,
                'longitude' => -0.1278,
                'population' => 8982000,
                'postal_codes' => ['SW1A', 'SW1B', 'SW1C', 'SW1D', 'SW1E'],
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Edinburgas', 'en' => 'Edinburgh'],
                'slug' => 'edinburgas',
                'code' => 'GB-EDI',
                'description' => ['lt' => 'Škotijos sostinė', 'en' => 'Capital of Scotland'],
                'is_capital' => false,
                'country_id' => $uk?->id,
                'zone_id' => $ukZone?->id,
                'region_id' => $scotlandRegion?->id,
                'level' => 0,
                'latitude' => 55.9533,
                'longitude' => -3.1883,
                'population' => 548560,
                'postal_codes' => ['EH1', 'EH2', 'EH3', 'EH4', 'EH5'],
                'sort_order' => 2,
            ],
        ];

        foreach ($cities as $cityData) {
            // Handle both old format (name/description arrays) and new format (translations)
            $translations = [];
            if (isset($cityData['translations'])) {
                $translations = $cityData['translations'];
                unset($cityData['translations']);
                $defaultName = $translations['en']['name'] ?? $cityData['code'];
            } elseif (isset($cityData['name']) && is_array($cityData['name'])) {
                $translations = [
                    'lt' => [
                        'name' => $cityData['name']['lt'] ?? 'City',
                        'description' => $cityData['description']['lt'] ?? '',
                    ],
                    'en' => [
                        'name' => $cityData['name']['en'] ?? 'City',
                        'description' => $cityData['description']['en'] ?? '',
                    ],
                ];
                $defaultName = $cityData['name']['en'] ?? $cityData['code'];
                unset($cityData['name'], $cityData['description']);
            } else {
                $defaultName = $cityData['code'];
                $translations = [];
            }

            // Check if city already exists to maintain idempotency
            $existingCity = City::where('code', $cityData['code'])->first();

            if ($existingCity) {
                $existingCity->update(array_merge($cityData, [
                    'name' => $defaultName,
                    'is_enabled' => true,
                    'is_default' => false,
                ]));
                $city = $existingCity;
            } else {
                // Use factory to create city with relationships
                $city = City::factory()
                    ->state(array_merge($cityData, [
                        'name' => $defaultName,
                        'is_enabled' => true,
                        'is_default' => false,
                    ]))
                    ->create();
            }

            // Create translations using factory relationships
            foreach ($locales as $locale) {
                $translationData = $translations[$locale] ?? [];

                $existingTranslation = CityTranslation::where([
                    'city_id' => $city->id,
                    'locale' => $locale,
                ])->first();

                if ($existingTranslation) {
                    $existingTranslation->update([
                        'name' => $translationData['name'] ?? 'City',
                        'description' => $translationData['description'] ?? '',
                    ]);
                } else {
                    CityTranslation::factory()
                        ->for($city)
                        ->state([
                            'locale' => $locale,
                            'name' => $translationData['name'] ?? 'City',
                            'description' => $translationData['description'] ?? '',
                        ])
                        ->create();
                }
            }

            $cityName = $translations['en']['name'] ?? $translations['lt']['name'] ?? 'City';
            $this->command->info("Upserted city: {$cityData['code']} - {$cityName}");
        }

        $this->command->info('City seeding completed successfully with translations (locales: ' . implode(',', $locales) . ')!');
    }

    private function supportedLocales(): array
    {
        return collect(explode(',', (string) config('app.supported_locales', 'lt,en')))
            ->map(fn($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}
