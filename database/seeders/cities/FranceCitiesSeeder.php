<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Translations\CityTranslation;
use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class FranceCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $france = Country::where('cca2', 'FR')->first();
        $euZone = Zone::where('code', 'EU')->first();

        // Get regions
        $ileDeFranceRegion = Region::where('code', 'FR-IDF')->first();
        $auvergneRhoneAlpesRegion = Region::where('code', 'FR-ARA')->first();
        $hautsDeFranceRegion = Region::where('code', 'FR-HDF')->first();
        $occitanieRegion = Region::where('code', 'FR-OCC')->first();
        $nouvelleAquitaineRegion = Region::where('code', 'FR-NAQ')->first();
        $grandEstRegion = Region::where('code', 'FR-GES')->first();
        $paysDeLaLoireRegion = Region::where('code', 'FR-PDL')->first();
        $bretagneRegion = Region::where('code', 'FR-BRE')->first();
        $normandieRegion = Region::where('code', 'FR-NOR')->first();
        $provenceAlpesCoteAzurRegion = Region::where('code', 'FR-PAC')->first();
        $bourgogneFrancheComteRegion = Region::where('code', 'FR-BFC')->first();
        $centreValDeLoireRegion = Region::where('code', 'FR-CVL')->first();
        $corseRegion = Region::where('code', 'FR-COR')->first();

        $cities = [
            // Île-de-France (Paris region)
            [
                'name' => 'Paris',
                'code' => 'FR-IDF-PAR',
                'is_capital' => true,
                'is_default' => true,
                'region_id' => $ileDeFranceRegion?->id,
                'latitude' => 48.8566,
                'longitude' => 2.3522,
                'population' => 2161000,
                'postal_codes' => ['75001', '75002', '75003'],
                'translations' => [
                    'lt' => ['name' => 'Paryžius', 'description' => 'Prancūzijos sostinė'],
                    'en' => ['name' => 'Paris', 'description' => 'Capital of France'],
                ],
            ],
            [
                'name' => 'Boulogne-Billancourt',
                'code' => 'FR-IDF-BOU',
                'region_id' => $ileDeFranceRegion?->id,
                'latitude' => 48.8354,
                'longitude' => 2.2413,
                'population' => 121334,
                'postal_codes' => ['92100'],
                'translations' => [
                    'lt' => ['name' => 'Boulogne-Billancourt', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Boulogne-Billancourt', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Saint-Denis',
                'code' => 'FR-IDF-SDN',
                'region_id' => $ileDeFranceRegion?->id,
                'latitude' => 48.9362,
                'longitude' => 2.3574,
                'population' => 111103,
                'postal_codes' => ['93200'],
                'translations' => [
                    'lt' => ['name' => 'Saint-Denis', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Saint-Denis', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Argenteuil',
                'code' => 'FR-IDF-ARG',
                'region_id' => $ileDeFranceRegion?->id,
                'latitude' => 48.9478,
                'longitude' => 2.2474,
                'population' => 110388,
                'postal_codes' => ['95100'],
                'translations' => [
                    'lt' => ['name' => 'Argenteuil', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'Argenteuil', 'description' => 'Seaside city'],
                ],
            ],
            [
                'name' => 'Montreuil',
                'code' => 'FR-IDF-MON',
                'region_id' => $ileDeFranceRegion?->id,
                'latitude' => 48.8614,
                'longitude' => 2.4442,
                'population' => 109914,
                'postal_codes' => ['93100'],
                'translations' => [
                    'lt' => ['name' => 'Montreuil', 'description' => 'Pramonės centras'],
                    'en' => ['name' => 'Montreuil', 'description' => 'Industrial center'],
                ],
            ],
            // Auvergne-Rhône-Alpes
            [
                'name' => 'Lyon',
                'code' => 'FR-ARA-LYO',
                'region_id' => $auvergneRhoneAlpesRegion?->id,
                'latitude' => 45.764,
                'longitude' => 4.8357,
                'population' => 515695,
                'postal_codes' => ['69001', '69002', '69003'],
                'translations' => [
                    'lt' => ['name' => 'Lionas', 'description' => 'Antrasis didžiausias Prancūzijos miestas'],
                    'en' => ['name' => 'Lyon', 'description' => 'Second largest city in France'],
                ],
            ],
            [
                'name' => 'Saint-Étienne',
                'code' => 'FR-ARA-STE',
                'region_id' => $auvergneRhoneAlpesRegion?->id,
                'latitude' => 45.4397,
                'longitude' => 4.3872,
                'population' => 171057,
                'postal_codes' => ['42000'],
                'translations' => [
                    'lt' => ['name' => 'Saint-Étienne', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Saint-Étienne', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Grenoble',
                'code' => 'FR-ARA-GRE',
                'region_id' => $auvergneRhoneAlpesRegion?->id,
                'latitude' => 45.1885,
                'longitude' => 5.7245,
                'population' => 158552,
                'postal_codes' => ['38000'],
                'translations' => [
                    'lt' => ['name' => 'Grenoble', 'description' => 'Mokslo miestas'],
                    'en' => ['name' => 'Grenoble', 'description' => 'Science city'],
                ],
            ],
            [
                'name' => 'Villeurbanne',
                'code' => 'FR-ARA-VIL',
                'region_id' => $auvergneRhoneAlpesRegion?->id,
                'latitude' => 45.7667,
                'longitude' => 4.8833,
                'population' => 149019,
                'postal_codes' => ['69100'],
                'translations' => [
                    'lt' => ['name' => 'Villeurbanne', 'description' => 'Liono priemiestis'],
                    'en' => ['name' => 'Villeurbanne', 'description' => 'Lyon suburb'],
                ],
            ],
            [
                'name' => 'Clermont-Ferrand',
                'code' => 'FR-ARA-CLF',
                'region_id' => $auvergneRhoneAlpesRegion?->id,
                'latitude' => 45.7772,
                'longitude' => 3.087,
                'population' => 143886,
                'postal_codes' => ['63000'],
                'translations' => [
                    'lt' => ['name' => 'Clermont-Ferrand', 'description' => 'Gumos pramonės centras'],
                    'en' => ['name' => 'Clermont-Ferrand', 'description' => 'Tire industry center'],
                ],
            ],
            // Hauts-de-France
            [
                'name' => 'Lille',
                'code' => 'FR-HDF-LIL',
                'region_id' => $hautsDeFranceRegion?->id,
                'latitude' => 50.6292,
                'longitude' => 3.0573,
                'population' => 232787,
                'postal_codes' => ['59000'],
                'translations' => [
                    'lt' => ['name' => 'Lille', 'description' => 'Šiaurės Prancūzijos centras'],
                    'en' => ['name' => 'Lille', 'description' => 'Center of Northern France'],
                ],
            ],
            [
                'name' => 'Amiens',
                'code' => 'FR-HDF-AMI',
                'region_id' => $hautsDeFranceRegion?->id,
                'latitude' => 49.8943,
                'longitude' => 2.2958,
                'population' => 133448,
                'postal_codes' => ['80000'],
                'translations' => [
                    'lt' => ['name' => 'Amiens', 'description' => 'Katedros miestas'],
                    'en' => ['name' => 'Amiens', 'description' => 'Cathedral city'],
                ],
            ],
            [
                'name' => 'Roubaix',
                'code' => 'FR-HDF-ROU',
                'region_id' => $hautsDeFranceRegion?->id,
                'latitude' => 50.6927,
                'longitude' => 3.1776,
                'population' => 95721,
                'postal_codes' => ['59100'],
                'translations' => [
                    'lt' => ['name' => 'Roubaix', 'description' => 'Tekstilės pramonės centras'],
                    'en' => ['name' => 'Roubaix', 'description' => 'Textile industry center'],
                ],
            ],
            [
                'name' => 'Tourcoing',
                'code' => 'FR-HDF-TOU',
                'region_id' => $hautsDeFranceRegion?->id,
                'latitude' => 50.7239,
                'longitude' => 3.1612,
                'population' => 97442,
                'postal_codes' => ['59200'],
                'translations' => [
                    'lt' => ['name' => 'Tourcoing', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Tourcoing', 'description' => 'Industrial city'],
                ],
            ],
            // Occitanie
            [
                'name' => 'Toulouse',
                'code' => 'FR-OCC-TOU',
                'region_id' => $occitanieRegion?->id,
                'latitude' => 43.6047,
                'longitude' => 1.4442,
                'population' => 479553,
                'postal_codes' => ['31000'],
                'translations' => [
                    'lt' => ['name' => 'Toulouse', 'description' => 'Aviacijos pramonės centras'],
                    'en' => ['name' => 'Toulouse', 'description' => 'Aviation industry center'],
                ],
            ],
            [
                'name' => 'Montpellier',
                'code' => 'FR-OCC-MON',
                'region_id' => $occitanieRegion?->id,
                'latitude' => 43.611,
                'longitude' => 3.8767,
                'population' => 290053,
                'postal_codes' => ['34000'],
                'translations' => [
                    'lt' => ['name' => 'Montpellier', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Montpellier', 'description' => 'University city'],
                ],
            ],
            [
                'name' => 'Nîmes',
                'code' => 'FR-OCC-NIM',
                'region_id' => $occitanieRegion?->id,
                'latitude' => 43.8367,
                'longitude' => 4.3601,
                'population' => 150564,
                'postal_codes' => ['30000'],
                'translations' => [
                    'lt' => ['name' => 'Nîmes', 'description' => 'Romėnų miestas'],
                    'en' => ['name' => 'Nîmes', 'description' => 'Roman city'],
                ],
            ],
            [
                'name' => 'Perpignan',
                'code' => 'FR-OCC-PER',
                'region_id' => $occitanieRegion?->id,
                'latitude' => 42.6886,
                'longitude' => 2.8948,
                'population' => 121934,
                'postal_codes' => ['66000'],
                'translations' => [
                    'lt' => ['name' => 'Perpignan', 'description' => 'Katalonijos miestas'],
                    'en' => ['name' => 'Perpignan', 'description' => 'Catalan city'],
                ],
            ],
            // Nouvelle-Aquitaine
            [
                'name' => 'Bordeaux',
                'code' => 'FR-NAQ-BOR',
                'region_id' => $nouvelleAquitaineRegion?->id,
                'latitude' => 44.8378,
                'longitude' => -0.5792,
                'population' => 254436,
                'postal_codes' => ['33000'],
                'translations' => [
                    'lt' => ['name' => 'Bordeaux', 'description' => 'Vyno miestas'],
                    'en' => ['name' => 'Bordeaux', 'description' => 'Wine city'],
                ],
            ],
            [
                'name' => 'Limoges',
                'code' => 'FR-NAQ-LIM',
                'region_id' => $nouvelleAquitaineRegion?->id,
                'latitude' => 45.8336,
                'longitude' => 1.2611,
                'population' => 132175,
                'postal_codes' => ['87000'],
                'translations' => [
                    'lt' => ['name' => 'Limoges', 'description' => 'Porceliano miestas'],
                    'en' => ['name' => 'Limoges', 'description' => 'Porcelain city'],
                ],
            ],
            [
                'name' => 'Poitiers',
                'code' => 'FR-NAQ-POI',
                'region_id' => $nouvelleAquitaineRegion?->id,
                'latitude' => 46.5802,
                'longitude' => 0.3404,
                'population' => 88765,
                'postal_codes' => ['86000'],
                'translations' => [
                    'lt' => ['name' => 'Poitiers', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Poitiers', 'description' => 'Historic city'],
                ],
            ],
            // Grand Est
            [
                'name' => 'Strasbourg',
                'code' => 'FR-GES-STR',
                'region_id' => $grandEstRegion?->id,
                'latitude' => 48.5734,
                'longitude' => 7.7521,
                'population' => 280966,
                'postal_codes' => ['67000'],
                'translations' => [
                    'lt' => ['name' => 'Strasburgas', 'description' => 'Europos Parlamento miestas'],
                    'en' => ['name' => 'Strasbourg', 'description' => 'European Parliament city'],
                ],
            ],
            [
                'name' => 'Reims',
                'code' => 'FR-GES-REI',
                'region_id' => $grandEstRegion?->id,
                'latitude' => 49.2583,
                'longitude' => 4.0317,
                'population' => 182592,
                'postal_codes' => ['51100'],
                'translations' => [
                    'lt' => ['name' => 'Reims', 'description' => 'Šampano miestas'],
                    'en' => ['name' => 'Reims', 'description' => 'Champagne city'],
                ],
            ],
            [
                'name' => 'Metz',
                'code' => 'FR-GES-MET',
                'region_id' => $grandEstRegion?->id,
                'latitude' => 49.1193,
                'longitude' => 6.1757,
                'population' => 116429,
                'postal_codes' => ['57000'],
                'translations' => [
                    'lt' => ['name' => 'Metz', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Metz', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Nancy',
                'code' => 'FR-GES-NAN',
                'region_id' => $grandEstRegion?->id,
                'latitude' => 48.6921,
                'longitude' => 6.1844,
                'population' => 104885,
                'postal_codes' => ['54000'],
                'translations' => [
                    'lt' => ['name' => 'Nancy', 'description' => 'Menų miestas'],
                    'en' => ['name' => 'Nancy', 'description' => 'Arts city'],
                ],
            ],
            // Pays de la Loire
            [
                'name' => 'Nantes',
                'code' => 'FR-PDL-NAN',
                'region_id' => $paysDeLaLoireRegion?->id,
                'latitude' => 47.2184,
                'longitude' => -1.5536,
                'population' => 314138,
                'postal_codes' => ['44000'],
                'translations' => [
                    'lt' => ['name' => 'Nantes', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'Nantes', 'description' => 'Seaside city'],
                ],
            ],
            [
                'name' => 'Le Mans',
                'code' => 'FR-PDL-LEM',
                'region_id' => $paysDeLaLoireRegion?->id,
                'latitude' => 48.0061,
                'longitude' => 0.1996,
                'population' => 143240,
                'postal_codes' => ['72000'],
                'translations' => [
                    'lt' => ['name' => 'Le Mans', 'description' => 'Automobilių lenktynių miestas'],
                    'en' => ['name' => 'Le Mans', 'description' => 'Car racing city'],
                ],
            ],
            [
                'name' => 'Angers',
                'code' => 'FR-PDL-ANG',
                'region_id' => $paysDeLaLoireRegion?->id,
                'latitude' => 47.4784,
                'longitude' => -0.5632,
                'population' => 152960,
                'postal_codes' => ['49000'],
                'translations' => [
                    'lt' => ['name' => 'Angers', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Angers', 'description' => 'Historic city'],
                ],
            ],
            // Bretagne
            [
                'name' => 'Rennes',
                'code' => 'FR-BRE-REN',
                'region_id' => $bretagneRegion?->id,
                'latitude' => 48.1173,
                'longitude' => -1.6778,
                'population' => 217728,
                'postal_codes' => ['35000'],
                'translations' => [
                    'lt' => ['name' => 'Rennes', 'description' => 'Bretanijos sostinė'],
                    'en' => ['name' => 'Rennes', 'description' => 'Capital of Brittany'],
                ],
            ],
            [
                'name' => 'Brest',
                'code' => 'FR-BRE-BRE',
                'region_id' => $bretagneRegion?->id,
                'latitude' => 48.3905,
                'longitude' => -4.486,
                'population' => 139342,
                'postal_codes' => ['29200'],
                'translations' => [
                    'lt' => ['name' => 'Brest', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Brest', 'description' => 'Port city'],
                ],
            ],
            [
                'name' => 'Quimper',
                'code' => 'FR-BRE-QUI',
                'region_id' => $bretagneRegion?->id,
                'latitude' => 47.996,
                'longitude' => -4.1024,
                'population' => 63235,
                'postal_codes' => ['29000'],
                'translations' => [
                    'lt' => ['name' => 'Quimper', 'description' => 'Kultūros miestas'],
                    'en' => ['name' => 'Quimper', 'description' => 'Cultural city'],
                ],
            ],
            // Normandie
            [
                'name' => 'Rouen',
                'code' => 'FR-NOR-ROU',
                'region_id' => $normandieRegion?->id,
                'latitude' => 49.4432,
                'longitude' => 1.0993,
                'population' => 110145,
                'postal_codes' => ['76000'],
                'translations' => [
                    'lt' => ['name' => 'Rouen', 'description' => 'Normandijos sostinė'],
                    'en' => ['name' => 'Rouen', 'description' => 'Capital of Normandy'],
                ],
            ],
            [
                'name' => 'Le Havre',
                'code' => 'FR-NOR-LEH',
                'region_id' => $normandieRegion?->id,
                'latitude' => 49.4944,
                'longitude' => 0.1079,
                'population' => 170147,
                'postal_codes' => ['76600'],
                'translations' => [
                    'lt' => ['name' => 'Le Havre', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Le Havre', 'description' => 'Port city'],
                ],
            ],
            [
                'name' => 'Caen',
                'code' => 'FR-NOR-CAE',
                'region_id' => $normandieRegion?->id,
                'latitude' => 49.1829,
                'longitude' => -0.3707,
                'population' => 105403,
                'postal_codes' => ['14000'],
                'translations' => [
                    'lt' => ['name' => 'Caen', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Caen', 'description' => 'University city'],
                ],
            ],
            // Provence-Alpes-Côte d'Azur
            [
                'name' => 'Marseille',
                'code' => 'FR-PAC-MAR',
                'region_id' => $provenceAlpesCoteAzurRegion?->id,
                'latitude' => 43.2965,
                'longitude' => 5.3698,
                'population' => 868277,
                'postal_codes' => ['13001', '13002', '13003'],
                'translations' => [
                    'lt' => ['name' => 'Marselis', 'description' => 'Antrasis didžiausias Prancūzijos miestas'],
                    'en' => ['name' => 'Marseille', 'description' => 'Second largest city in France'],
                ],
            ],
            [
                'name' => 'Nice',
                'code' => 'FR-PAC-NIC',
                'region_id' => $provenceAlpesCoteAzurRegion?->id,
                'latitude' => 43.7102,
                'longitude' => 7.262,
                'population' => 342637,
                'postal_codes' => ['06000'],
                'translations' => [
                    'lt' => ['name' => 'Nice', 'description' => 'Kurortinis miestas'],
                    'en' => ['name' => 'Nice', 'description' => 'Resort city'],
                ],
            ],
            [
                'name' => 'Toulon',
                'code' => 'FR-PAC-TOU',
                'region_id' => $provenceAlpesCoteAzurRegion?->id,
                'latitude' => 43.1242,
                'longitude' => 5.928,
                'population' => 171953,
                'postal_codes' => ['83000'],
                'translations' => [
                    'lt' => ['name' => 'Toulon', 'description' => 'Karinis uostas'],
                    'en' => ['name' => 'Toulon', 'description' => 'Naval port'],
                ],
            ],
            [
                'name' => 'Aix-en-Provence',
                'code' => 'FR-PAC-AIX',
                'region_id' => $provenceAlpesCoteAzurRegion?->id,
                'latitude' => 43.5297,
                'longitude' => 5.4474,
                'population' => 143006,
                'postal_codes' => ['13100'],
                'translations' => [
                    'lt' => ['name' => 'Aix-en-Provence', 'description' => 'Menų miestas'],
                    'en' => ['name' => 'Aix-en-Provence', 'description' => 'Arts city'],
                ],
            ],
            [
                'name' => 'Avignon',
                'code' => 'FR-PAC-AVI',
                'region_id' => $provenceAlpesCoteAzurRegion?->id,
                'latitude' => 43.9493,
                'longitude' => 4.8055,
                'population' => 93371,
                'postal_codes' => ['84000'],
                'translations' => [
                    'lt' => ['name' => 'Avignon', 'description' => 'Popiežiaus miestas'],
                    'en' => ['name' => 'Avignon', 'description' => 'Pope city'],
                ],
            ],
            // Bourgogne-Franche-Comté
            [
                'name' => 'Dijon',
                'code' => 'FR-BFC-DIJ',
                'region_id' => $bourgogneFrancheComteRegion?->id,
                'latitude' => 47.322,
                'longitude' => 5.0415,
                'population' => 156920,
                'postal_codes' => ['21000'],
                'translations' => [
                    'lt' => ['name' => 'Dijon', 'description' => 'Garstyčių miestas'],
                    'en' => ['name' => 'Dijon', 'description' => 'Mustard city'],
                ],
            ],
            [
                'name' => 'Besançon',
                'code' => 'FR-BFC-BES',
                'region_id' => $bourgogneFrancheComteRegion?->id,
                'latitude' => 47.238,
                'longitude' => 6.024,
                'population' => 116914,
                'postal_codes' => ['25000'],
                'translations' => [
                    'lt' => ['name' => 'Besançon', 'description' => 'Laikrodžių miestas'],
                    'en' => ['name' => 'Besançon', 'description' => 'Watch city'],
                ],
            ],
            // Centre-Val de Loire
            [
                'name' => 'Tours',
                'code' => 'FR-CVL-TOU',
                'region_id' => $centreValDeLoireRegion?->id,
                'latitude' => 47.3941,
                'longitude' => 0.6848,
                'population' => 136463,
                'postal_codes' => ['37000'],
                'translations' => [
                    'lt' => ['name' => 'Tours', 'description' => 'Luaros slėnio miestas'],
                    'en' => ['name' => 'Tours', 'description' => 'Loire Valley city'],
                ],
            ],
            [
                'name' => 'Orléans',
                'code' => 'FR-CVL-ORL',
                'region_id' => $centreValDeLoireRegion?->id,
                'latitude' => 47.9029,
                'longitude' => 1.9093,
                'population' => 116238,
                'postal_codes' => ['45000'],
                'translations' => [
                    'lt' => ['name' => 'Orléans', 'description' => 'Joanos Arkos miestas'],
                    'en' => ['name' => 'Orléans', 'description' => 'Joan of Arc city'],
                ],
            ],
            // Corse
            [
                'name' => 'Ajaccio',
                'code' => 'FR-COR-AJA',
                'region_id' => $corseRegion?->id,
                'latitude' => 41.9267,
                'longitude' => 8.7369,
                'population' => 70597,
                'postal_codes' => ['20000'],
                'translations' => [
                    'lt' => ['name' => 'Ajaccio', 'description' => 'Korsikos sostinė'],
                    'en' => ['name' => 'Ajaccio', 'description' => 'Capital of Corsica'],
                ],
            ],
            [
                'name' => 'Bastia',
                'code' => 'FR-COR-BAS',
                'region_id' => $corseRegion?->id,
                'latitude' => 42.7028,
                'longitude' => 9.45,
                'population' => 44829,
                'postal_codes' => ['20200'],
                'translations' => [
                    'lt' => ['name' => 'Bastia', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Bastia', 'description' => 'Port city'],
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
                    'country_id' => $france->id,
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
