<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\Translations\CityTranslation;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class ItalyCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $italy = Country::where('cca2', 'IT')->first();
        $euZone = Zone::where('code', 'EU')->first();

        // Get regions
        $lazioRegion = Region::where('code', 'IT-LAZ')->first();
        $lombardyRegion = Region::where('code', 'IT-LOM')->first();
        $campaniaRegion = Region::where('code', 'IT-CAM')->first();
        $sicilyRegion = Region::where('code', 'IT-SIC')->first();
        $venetoRegion = Region::where('code', 'IT-VEN')->first();
        $emiliaRomagnaRegion = Region::where('code', 'IT-EMR')->first();
        $piedmontRegion = Region::where('code', 'IT-PIE')->first();
        $apuliaRegion = Region::where('code', 'IT-PUG')->first();
        $tuscanyRegion = Region::where('code', 'IT-TOS')->first();
        $calabriaRegion = Region::where('code', 'IT-CAL')->first();
        $sardiniaRegion = Region::where('code', 'IT-SAR')->first();
        $liguriaRegion = Region::where('code', 'IT-LIG')->first();
        $marcheRegion = Region::where('code', 'IT-MAR')->first();
        $abruzzoRegion = Region::where('code', 'IT-ABR')->first();
        $friuliVeneziaGiuliaRegion = Region::where('code', 'IT-FVG')->first();
        $trentinoAltoAdigeRegion = Region::where('code', 'IT-TAA')->first();
        $umbriaRegion = Region::where('code', 'IT-UMB')->first();
        $basilicataRegion = Region::where('code', 'IT-BAS')->first();
        $moliseRegion = Region::where('code', 'IT-MOL')->first();
        $valleAostaRegion = Region::where('code', 'IT-VDA')->first();

        $cities = [
            // Lazio (Rome region)
            [
                'name' => 'Rome',
                'code' => 'IT-LAZ-ROM',
                'is_capital' => true,
                'is_default' => true,
                'region_id' => $lazioRegion?->id,
                'latitude' => 41.9028,
                'longitude' => 12.4964,
                'population' => 2872800,
                'postal_codes' => ['00100', '00118', '00119'],
                'translations' => [
                    'lt' => ['name' => 'Roma', 'description' => 'Italijos sostinė'],
                    'en' => ['name' => 'Rome', 'description' => 'Capital of Italy'],
                ],
            ],
            [
                'name' => 'Latina',
                'code' => 'IT-LAZ-LAT',
                'region_id' => $lazioRegion?->id,
                'latitude' => 41.4679,
                'longitude' => 12.9036,
                'population' => 126151,
                'postal_codes' => ['04100'],
                'translations' => [
                    'lt' => ['name' => 'Latina', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Latina', 'description' => 'Industrial city'],
                ],
            ],
            // Lombardy
            [
                'name' => 'Milan',
                'code' => 'IT-LOM-MIL',
                'region_id' => $lombardyRegion?->id,
                'latitude' => 45.4642,
                'longitude' => 9.19,
                'population' => 1371498,
                'postal_codes' => ['20100', '20121', '20122'],
                'translations' => [
                    'lt' => ['name' => 'Milanas', 'description' => 'Lombardijos sostinė'],
                    'en' => ['name' => 'Milan', 'description' => 'Capital of Lombardy'],
                ],
            ],
            [
                'name' => 'Brescia',
                'code' => 'IT-LOM-BRE',
                'region_id' => $lombardyRegion?->id,
                'latitude' => 45.5416,
                'longitude' => 10.2118,
                'population' => 196670,
                'postal_codes' => ['25100'],
                'translations' => [
                    'lt' => ['name' => 'Brešija', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Brescia', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Bergamo',
                'code' => 'IT-LOM-BER',
                'region_id' => $lombardyRegion?->id,
                'latitude' => 45.6949,
                'longitude' => 9.6773,
                'population' => 120923,
                'postal_codes' => ['24100'],
                'translations' => [
                    'lt' => ['name' => 'Bergamas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Bergamo', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Monza',
                'code' => 'IT-LOM-MON',
                'region_id' => $lombardyRegion?->id,
                'latitude' => 45.5845,
                'longitude' => 9.2744,
                'population' => 123598,
                'postal_codes' => ['20900'],
                'translations' => [
                    'lt' => ['name' => 'Monca', 'description' => 'Automobilių lenktynių miestas'],
                    'en' => ['name' => 'Monza', 'description' => 'Car racing city'],
                ],
            ],
            [
                'name' => 'Como',
                'code' => 'IT-LOM-COM',
                'region_id' => $lombardyRegion?->id,
                'latitude' => 45.8081,
                'longitude' => 9.0852,
                'population' => 84234,
                'postal_codes' => ['22100'],
                'translations' => [
                    'lt' => ['name' => 'Komas', 'description' => 'Ežero miestas'],
                    'en' => ['name' => 'Como', 'description' => 'Lake city'],
                ],
            ],
            // Campania
            [
                'name' => 'Naples',
                'code' => 'IT-CAM-NAP',
                'region_id' => $campaniaRegion?->id,
                'latitude' => 40.8518,
                'longitude' => 14.2681,
                'population' => 914758,
                'postal_codes' => ['80100', '80121', '80122'],
                'translations' => [
                    'lt' => ['name' => 'Neapolis', 'description' => 'Kampanijos sostinė'],
                    'en' => ['name' => 'Naples', 'description' => 'Capital of Campania'],
                ],
            ],
            [
                'name' => 'Salerno',
                'code' => 'IT-CAM-SAL',
                'region_id' => $campaniaRegion?->id,
                'latitude' => 40.6824,
                'longitude' => 14.7681,
                'population' => 133364,
                'postal_codes' => ['84100'],
                'translations' => [
                    'lt' => ['name' => 'Salernas', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'Salerno', 'description' => 'Seaside city'],
                ],
            ],
            [
                'name' => 'Caserta',
                'code' => 'IT-CAM-CAS',
                'region_id' => $campaniaRegion?->id,
                'latitude' => 41.0736,
                'longitude' => 14.3325,
                'population' => 75561,
                'postal_codes' => ['81100'],
                'translations' => [
                    'lt' => ['name' => 'Kazerta', 'description' => 'Karališkojo rūmo miestas'],
                    'en' => ['name' => 'Caserta', 'description' => 'Royal palace city'],
                ],
            ],
            // Sicily
            [
                'name' => 'Palermo',
                'code' => 'IT-SIC-PAL',
                'region_id' => $sicilyRegion?->id,
                'latitude' => 38.1157,
                'longitude' => 13.3613,
                'population' => 630828,
                'postal_codes' => ['90100'],
                'translations' => [
                    'lt' => ['name' => 'Palermas', 'description' => 'Sicilijos sostinė'],
                    'en' => ['name' => 'Palermo', 'description' => 'Capital of Sicily'],
                ],
            ],
            [
                'name' => 'Catania',
                'code' => 'IT-SIC-CAT',
                'region_id' => $sicilyRegion?->id,
                'latitude' => 37.5079,
                'longitude' => 15.083,
                'population' => 311584,
                'postal_codes' => ['95100'],
                'translations' => [
                    'lt' => ['name' => 'Katanija', 'description' => 'Etna ugnikalnio miestas'],
                    'en' => ['name' => 'Catania', 'description' => 'Mount Etna city'],
                ],
            ],
            [
                'name' => 'Messina',
                'code' => 'IT-SIC-MES',
                'region_id' => $sicilyRegion?->id,
                'latitude' => 38.1938,
                'longitude' => 15.554,
                'population' => 234293,
                'postal_codes' => ['98100'],
                'translations' => [
                    'lt' => ['name' => 'Mesina', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Messina', 'description' => 'Port city'],
                ],
            ],
            [
                'name' => 'Syracuse',
                'code' => 'IT-SIC-SIR',
                'region_id' => $sicilyRegion?->id,
                'latitude' => 37.0755,
                'longitude' => 15.2866,
                'population' => 121605,
                'postal_codes' => ['96100'],
                'translations' => [
                    'lt' => ['name' => 'Sirakūzai', 'description' => 'Senovinis miestas'],
                    'en' => ['name' => 'Syracuse', 'description' => 'Ancient city'],
                ],
            ],
            // Veneto
            [
                'name' => 'Venice',
                'code' => 'IT-VEN-VEN',
                'region_id' => $venetoRegion?->id,
                'latitude' => 45.4408,
                'longitude' => 12.3155,
                'population' => 261905,
                'postal_codes' => ['30100'],
                'translations' => [
                    'lt' => ['name' => 'Venecija', 'description' => 'Kanalų miestas'],
                    'en' => ['name' => 'Venice', 'description' => 'City of canals'],
                ],
            ],
            [
                'name' => 'Verona',
                'code' => 'IT-VEN-VER',
                'region_id' => $venetoRegion?->id,
                'latitude' => 45.4384,
                'longitude' => 10.9916,
                'population' => 257353,
                'postal_codes' => ['37100'],
                'translations' => [
                    'lt' => ['name' => 'Verona', 'description' => 'Romeo ir Džuljetos miestas'],
                    'en' => ['name' => 'Verona', 'description' => 'Romeo and Juliet city'],
                ],
            ],
            [
                'name' => 'Padua',
                'code' => 'IT-VEN-PAD',
                'region_id' => $venetoRegion?->id,
                'latitude' => 45.4064,
                'longitude' => 11.8768,
                'population' => 210440,
                'postal_codes' => ['35100'],
                'translations' => [
                    'lt' => ['name' => 'Paduja', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Padua', 'description' => 'University city'],
                ],
            ],
            [
                'name' => 'Vicenza',
                'code' => 'IT-VEN-VIC',
                'region_id' => $venetoRegion?->id,
                'latitude' => 45.5455,
                'longitude' => 11.5353,
                'population' => 111395,
                'postal_codes' => ['36100'],
                'translations' => [
                    'lt' => ['name' => 'Vičenca', 'description' => 'Palladio miestas'],
                    'en' => ['name' => 'Vicenza', 'description' => 'Palladio city'],
                ],
            ],
            // Emilia-Romagna
            [
                'name' => 'Bologna',
                'code' => 'IT-EMR-BOL',
                'region_id' => $emiliaRomagnaRegion?->id,
                'latitude' => 44.4949,
                'longitude' => 11.3426,
                'population' => 390625,
                'postal_codes' => ['40100'],
                'translations' => [
                    'lt' => ['name' => 'Bolonija', 'description' => 'Emilijos-Romanijos sostinė'],
                    'en' => ['name' => 'Bologna', 'description' => 'Capital of Emilia-Romagna'],
                ],
            ],
            [
                'name' => 'Modena',
                'code' => 'IT-EMR-MOD',
                'region_id' => $emiliaRomagnaRegion?->id,
                'latitude' => 44.6471,
                'longitude' => 10.9252,
                'population' => 185273,
                'postal_codes' => ['41100'],
                'translations' => [
                    'lt' => ['name' => 'Modena', 'description' => 'Automobilių pramonės centras'],
                    'en' => ['name' => 'Modena', 'description' => 'Automotive industry center'],
                ],
            ],
            [
                'name' => 'Parma',
                'code' => 'IT-EMR-PAR',
                'region_id' => $emiliaRomagnaRegion?->id,
                'latitude' => 44.8015,
                'longitude' => 10.3279,
                'population' => 195687,
                'postal_codes' => ['43100'],
                'translations' => [
                    'lt' => ['name' => 'Parma', 'description' => 'Maisto pramonės centras'],
                    'en' => ['name' => 'Parma', 'description' => 'Food industry center'],
                ],
            ],
            [
                'name' => 'Reggio Emilia',
                'code' => 'IT-EMR-REG',
                'region_id' => $emiliaRomagnaRegion?->id,
                'latitude' => 44.6989,
                'longitude' => 10.6297,
                'population' => 171491,
                'postal_codes' => ['42100'],
                'translations' => [
                    'lt' => ['name' => 'Redžo Emilija', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Reggio Emilia', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Ravenna',
                'code' => 'IT-EMR-RAV',
                'region_id' => $emiliaRomagnaRegion?->id,
                'latitude' => 44.4184,
                'longitude' => 12.2035,
                'population' => 159115,
                'postal_codes' => ['48100'],
                'translations' => [
                    'lt' => ['name' => 'Ravena', 'description' => 'Bizantijos miestas'],
                    'en' => ['name' => 'Ravenna', 'description' => 'Byzantine city'],
                ],
            ],
            // Piedmont
            [
                'name' => 'Turin',
                'code' => 'IT-PIE-TUR',
                'region_id' => $piedmontRegion?->id,
                'latitude' => 45.0703,
                'longitude' => 7.6869,
                'population' => 848196,
                'postal_codes' => ['10100'],
                'translations' => [
                    'lt' => ['name' => 'Torinas', 'description' => 'Pjemonto sostinė'],
                    'en' => ['name' => 'Turin', 'description' => 'Capital of Piedmont'],
                ],
            ],
            [
                'name' => 'Novara',
                'code' => 'IT-PIE-NOV',
                'region_id' => $piedmontRegion?->id,
                'latitude' => 45.4469,
                'longitude' => 8.6222,
                'population' => 101952,
                'postal_codes' => ['28100'],
                'translations' => [
                    'lt' => ['name' => 'Novara', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Novara', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Alessandria',
                'code' => 'IT-PIE-ALE',
                'region_id' => $piedmontRegion?->id,
                'latitude' => 44.9133,
                'longitude' => 8.615,
                'population' => 89910,
                'postal_codes' => ['15100'],
                'translations' => [
                    'lt' => ['name' => 'Aleksandrija', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Alessandria', 'description' => 'Historic city'],
                ],
            ],
            // Apulia
            [
                'name' => 'Bari',
                'code' => 'IT-PUG-BAR',
                'region_id' => $apuliaRegion?->id,
                'latitude' => 41.1177,
                'longitude' => 16.8719,
                'population' => 315284,
                'postal_codes' => ['70100'],
                'translations' => [
                    'lt' => ['name' => 'Bari', 'description' => 'Apulijos sostinė'],
                    'en' => ['name' => 'Bari', 'description' => 'Capital of Apulia'],
                ],
            ],
            [
                'name' => 'Taranto',
                'code' => 'IT-PUG-TAR',
                'region_id' => $apuliaRegion?->id,
                'latitude' => 40.4737,
                'longitude' => 17.23,
                'population' => 195227,
                'postal_codes' => ['74100'],
                'translations' => [
                    'lt' => ['name' => 'Tarantas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Taranto', 'description' => 'Port city'],
                ],
            ],
            [
                'name' => 'Foggia',
                'code' => 'IT-PUG-FOG',
                'region_id' => $apuliaRegion?->id,
                'latitude' => 41.4622,
                'longitude' => 15.5442,
                'population' => 147036,
                'postal_codes' => ['71100'],
                'translations' => [
                    'lt' => ['name' => 'Fodžija', 'description' => 'Žemės ūkio centras'],
                    'en' => ['name' => 'Foggia', 'description' => 'Agricultural center'],
                ],
            ],
            // Tuscany
            [
                'name' => 'Florence',
                'code' => 'IT-TOS-FLO',
                'region_id' => $tuscanyRegion?->id,
                'latitude' => 43.7696,
                'longitude' => 11.2558,
                'population' => 366927,
                'postal_codes' => ['50100'],
                'translations' => [
                    'lt' => ['name' => 'Florencija', 'description' => 'Toskanos sostinė'],
                    'en' => ['name' => 'Florence', 'description' => 'Capital of Tuscany'],
                ],
            ],
            [
                'name' => 'Pisa',
                'code' => 'IT-TOS-PIS',
                'region_id' => $tuscanyRegion?->id,
                'latitude' => 43.7228,
                'longitude' => 10.4017,
                'population' => 89674,
                'postal_codes' => ['56100'],
                'translations' => [
                    'lt' => ['name' => 'Piza', 'description' => 'Kreivosios bokšto miestas'],
                    'en' => ['name' => 'Pisa', 'description' => 'Leaning tower city'],
                ],
            ],
            [
                'name' => 'Siena',
                'code' => 'IT-TOS-SIE',
                'region_id' => $tuscanyRegion?->id,
                'latitude' => 43.3188,
                'longitude' => 11.3307,
                'population' => 53343,
                'postal_codes' => ['53100'],
                'translations' => [
                    'lt' => ['name' => 'Siena', 'description' => 'UNESCO miestas'],
                    'en' => ['name' => 'Siena', 'description' => 'UNESCO city'],
                ],
            ],
            [
                'name' => 'Livorno',
                'code' => 'IT-TOS-LIV',
                'region_id' => $tuscanyRegion?->id,
                'latitude' => 43.5503,
                'longitude' => 10.3103,
                'population' => 157017,
                'postal_codes' => ['57100'],
                'translations' => [
                    'lt' => ['name' => 'Livornas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Livorno', 'description' => 'Port city'],
                ],
            ],
            // Calabria
            [
                'name' => 'Reggio Calabria',
                'code' => 'IT-CAL-REG',
                'region_id' => $calabriaRegion?->id,
                'latitude' => 38.1112,
                'longitude' => 15.6613,
                'population' => 180353,
                'postal_codes' => ['89100'],
                'translations' => [
                    'lt' => ['name' => 'Redžo Kalabrija', 'description' => 'Kalabrijos sostinė'],
                    'en' => ['name' => 'Reggio Calabria', 'description' => 'Capital of Calabria'],
                ],
            ],
            [
                'name' => 'Catanzaro',
                'code' => 'IT-CAL-CAT',
                'region_id' => $calabriaRegion?->id,
                'latitude' => 38.9108,
                'longitude' => 16.5874,
                'population' => 89364,
                'postal_codes' => ['88100'],
                'translations' => [
                    'lt' => ['name' => 'Katancaras', 'description' => 'Administracinis centras'],
                    'en' => ['name' => 'Catanzaro', 'description' => 'Administrative center'],
                ],
            ],
            // Sardinia
            [
                'name' => 'Cagliari',
                'code' => 'IT-SAR-CAG',
                'region_id' => $sardiniaRegion?->id,
                'latitude' => 39.2238,
                'longitude' => 9.1217,
                'population' => 154460,
                'postal_codes' => ['09100'],
                'translations' => [
                    'lt' => ['name' => 'Kaljaris', 'description' => 'Sardinijos sostinė'],
                    'en' => ['name' => 'Cagliari', 'description' => 'Capital of Sardinia'],
                ],
            ],
            [
                'name' => 'Sassari',
                'code' => 'IT-SAR-SAS',
                'region_id' => $sardiniaRegion?->id,
                'latitude' => 40.7259,
                'longitude' => 8.5557,
                'population' => 127525,
                'postal_codes' => ['07100'],
                'translations' => [
                    'lt' => ['name' => 'Sasaris', 'description' => 'Šiaurės Sardinijos centras'],
                    'en' => ['name' => 'Sassari', 'description' => 'Center of Northern Sardinia'],
                ],
            ],
            // Liguria
            [
                'name' => 'Genoa',
                'code' => 'IT-LIG-GEN',
                'region_id' => $liguriaRegion?->id,
                'latitude' => 44.4056,
                'longitude' => 8.9463,
                'population' => 580097,
                'postal_codes' => ['16100'],
                'translations' => [
                    'lt' => ['name' => 'Genuja', 'description' => 'Ligūrijos sostinė'],
                    'en' => ['name' => 'Genoa', 'description' => 'Capital of Liguria'],
                ],
            ],
            // Marche
            [
                'name' => 'Ancona',
                'code' => 'IT-MAR-ANC',
                'region_id' => $marcheRegion?->id,
                'latitude' => 43.6158,
                'longitude' => 13.5189,
                'population' => 100497,
                'postal_codes' => ['60100'],
                'translations' => [
                    'lt' => ['name' => 'Ankona', 'description' => 'Markės sostinė'],
                    'en' => ['name' => 'Ancona', 'description' => 'Capital of Marche'],
                ],
            ],
            // Abruzzo
            [
                'name' => "L'Aquila",
                'code' => 'IT-ABR-AQU',
                'region_id' => $abruzzoRegion?->id,
                'latitude' => 42.354,
                'longitude' => 13.392,
                'population' => 69684,
                'postal_codes' => ['67100'],
                'translations' => [
                    'lt' => ['name' => "L'Akila", 'description' => 'Abruco sostinė'],
                    'en' => ['name' => "L'Aquila", 'description' => 'Capital of Abruzzo'],
                ],
            ],
            // Friuli-Venezia Giulia
            [
                'name' => 'Trieste',
                'code' => 'IT-FVG-TRI',
                'region_id' => $friuliVeneziaGiuliaRegion?->id,
                'latitude' => 45.6495,
                'longitude' => 13.7768,
                'population' => 204338,
                'postal_codes' => ['34100'],
                'translations' => [
                    'lt' => ['name' => 'Triestas', 'description' => 'Friulio-Venecijos Džulijos sostinė'],
                    'en' => ['name' => 'Trieste', 'description' => 'Capital of Friuli-Venezia Giulia'],
                ],
            ],
            // Trentino-Alto Adige
            [
                'name' => 'Trento',
                'code' => 'IT-TAA-TRE',
                'region_id' => $trentinoAltoAdigeRegion?->id,
                'latitude' => 46.0748,
                'longitude' => 11.1217,
                'population' => 117997,
                'postal_codes' => ['38100'],
                'translations' => [
                    'lt' => ['name' => 'Trentas', 'description' => 'Trentino-Alto Adidžės sostinė'],
                    'en' => ['name' => 'Trento', 'description' => 'Capital of Trentino-Alto Adige'],
                ],
            ],
            // Umbria
            [
                'name' => 'Perugia',
                'code' => 'IT-UMB-PER',
                'region_id' => $umbriaRegion?->id,
                'latitude' => 43.1122,
                'longitude' => 12.3888,
                'population' => 165683,
                'postal_codes' => ['06100'],
                'translations' => [
                    'lt' => ['name' => 'Perudžija', 'description' => 'Umbrijos sostinė'],
                    'en' => ['name' => 'Perugia', 'description' => 'Capital of Umbria'],
                ],
            ],
            // Basilicata
            [
                'name' => 'Potenza',
                'code' => 'IT-BAS-POT',
                'region_id' => $basilicataRegion?->id,
                'latitude' => 40.6418,
                'longitude' => 15.8079,
                'population' => 67122,
                'postal_codes' => ['85100'],
                'translations' => [
                    'lt' => ['name' => 'Potenca', 'description' => 'Bazilikatos sostinė'],
                    'en' => ['name' => 'Potenza', 'description' => 'Capital of Basilicata'],
                ],
            ],
            // Molise
            [
                'name' => 'Campobasso',
                'code' => 'IT-MOL-CAM',
                'region_id' => $moliseRegion?->id,
                'latitude' => 41.5598,
                'longitude' => 14.6674,
                'population' => 49062,
                'postal_codes' => ['86100'],
                'translations' => [
                    'lt' => ['name' => 'Kampobasas', 'description' => 'Molizės sostinė'],
                    'en' => ['name' => 'Campobasso', 'description' => 'Capital of Molise'],
                ],
            ],
            // Valle d\'Aosta
            [
                'name' => 'Aosta',
                'code' => 'IT-VDA-AOS',
                'region_id' => $valleAostaRegion?->id,
                'latitude' => 45.7372,
                'longitude' => 7.3206,
                'population' => 34218,
                'postal_codes' => ['11100'],
                'translations' => [
                    'lt' => ['name' => 'Aosta', 'description' => "Valle d'Aostos sostinė"],
                    'en' => ['name' => 'Aosta', 'description' => "Capital of Valle d'Aosta"],
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
                    'country_id' => $italy->id,
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
