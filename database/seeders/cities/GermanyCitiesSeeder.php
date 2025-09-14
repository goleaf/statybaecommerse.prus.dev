<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class GermanyCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $germany = Country::where('cca2', 'DE')->first();
        $euZone = Zone::where('code', 'EU')->first();

        // Regions are no longer used in the database schema
        
        $cities = [
            // Berlin
            [
                'name' => 'Berlin',
                'code' => 'DE-BE-BER',
                'is_capital' => true,
                'is_default' => true,
                'latitude' => 52.52,
                'longitude' => 13.405,
                'population' => 3669491,
                'postal_codes' => ['10115', '10117', '10119'],
                'translations' => [
                    'lt' => ['name' => 'Berlynas', 'description' => 'Vokietijos sostinė'],
                    'en' => ['name' => 'Berlin', 'description' => 'Capital of Germany']
            ],
            ],
            // Hamburg
            [
                'name' => 'Hamburg',
                'code' => 'DE-HH-HAM',
                'latitude' => 53.5511,
                'longitude' => 9.9937,
                'population' => 1899160,
                'postal_codes' => ['20095', '20097', '20099'],
                'translations' => [
                    'lt' => ['name' => 'Hamburgas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Hamburg', 'description' => 'Port city']
            ],
            ],
            // Bavaria
            [
                'name' => 'Munich',
                'code' => 'DE-BY-MUN',
                'latitude' => 48.1351,
                'longitude' => 11.582,
                'population' => 1484226,
                'postal_codes' => ['80331', '80333', '80335'],
                'translations' => [
                    'lt' => ['name' => 'Miunchenas', 'description' => 'Bavarijos sostinė'],
                    'en' => ['name' => 'Munich', 'description' => 'Capital of Bavaria']
            ],
            ],
            [
                'name' => 'Nuremberg',
                'code' => 'DE-BY-NUR',
                'latitude' => 49.4521,
                'longitude' => 11.0767,
                'population' => 518365,
                'postal_codes' => ['90402', '90403', '90408'],
                'translations' => [
                    'lt' => ['name' => 'Niurnbergas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Nuremberg', 'description' => 'Historic city']
            ],
            ],
            [
                'name' => 'Augsburg',
                'code' => 'DE-BY-AUG',
                'latitude' => 48.3705,
                'longitude' => 10.8978,
                'population' => 296582,
                'postal_codes' => ['86150', '86152', '86153'],
                'translations' => [
                    'lt' => ['name' => 'Augsburgas', 'description' => 'Senovinis miestas'],
                    'en' => ['name' => 'Augsburg', 'description' => 'Ancient city']
            ],
            ],
            [
                'name' => 'Würzburg',
                'code' => 'DE-BY-WUR',
                'latitude' => 49.7913,
                'longitude' => 9.9534,
                'population' => 126635,
                'postal_codes' => ['97070', '97072', '97074'],
                'translations' => [
                    'lt' => ['name' => 'Vircburgas', 'description' => 'Vyno miestas'],
                    'en' => ['name' => 'Würzburg', 'description' => 'Wine city']
            ],
            ],
            [
                'name' => 'Regensburg',
                'code' => 'DE-BY-REG',
                'latitude' => 49.0134,
                'longitude' => 12.1016,
                'population' => 152610,
                'postal_codes' => ['93047', '93049', '93051'],
                'translations' => [
                    'lt' => ['name' => 'Regensburgas', 'description' => 'UNESCO miestas'],
                    'en' => ['name' => 'Regensburg', 'description' => 'UNESCO city']
            ],
            ],
            [
                'name' => 'Ingolstadt',
                'code' => 'DE-BY-ING',
                'latitude' => 48.7665,
                'longitude' => 11.4258,
                'population' => 137392,
                'postal_codes' => ['85049', '85051', '85053'],
                'translations' => [
                    'lt' => ['name' => 'Ingolštatas', 'description' => 'Automobilių pramonės centras'],
                    'en' => ['name' => 'Ingolstadt', 'description' => 'Automotive industry center']
            ],
            ],
            [
                'name' => 'Fürth',
                'code' => 'DE-BY-FUR',
                'latitude' => 49.4778,
                'longitude' => 10.9886,
                'population' => 128497,
                'postal_codes' => ['90762', '90763', '90765'],
                'translations' => [
                    'lt' => ['name' => 'Fiurtas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Fürth', 'description' => 'Industrial city']
            ],
            ],
            // Baden-Württemberg
            [
                'name' => 'Stuttgart',
                'code' => 'DE-BW-STU',
                'latitude' => 48.7758,
                'longitude' => 9.1829,
                'population' => 634830,
                'postal_codes' => ['70173', '70174', '70176'],
                'translations' => [
                    'lt' => ['name' => 'Štutgartas', 'description' => 'Badeno-Viurtembergo sostinė'],
                    'en' => ['name' => 'Stuttgart', 'description' => 'Capital of Baden-Württemberg']
            ],
            ],
            [
                'name' => 'Mannheim',
                'code' => 'DE-BW-MAN',
                'latitude' => 49.4875,
                'longitude' => 8.466,
                'population' => 315554,
                'postal_codes' => ['68159', '68161', '68163'],
                'translations' => [
                    'lt' => ['name' => 'Manheimas', 'description' => 'Pramonės centras'],
                    'en' => ['name' => 'Mannheim', 'description' => 'Industrial center']
            ],
            ],
            [
                'name' => 'Karlsruhe',
                'code' => 'DE-BW-KAR',
                'latitude' => 49.0069,
                'longitude' => 8.4037,
                'population' => 313092,
                'postal_codes' => ['76133', '76135', '76137'],
                'translations' => [
                    'lt' => ['name' => 'Karlsruhė', 'description' => 'Teismo miestas'],
                    'en' => ['name' => 'Karlsruhe', 'description' => 'Court city']
            ],
            ],
            [
                'name' => 'Freiburg',
                'code' => 'DE-BW-FRE',
                'latitude' => 47.999,
                'longitude' => 7.8421,
                'population' => 230940,
                'postal_codes' => ['79098', '79100', '79102'],
                'translations' => [
                    'lt' => ['name' => 'Freiburgas', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Freiburg', 'description' => 'University city']
            ],
            ],
            [
                'name' => 'Heidelberg',
                'code' => 'DE-BW-HEI',
                'latitude' => 49.3988,
                'longitude' => 8.6724,
                'population' => 160355,
                'postal_codes' => ['69115', '69117', '69118'],
                'translations' => [
                    'lt' => ['name' => 'Heidelbergas', 'description' => 'Romantikos miestas'],
                    'en' => ['name' => 'Heidelberg', 'description' => 'Romantic city']
            ],
            ],
            [
                'name' => 'Ulm',
                'code' => 'DE-BW-ULM',
                'latitude' => 48.4011,
                'longitude' => 9.9876,
                'population' => 126329,
                'postal_codes' => ['89073', '89075', '89077'],
                'translations' => [
                    'lt' => ['name' => 'Ulmai', 'description' => 'Einšteino gimtasis miestas'],
                    'en' => ['name' => 'Ulm', 'description' => "Einstein's birthplace"]
            ],
            ],
            [
                'name' => 'Tübingen',
                'code' => 'DE-BW-TUB',
                'latitude' => 48.52,
                'longitude' => 9.0556,
                'population' => 91108,
                'postal_codes' => ['72070', '72072', '72074'],
                'translations' => [
                    'lt' => ['name' => 'Tiubingenas', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Tübingen', 'description' => 'University city']
            ],
            ],
            // North Rhine-Westphalia
            [
                'name' => 'Cologne',
                'code' => 'DE-NW-COL',
                'latitude' => 50.9375,
                'longitude' => 6.9603,
                'population' => 1085664,
                'postal_codes' => ['50667', '50668', '50670'],
                'translations' => [
                    'lt' => ['name' => 'Kelnas', 'description' => 'Katedros miestas'],
                    'en' => ['name' => 'Cologne', 'description' => 'Cathedral city']
            ],
            ],
            [
                'name' => 'Düsseldorf',
                'code' => 'DE-NW-DUS',
                'latitude' => 51.2277,
                'longitude' => 6.7735,
                'population' => 619294,
                'postal_codes' => ['40210', '40211', '40212'],
                'translations' => [
                    'lt' => ['name' => 'Diūseldorfas', 'description' => 'Šiaurės Reino-Vestfalijos sostinė'],
                    'en' => ['name' => 'Düsseldorf', 'description' => 'Capital of North Rhine-Westphalia']
            ],
            ],
            [
                'name' => 'Dortmund',
                'code' => 'DE-NW-DOR',
                'latitude' => 51.5136,
                'longitude' => 7.4653,
                'population' => 588250,
                'postal_codes' => ['44135', '44137', '44139'],
                'translations' => [
                    'lt' => ['name' => 'Dortmundas', 'description' => 'Anglies pramonės centras'],
                    'en' => ['name' => 'Dortmund', 'description' => 'Coal industry center']
            ],
            ],
            [
                'name' => 'Essen',
                'code' => 'DE-NW-ESS',
                'latitude' => 51.4556,
                'longitude' => 7.0116,
                'population' => 582760,
                'postal_codes' => ['45127', '45128', '45130'],
                'translations' => [
                    'lt' => ['name' => 'Esenas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Essen', 'description' => 'Industrial city']
            ],
            ],
            [
                'name' => 'Duisburg',
                'code' => 'DE-NW-DUI',
                'latitude' => 51.4344,
                'longitude' => 6.7623,
                'population' => 498686,
                'postal_codes' => ['47051', '47053', '47055'],
                'translations' => [
                    'lt' => ['name' => 'Diūsburgas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Duisburg', 'description' => 'Port city']
            ],
            ],
            [
                'name' => 'Bochum',
                'code' => 'DE-NW-BOC',
                'latitude' => 51.4818,
                'longitude' => 7.2162,
                'population' => 365742,
                'postal_codes' => ['44787', '44789', '44791'],
                'translations' => [
                    'lt' => ['name' => 'Bochumas', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Bochum', 'description' => 'University city']
            ],
            ],
            [
                'name' => 'Wuppertal',
                'code' => 'DE-NW-WUP',
                'latitude' => 51.2562,
                'longitude' => 7.1508,
                'population' => 355100,
                'postal_codes' => ['42103', '42105', '42107'],
                'translations' => [
                    'lt' => ['name' => 'Vuperatalis', 'description' => 'Pakabinamosios geležinkelio miestas'],
                    'en' => ['name' => 'Wuppertal', 'description' => 'Suspension railway city']
            ],
            ],
            [
                'name' => 'Bielefeld',
                'code' => 'DE-NW-BIE',
                'latitude' => 52.0302,
                'longitude' => 8.5325,
                'population' => 334195,
                'postal_codes' => ['33602', '33604', '33605'],
                'translations' => [
                    'lt' => ['name' => 'Bilefeldas', 'description' => 'Tekstilės pramonės centras'],
                    'en' => ['name' => 'Bielefeld', 'description' => 'Textile industry center']
            ],
            ],
            [
                'name' => 'Bonn',
                'code' => 'DE-NW-BON',
                'latitude' => 50.7374,
                'longitude' => 7.0982,
                'population' => 330579,
                'postal_codes' => ['53111', '53113', '53115'],
                'translations' => [
                    'lt' => ['name' => 'Bonas', 'description' => 'Buvo Vokietijos sostinė'],
                    'en' => ['name' => 'Bonn', 'description' => 'Former capital of Germany']
            ],
            ],
            [
                'name' => 'Münster',
                'code' => 'DE-NW-MUN',
                'latitude' => 51.9607,
                'longitude' => 7.6261,
                'population' => 316403,
                'postal_codes' => ['48143', '48145', '48147'],
                'translations' => [
                    'lt' => ['name' => 'Miunsteris', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Münster', 'description' => 'University city']
            ],
            ],
            [
                'name' => 'Gelsenkirchen',
                'code' => 'DE-NW-GEL',
                'latitude' => 51.5177,
                'longitude' => 7.0857,
                'population' => 260654,
                'postal_codes' => ['45879', '45881', '45883'],
                'translations' => [
                    'lt' => ['name' => 'Gelzenkirhenas', 'description' => 'Anglies pramonės centras'],
                    'en' => ['name' => 'Gelsenkirchen', 'description' => 'Coal industry center']
            ],
            ],
            [
                'name' => 'Mönchengladbach',
                'code' => 'DE-NW-MON',
                'latitude' => 51.1805,
                'longitude' => 6.4428,
                'population' => 261454,
                'postal_codes' => ['41061', '41063', '41065'],
                'translations' => [
                    'lt' => ['name' => 'Menhengladbachas', 'description' => 'Tekstilės pramonės centras'],
                    'en' => ['name' => 'Mönchengladbach', 'description' => 'Textile industry center']
            ],
            ],
            [
                'name' => 'Aachen',
                'code' => 'DE-NW-AAC',
                'latitude' => 50.7753,
                'longitude' => 6.0839,
                'population' => 248960,
                'postal_codes' => ['52062', '52064', '52066'],
                'translations' => [
                    'lt' => ['name' => 'Achenas', 'description' => 'Karolio Didžiojo miestas'],
                    'en' => ['name' => 'Aachen', 'description' => "Charlemagne's city"]
            ],
            ],
            // Hesse
            [
                'name' => 'Frankfurt',
                'code' => 'DE-HE-FRA',
                'latitude' => 50.1109,
                'longitude' => 8.6821,
                'population' => 753056,
                'postal_codes' => ['60311', '60313', '60316'],
                'translations' => [
                    'lt' => ['name' => 'Frankfurtas', 'description' => 'Finansų centras'],
                    'en' => ['name' => 'Frankfurt', 'description' => 'Financial center']
            ],
            ],
            [
                'name' => 'Wiesbaden',
                'code' => 'DE-HE-WIE',
                'latitude' => 50.0826,
                'longitude' => 8.2493,
                'population' => 278950,
                'postal_codes' => ['65183', '65185', '65187'],
                'translations' => [
                    'lt' => ['name' => 'Viesbadenas', 'description' => 'Hesės sostinė'],
                    'en' => ['name' => 'Wiesbaden', 'description' => 'Capital of Hesse']
            ],
            ],
            [
                'name' => 'Kassel',
                'code' => 'DE-HE-KAS',
                'latitude' => 51.3127,
                'longitude' => 9.4797,
                'population' => 201048,
                'postal_codes' => ['34117', '34119', '34121'],
                'translations' => [
                    'lt' => ['name' => 'Kasselis', 'description' => 'Dokumentų parodos miestas'],
                    'en' => ['name' => 'Kassel', 'description' => 'Documenta exhibition city']
            ],
            ],
            [
                'name' => 'Darmstadt',
                'code' => 'DE-HE-DAR',
                'latitude' => 49.8728,
                'longitude' => 8.6512,
                'population' => 159174,
                'postal_codes' => ['64283', '64285', '64287'],
                'translations' => [
                    'lt' => ['name' => 'Darmštatas', 'description' => 'Mokslo miestas'],
                    'en' => ['name' => 'Darmstadt', 'description' => 'Science city']
            ],
            ],
            // Saxony
            [
                'name' => 'Dresden',
                'code' => 'DE-SN-DRE',
                'latitude' => 51.0504,
                'longitude' => 13.7373,
                'population' => 556780,
                'postal_codes' => ['01067', '01069', '01097'],
                'translations' => [
                    'lt' => ['name' => 'Drezdenas', 'description' => 'Saksonijos sostinė'],
                    'en' => ['name' => 'Dresden', 'description' => 'Capital of Saxony']
            ],
            ],
            [
                'name' => 'Leipzig',
                'code' => 'DE-SN-LEI',
                'latitude' => 51.3397,
                'longitude' => 12.3731,
                'population' => 597493,
                'postal_codes' => ['04109', '04105', '04107'],
                'translations' => [
                    'lt' => ['name' => 'Leipcigas', 'description' => 'Knygų mugės miestas'],
                    'en' => ['name' => 'Leipzig', 'description' => 'Book fair city']
            ],
            ],
            [
                'name' => 'Chemnitz',
                'code' => 'DE-SN-CHE',
                'latitude' => 50.8278,
                'longitude' => 12.9214,
                'population' => 243521,
                'postal_codes' => ['09111', '09113', '09117'],
                'translations' => [
                    'lt' => ['name' => 'Chemnicas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Chemnitz', 'description' => 'Industrial city']
            ],
            ],
            // Lower Saxony
            [
                'name' => 'Hanover',
                'code' => 'DE-NI-HAN',
                'latitude' => 52.3759,
                'longitude' => 9.732,
                'population' => 535061,
                'postal_codes' => ['30159', '30161', '30163'],
                'translations' => [
                    'lt' => ['name' => 'Hanoveris', 'description' => 'Žemutinės Saksonijos sostinė'],
                    'en' => ['name' => 'Hanover', 'description' => 'Capital of Lower Saxony']
            ],
            ],
            [
                'name' => 'Braunschweig',
                'code' => 'DE-NI-BRA',
                'latitude' => 52.2689,
                'longitude' => 10.5268,
                'population' => 248292,
                'postal_codes' => ['38100', '38102', '38104'],
                'translations' => [
                    'lt' => ['name' => 'Braunšvaigas', 'description' => 'Lionų miestas'],
                    'en' => ['name' => 'Braunschweig', 'description' => 'Lion city']
            ],
            ],
            [
                'name' => 'Osnabrück',
                'code' => 'DE-NI-OSN',
                'latitude' => 52.2799,
                'longitude' => 8.0472,
                'population' => 164748,
                'postal_codes' => ['49074', '49076', '49078'],
                'translations' => [
                    'lt' => ['name' => 'Osnabriukas', 'description' => 'Taikos miestas'],
                    'en' => ['name' => 'Osnabrück', 'description' => 'Peace city']
            ],
            ],
            // Brandenburg
            [
                'name' => 'Potsdam',
                'code' => 'DE-BB-POT',
                'latitude' => 52.4009,
                'longitude' => 13.0591,
                'population' => 182112,
                'postal_codes' => ['14467', '14469', '14471'],
                'translations' => [
                    'lt' => ['name' => 'Potsdamas', 'description' => 'Brandenburgo sostinė'],
                    'en' => ['name' => 'Potsdam', 'description' => 'Capital of Brandenburg']
            ],
            ],
            [
                'name' => 'Cottbus',
                'code' => 'DE-BB-COT',
                'latitude' => 51.7606,
                'longitude' => 14.3342,
                'population' => 99678,
                'postal_codes' => ['03042', '03044', '03046'],
                'translations' => [
                    'lt' => ['name' => 'Kotbusas', 'description' => 'Sorbų kultūros centras'],
                    'en' => ['name' => 'Cottbus', 'description' => 'Sorbian culture center']
            ],
            ],
            // Saxony-Anhalt
            [
                'name' => 'Magdeburg',
                'code' => 'DE-ST-MAG',
                'latitude' => 52.1205,
                'longitude' => 11.6276,
                'population' => 238697,
                'postal_codes' => ['39104', '39106', '39108'],
                'translations' => [
                    'lt' => ['name' => 'Magdeburgas', 'description' => 'Saksonijos-Anhalto sostinė'],
                    'en' => ['name' => 'Magdeburg', 'description' => 'Capital of Saxony-Anhalt']
            ],
            ],
            [
                'name' => 'Halle',
                'code' => 'DE-ST-HAL',
                'latitude' => 51.4964,
                'longitude' => 11.9682,
                'population' => 238762,
                'postal_codes' => ['06108', '06110', '06112'],
                'translations' => [
                    'lt' => ['name' => 'Halė', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Halle', 'description' => 'University city']
            ],
            ],
            // Thuringia
            [
                'name' => 'Erfurt',
                'code' => 'DE-TH-ERF',
                'latitude' => 50.9848,
                'longitude' => 11.0299,
                'population' => 213699,
                'postal_codes' => ['99084', '99086', '99089'],
                'translations' => [
                    'lt' => ['name' => 'Erfurtas', 'description' => 'Tiuringijos sostinė'],
                    'en' => ['name' => 'Erfurt', 'description' => 'Capital of Thuringia']
            ],
            ],
            [
                'name' => 'Jena',
                'code' => 'DE-TH-JEN',
                'latitude' => 50.9279,
                'longitude' => 11.5892,
                'population' => 111343,
                'postal_codes' => ['07743', '07745', '07747'],
                'translations' => [
                    'lt' => ['name' => 'Jena', 'description' => 'Optikos pramonės centras'],
                    'en' => ['name' => 'Jena', 'description' => 'Optics industry center']
            ],
            ],
            // Mecklenburg-Vorpommern
            [
                'name' => 'Rostock',
                'code' => 'DE-MV-ROS',
                'latitude' => 54.0887,
                'longitude' => 12.1401,
                'population' => 209191,
                'postal_codes' => ['18055', '18057', '18059'],
                'translations' => [
                    'lt' => ['name' => 'Rostokas', 'description' => 'Baltijos jūros uostas'],
                    'en' => ['name' => 'Rostock', 'description' => 'Baltic Sea port']
            ],
            ],
            [
                'name' => 'Schwerin',
                'code' => 'DE-MV-SCH',
                'latitude' => 53.6355,
                'longitude' => 11.4012,
                'population' => 95818,
                'postal_codes' => ['19053', '19055', '19057'],
                'translations' => [
                    'lt' => ['name' => 'Šverinas', 'description' => 'Meklenburgo-Pomeranijos sostinė'],
                    'en' => ['name' => 'Schwerin', 'description' => 'Capital of Mecklenburg-Vorpommern']
            ],
            ],
            // Schleswig-Holstein
            [
                'name' => 'Kiel',
                'code' => 'DE-SH-KIE',
                'latitude' => 54.3233,
                'longitude' => 10.1228,
                'population' => 247717,
                'postal_codes' => ['24103', '24105', '24107'],
                'translations' => [
                    'lt' => ['name' => 'Kilis', 'description' => 'Šlėzvigo-Holšteino sostinė'],
                    'en' => ['name' => 'Kiel', 'description' => 'Capital of Schleswig-Holstein']
            ],
            ],
            [
                'name' => 'Lübeck',
                'code' => 'DE-SH-LUB',
                'latitude' => 53.8655,
                'longitude' => 10.6866,
                'population' => 216530,
                'postal_codes' => ['23552', '23554', '23556'],
                'translations' => [
                    'lt' => ['name' => 'Liubekas', 'description' => 'Hanzos miestas'],
                    'en' => ['name' => 'Lübeck', 'description' => 'Hanseatic city']
            ],
            ],
            // Saarland
            [
                'name' => 'Saarbrücken',
                'code' => 'DE-SL-SAA',
                'latitude' => 49.2401,
                'longitude' => 6.9969,
                'population' => 180966,
                'postal_codes' => ['66111', '66113', '66115'],
                'translations' => [
                    'lt' => ['name' => 'Zarbrūkenas', 'description' => 'Zarlando sostinė'],
                    'en' => ['name' => 'Saarbrücken', 'description' => 'Capital of Saarland']
            ],
            ],
            // Bremen
            [
                'name' => 'Bremen',
                'code' => 'DE-HB-BRE',
                'latitude' => 53.0793,
                'longitude' => 8.8017,
                'population' => 569352,
                'postal_codes' => ['28195', '28197', '28199'],
                'translations' => [
                    'lt' => ['name' => 'Bremenas', 'description' => 'Hanzos miestas'],
                    'en' => ['name' => 'Bremen', 'description' => 'Hanseatic city']
            ],
            ],
            // Rhineland-Palatinate
            [
                'name' => 'Mainz',
                'code' => 'DE-RP-MAI',
                'latitude' => 49.9929,
                'longitude' => 8.2473,
                'population' => 217556,
                'postal_codes' => ['55116', '55118', '55120'],
                'translations' => [
                    'lt' => ['name' => 'Maincas', 'description' => 'Reino krašto-Pfalco sostinė'],
                    'en' => ['name' => 'Mainz', 'description' => 'Capital of Rhineland-Palatinate']
            ],
            ],
            [
                'name' => 'Ludwigshafen',
                'code' => 'DE-RP-LUD',
                'latitude' => 49.4812,
                'longitude' => 8.4464,
                'population' => 172145,
                'postal_codes' => ['67059', '67061', '67063'],
                'translations' => [
                    'lt' => ['name' => 'Ludvigshafenas', 'description' => 'Chemijos pramonės centras'],
                    'en' => ['name' => 'Ludwigshafen', 'description' => 'Chemical industry center']
            ],
            ],
            [
                'name' => 'Koblenz',
                'code' => 'DE-RP-KOB',
                'latitude' => 50.3569,
                'longitude' => 7.589,
                'population' => 113638,
                'postal_codes' => ['56068', '56070', '56072'],
                'translations' => [
                    'lt' => ['name' => 'Koblencas', 'description' => 'Upės susijungimo miestas'],
                    'en' => ['name' => 'Koblenz', 'description' => 'River confluence city']
            ],
            ],
            // Additional Bavaria cities
            [
                'name' => 'Aschaffenburg',
                'code' => 'DE-BY-ASC',
                'latitude' => 49.9770,
                'longitude' => 9.1521,
                'population' => 71000,
                'postal_codes' => ['63739'],
                'translations' => [
                    'lt' => ['name' => 'Aschaffenburgas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Aschaffenburg', 'description' => 'Industrial city']
            ],
            ],
            [
                'name' => 'Bamberg',
                'code' => 'DE-BY-BAM',
                'latitude' => 49.8967,
                'longitude' => 10.9023,
                'population' => 77000,
                'postal_codes' => ['96047'],
                'translations' => [
                    'lt' => ['name' => 'Bambergas', 'description' => 'UNESCO miestas'],
                    'en' => ['name' => 'Bamberg', 'description' => 'UNESCO city']
            ],
            ],
            [
                'name' => 'Bayreuth',
                'code' => 'DE-BY-BAY',
                'latitude' => 49.9480,
                'longitude' => 11.5783,
                'population' => 74000,
                'postal_codes' => ['95444'],
                'translations' => [
                    'lt' => ['name' => 'Baireutas', 'description' => 'Vagnerio festivalio miestas'],
                    'en' => ['name' => 'Bayreuth', 'description' => 'Wagner festival city']
            ],
            ],
            [
                'name' => 'Landshut',
                'code' => 'DE-BY-LAN',
                'latitude' => 48.5371,
                'longitude' => 12.1514,
                'population' => 72000,
                'postal_codes' => ['84028'],
                'translations' => [
                    'lt' => ['name' => 'Landšutas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Landshut', 'description' => 'Historic city']
            ],
            ],
            [
                'name' => 'Rosenheim',
                'code' => 'DE-BY-ROS',
                'latitude' => 47.8564,
                'longitude' => 12.1291,
                'population' => 63000,
                'postal_codes' => ['83022'],
                'translations' => [
                    'lt' => ['name' => 'Rozenheimas', 'description' => 'Alpų miestas'],
                    'en' => ['name' => 'Rosenheim', 'description' => 'Alpine city']
            ],
            ],
            [
                'name' => 'Schweinfurt',
                'code' => 'DE-BY-SCH',
                'latitude' => 50.0495,
                'longitude' => 10.2214,
                'population' => 54000,
                'postal_codes' => ['97421'],
                'translations' => [
                    'lt' => ['name' => 'Švainfurtas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Schweinfurt', 'description' => 'Industrial city']
            ],
            ],
            [
                'name' => 'Passau',
                'code' => 'DE-BY-PAS',
                'latitude' => 48.5665,
                'longitude' => 13.4312,
                'population' => 52000,
                'postal_codes' => ['94032'],
                'translations' => [
                    'lt' => ['name' => 'Pasau', 'description' => 'Upės susijungimo miestas'],
                    'en' => ['name' => 'Passau', 'description' => 'River confluence city']
            ],
            ],
            [
                'name' => 'Straubing',
                'code' => 'DE-BY-STR',
                'latitude' => 48.8814,
                'longitude' => 12.5739,
                'population' => 47000,
                'postal_codes' => ['94315'],
                'translations' => [
                    'lt' => ['name' => 'Straubingas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Straubing', 'description' => 'Industrial city']
            ],
            ],
            [
                'name' => 'Amberg',
                'code' => 'DE-BY-AMB',
                'latitude' => 49.4439,
                'longitude' => 11.8625,
                'population' => 42000,
                'postal_codes' => ['92224'],
                'translations' => [
                    'lt' => ['name' => 'Ambergas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Amberg', 'description' => 'Historic city']
            ],
            ],
            [
                'name' => 'Weiden',
                'code' => 'DE-BY-WEI',
                'latitude' => 49.6761,
                'longitude' => 12.1561,
                'population' => 42000,
                'postal_codes' => ['92637'],
                'translations' => [
                    'lt' => ['name' => 'Veidenas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Weiden', 'description' => 'Industrial city']
            ],
            ],
            // Additional Baden-Württemberg cities
            [
                'name' => 'Pforzheim',
                'code' => 'DE-BW-PFO',
                'latitude' => 48.8944,
                'longitude' => 8.7089,
                'population' => 125000,
                'postal_codes' => ['75172'],
                'translations' => [
                    'lt' => ['name' => 'Pforcheimas', 'description' => 'Aukso miestas'],
                    'en' => ['name' => 'Pforzheim', 'description' => 'Gold city']
            ],
            ],
            [
                'name' => 'Reutlingen',
                'code' => 'DE-BW-REU',
                'latitude' => 48.4919,
                'longitude' => 9.2042,
                'population' => 116000,
                'postal_codes' => ['72764'],
                'translations' => [
                    'lt' => ['name' => 'Reutlingenas', 'description' => 'Tekstilės pramonės centras'],
                    'en' => ['name' => 'Reutlingen', 'description' => 'Textile industry center']
            ],
            ],
            [
                'name' => 'Esslingen',
                'code' => 'DE-BW-ESS',
                'latitude' => 48.7406,
                'longitude' => 9.3067,
                'population' => 94000,
                'postal_codes' => ['73728'],
                'translations' => [
                    'lt' => ['name' => 'Eslingenas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Esslingen', 'description' => 'Historic city']
            ],
            ],
            [
                'name' => 'Ludwigsburg',
                'code' => 'DE-BW-LUD',
                'latitude' => 48.8972,
                'longitude' => 9.1919,
                'population' => 93000,
                'postal_codes' => ['71634'],
                'translations' => [
                    'lt' => ['name' => 'Ludvigburgas', 'description' => 'Baroko miestas'],
                    'en' => ['name' => 'Ludwigsburg', 'description' => 'Baroque city']
            ],
            ],
            [
                'name' => 'Tuttlingen',
                'code' => 'DE-BW-TUT',
                'latitude' => 47.9850,
                'longitude' => 8.8172,
                'population' => 36000,
                'postal_codes' => ['78532'],
                'translations' => [
                    'lt' => ['name' => 'Tutlingenas', 'description' => 'Medicinos technikos centras'],
                    'en' => ['name' => 'Tuttlingen', 'description' => 'Medical technology center']
            ],
            ],
            [
                'name' => 'Villingen-Schwenningen',
                'code' => 'DE-BW-VIL',
                'latitude' => 48.0606,
                'longitude' => 8.4589,
                'population' => 85000,
                'postal_codes' => ['78048'],
                'translations' => [
                    'lt' => ['name' => 'Vilingenas-Šveningenas', 'description' => 'Dvigubas miestas'],
                    'en' => ['name' => 'Villingen-Schwenningen', 'description' => 'Twin city']
            ],
            ],
            [
                'name' => 'Sindelfingen',
                'code' => 'DE-BW-SIN',
                'latitude' => 48.7131,
                'longitude' => 9.0033,
                'population' => 64000,
                'postal_codes' => ['71063'],
                'translations' => [
                    'lt' => ['name' => 'Sindelfingenas', 'description' => 'Automobilių pramonės centras'],
                    'en' => ['name' => 'Sindelfingen', 'description' => 'Automotive industry center']
            ],
            ],
            [
                'name' => 'Ravensburg',
                'code' => 'DE-BW-RAV',
                'latitude' => 47.7819,
                'longitude' => 9.6114,
                'population' => 50000,
                'postal_codes' => ['88212'],
                'translations' => [
                    'lt' => ['name' => 'Ravensburgas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Ravensburg', 'description' => 'Historic city']
            ],
            ],
            [
                'name' => 'Baden-Baden',
                'code' => 'DE-BW-BAD',
                'latitude' => 48.7619,
                'longitude' => 8.2408,
                'population' => 55000,
                'postal_codes' => ['76530'],
                'translations' => [
                    'lt' => ['name' => 'Badenas-Badenas', 'description' => 'Kurortinis miestas'],
                    'en' => ['name' => 'Baden-Baden', 'description' => 'Spa city']
            ],
            ],
            [
                'name' => 'Konstanz',
                'code' => 'DE-BW-KON',
                'latitude' => 47.6633,
                'longitude' => 9.1753,
                'population' => 84000,
                'postal_codes' => ['78462'],
                'translations' => [
                    'lt' => ['name' => 'Konstancas', 'description' => 'Bodeno ežero miestas'],
                    'en' => ['name' => 'Konstanz', 'description' => 'Lake Constance city'],
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
                    'country_id' => $germany->id,
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
