<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class SpainCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $spain = Country::where('cca2', 'ES')->first();
        $euZone = Zone::where('code', 'EU')->first();

        // Regions are no longer used in the database schema

        $cities = [
            // Madrid
            [
                'name' => 'Madrid',
                'code' => 'ES-MD-MAD',
                'is_capital' => true,
                'is_default' => true,
                'latitude' => 40.4168,
                'longitude' => -3.7038,
                'population' => 3223334,
                'postal_codes' => ['28001', '28002', '28003'],
                'translations' => [
                    'lt' => ['name' => 'Madridas', 'description' => 'Ispanijos sostinė'],
                    'en' => ['name' => 'Madrid', 'description' => 'Capital of Spain'],
                ],
            ],
            [
                'name' => 'Alcalá de Henares',
                'code' => 'ES-MD-ALC',
                'latitude' => 40.4817,
                'longitude' => -3.3642,
                'population' => 196888,
                'postal_codes' => ['28801'],
                'translations' => [
                    'lt' => ['name' => 'Alkalá de Henares', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Alcalá de Henares', 'description' => 'University city'],
                ],
            ],
            // Catalonia
            [
                'name' => 'Barcelona',
                'code' => 'ES-CT-BAR',
                'latitude' => 41.3851,
                'longitude' => 2.1734,
                'population' => 1636762,
                'postal_codes' => ['08001', '08002', '08003'],
                'translations' => [
                    'lt' => ['name' => 'Barselona', 'description' => 'Katalonijos sostinė'],
                    'en' => ['name' => 'Barcelona', 'description' => 'Capital of Catalonia'],
                ],
            ],
            [
                'name' => "L'Hospitalet de Llobregat",
                'code' => 'ES-CT-HOS',
                'latitude' => 41.3597,
                'longitude' => 2.0997,
                'population' => 264923,
                'postal_codes' => ['08901'],
                'translations' => [
                    'lt' => ['name' => "L'Hospitalet de Llobregat", 'description' => 'Barselonos priemiestis'],
                    'en' => ['name' => "L'Hospitalet de Llobregat", 'description' => 'Barcelona suburb'],
                ],
            ],
            [
                'name' => 'Badalona',
                'code' => 'ES-CT-BAD',
                'latitude' => 41.45,
                'longitude' => 2.2472,
                'population' => 223166,
                'postal_codes' => ['08911'],
                'translations' => [
                    'lt' => ['name' => 'Badalona', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'Badalona', 'description' => 'Seaside city'],
                ],
            ],
            [
                'name' => 'Sabadell',
                'code' => 'ES-CT-SAB',
                'latitude' => 41.5481,
                'longitude' => 2.1075,
                'population' => 216520,
                'postal_codes' => ['08201'],
                'translations' => [
                    'lt' => ['name' => 'Sabadell', 'description' => 'Tekstilės pramonės centras'],
                    'en' => ['name' => 'Sabadell', 'description' => 'Textile industry center'],
                ],
            ],
            [
                'name' => 'Terrassa',
                'code' => 'ES-CT-TER',
                'latitude' => 41.5667,
                'longitude' => 2.0167,
                'population' => 223011,
                'postal_codes' => ['08221'],
                'translations' => [
                    'lt' => ['name' => 'Terrassa', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Terrassa', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Lleida',
                'code' => 'ES-CT-LLE',
                'latitude' => 41.6167,
                'longitude' => 0.6333,
                'population' => 140797,
                'postal_codes' => ['25001'],
                'translations' => [
                    'lt' => ['name' => 'Lleida', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Lleida', 'description' => 'University city'],
                ],
            ],
            [
                'name' => 'Tarragona',
                'code' => 'ES-CT-TAR',
                'latitude' => 41.1167,
                'longitude' => 1.25,
                'population' => 140323,
                'postal_codes' => ['43001'],
                'translations' => [
                    'lt' => ['name' => 'Tarragona', 'description' => 'Romėnų miestas'],
                    'en' => ['name' => 'Tarragona', 'description' => 'Roman city'],
                ],
            ],
            [
                'name' => 'Girona',
                'code' => 'ES-CT-GIR',
                'latitude' => 41.9833,
                'longitude' => 2.8167,
                'population' => 103369,
                'postal_codes' => ['17001'],
                'translations' => [
                    'lt' => ['name' => 'Girona', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Girona', 'description' => 'Historic city'],
                ],
            ],
            // Andalusia
            [
                'name' => 'Seville',
                'code' => 'ES-AN-SEV',
                'latitude' => 37.3891,
                'longitude' => -5.9845,
                'population' => 688711,
                'postal_codes' => ['41001', '41002', '41003'],
                'translations' => [
                    'lt' => ['name' => 'Sevilija', 'description' => 'Andalūzijos sostinė'],
                    'en' => ['name' => 'Seville', 'description' => 'Capital of Andalusia'],
                ],
            ],
            [
                'name' => 'Málaga',
                'code' => 'ES-AN-MAL',
                'latitude' => 36.7213,
                'longitude' => -4.4214,
                'population' => 578460,
                'postal_codes' => ['29001', '29002', '29003'],
                'translations' => [
                    'lt' => ['name' => 'Malaga', 'description' => 'Kurortinis miestas'],
                    'en' => ['name' => 'Málaga', 'description' => 'Resort city'],
                ],
            ],
            [
                'name' => 'Córdoba',
                'code' => 'ES-AN-COR',
                'latitude' => 37.8882,
                'longitude' => -4.7794,
                'population' => 325708,
                'postal_codes' => ['14001'],
                'translations' => [
                    'lt' => ['name' => 'Kordoba', 'description' => 'UNESCO miestas'],
                    'en' => ['name' => 'Córdoba', 'description' => 'UNESCO city'],
                ],
            ],
            [
                'name' => 'Granada',
                'code' => 'ES-AN-GRA',
                'latitude' => 37.1773,
                'longitude' => -3.5986,
                'population' => 232462,
                'postal_codes' => ['18001'],
                'translations' => [
                    'lt' => ['name' => 'Granada', 'description' => 'Alhambros miestas'],
                    'en' => ['name' => 'Granada', 'description' => 'Alhambra city'],
                ],
            ],
            [
                'name' => 'Jerez de la Frontera',
                'code' => 'ES-AN-JER',
                'latitude' => 36.686,
                'longitude' => -6.136,
                'population' => 212876,
                'postal_codes' => ['11401'],
                'translations' => [
                    'lt' => ['name' => 'Jerez de la Frontera', 'description' => 'Šerio miestas'],
                    'en' => ['name' => 'Jerez de la Frontera', 'description' => 'Sherry city'],
                ],
            ],
            [
                'name' => 'Almería',
                'code' => 'ES-AN-ALM',
                'latitude' => 36.8381,
                'longitude' => -2.4597,
                'population' => 200753,
                'postal_codes' => ['04001'],
                'translations' => [
                    'lt' => ['name' => 'Almerija', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'Almería', 'description' => 'Seaside city'],
                ],
            ],
            [
                'name' => 'Huelva',
                'code' => 'ES-AN-HUE',
                'latitude' => 37.2583,
                'longitude' => -6.9508,
                'population' => 143837,
                'postal_codes' => ['21001'],
                'translations' => [
                    'lt' => ['name' => 'Huelva', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Huelva', 'description' => 'Port city'],
                ],
            ],
            [
                'name' => 'Cádiz',
                'code' => 'ES-AN-CAD',
                'latitude' => 36.5298,
                'longitude' => -6.2934,
                'population' => 116979,
                'postal_codes' => ['11001'],
                'translations' => [
                    'lt' => ['name' => 'Kadisas', 'description' => 'Senovinis miestas'],
                    'en' => ['name' => 'Cádiz', 'description' => 'Ancient city'],
                ],
            ],
            // Valencia
            [
                'name' => 'Valencia',
                'code' => 'ES-VC-VAL',
                'latitude' => 39.4699,
                'longitude' => -0.3763,
                'population' => 800215,
                'postal_codes' => ['46001', '46002', '46003'],
                'translations' => [
                    'lt' => ['name' => 'Valensija', 'description' => 'Valensijos sostinė'],
                    'en' => ['name' => 'Valencia', 'description' => 'Capital of Valencia'],
                ],
            ],
            [
                'name' => 'Alicante',
                'code' => 'ES-VC-ALI',
                'latitude' => 38.3452,
                'longitude' => -0.481,
                'population' => 337304,
                'postal_codes' => ['03001'],
                'translations' => [
                    'lt' => ['name' => 'Alicantė', 'description' => 'Kurortinis miestas'],
                    'en' => ['name' => 'Alicante', 'description' => 'Resort city'],
                ],
            ],
            [
                'name' => 'Elche',
                'code' => 'ES-VC-ELC',
                'latitude' => 38.2622,
                'longitude' => -0.7011,
                'population' => 234765,
                'postal_codes' => ['03201'],
                'translations' => [
                    'lt' => ['name' => 'Elche', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Elche', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Castellón de la Plana',
                'code' => 'ES-VC-CAS',
                'latitude' => 39.9861,
                'longitude' => -0.0369,
                'population' => 171728,
                'postal_codes' => ['12001'],
                'translations' => [
                    'lt' => ['name' => 'Castellón de la Plana', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'Castellón de la Plana', 'description' => 'Seaside city'],
                ],
            ],
            // Galicia
            [
                'name' => 'Vigo',
                'code' => 'ES-GA-VIG',
                'latitude' => 42.2406,
                'longitude' => -8.7206,
                'population' => 293837,
                'postal_codes' => ['36201'],
                'translations' => [
                    'lt' => ['name' => 'Vigo', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Vigo', 'description' => 'Port city'],
                ],
            ],
            [
                'name' => 'A Coruña',
                'code' => 'ES-GA-ACO',
                'latitude' => 43.3713,
                'longitude' => -8.396,
                'population' => 245711,
                'postal_codes' => ['15001'],
                'translations' => [
                    'lt' => ['name' => 'A Coruña', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'A Coruña', 'description' => 'Seaside city'],
                ],
            ],
            [
                'name' => 'Santiago de Compostela',
                'code' => 'ES-GA-SAN',
                'latitude' => 42.8805,
                'longitude' => -8.5456,
                'population' => 97808,
                'postal_codes' => ['15701'],
                'translations' => [
                    'lt' => ['name' => 'Santiago de Compostela', 'description' => 'Pilgrimų miestas'],
                    'en' => ['name' => 'Santiago de Compostela', 'description' => 'Pilgrimage city'],
                ],
            ],
            [
                'name' => 'Lugo',
                'code' => 'ES-GA-LUG',
                'latitude' => 43.0121,
                'longitude' => -7.5559,
                'population' => 98225,
                'postal_codes' => ['27001'],
                'translations' => [
                    'lt' => ['name' => 'Lugo', 'description' => 'Romėnų miestas'],
                    'en' => ['name' => 'Lugo', 'description' => 'Roman city'],
                ],
            ],
            // Castile and León
            [
                'name' => 'Valladolid',
                'code' => 'ES-CL-VAL',
                'latitude' => 41.6523,
                'longitude' => -4.7245,
                'population' => 298412,
                'postal_codes' => ['47001'],
                'translations' => [
                    'lt' => ['name' => 'Valladolid', 'description' => 'Kastilijos ir Leono sostinė'],
                    'en' => ['name' => 'Valladolid', 'description' => 'Capital of Castile and León'],
                ],
            ],
            [
                'name' => 'León',
                'code' => 'ES-CL-LEO',
                'latitude' => 42.5987,
                'longitude' => -5.5671,
                'population' => 124303,
                'postal_codes' => ['24001'],
                'translations' => [
                    'lt' => ['name' => 'Leonas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'León', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Burgos',
                'code' => 'ES-CL-BUR',
                'latitude' => 42.3409,
                'longitude' => -3.6997,
                'population' => 175623,
                'postal_codes' => ['09001'],
                'translations' => [
                    'lt' => ['name' => 'Burgosas', 'description' => 'Katedros miestas'],
                    'en' => ['name' => 'Burgos', 'description' => 'Cathedral city'],
                ],
            ],
            [
                'name' => 'Salamanca',
                'code' => 'ES-CL-SAL',
                'latitude' => 40.9701,
                'longitude' => -5.6635,
                'population' => 144436,
                'postal_codes' => ['37001'],
                'translations' => [
                    'lt' => ['name' => 'Salamanka', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Salamanca', 'description' => 'University city'],
                ],
            ],
            // Basque Country
            [
                'name' => 'Bilbao',
                'code' => 'ES-PV-BIL',
                'latitude' => 43.2627,
                'longitude' => -2.9253,
                'population' => 346405,
                'postal_codes' => ['48001'],
                'translations' => [
                    'lt' => ['name' => 'Bilbao', 'description' => 'Baskų šalies centras'],
                    'en' => ['name' => 'Bilbao', 'description' => 'Center of Basque Country'],
                ],
            ],
            [
                'name' => 'Vitoria-Gasteiz',
                'code' => 'ES-PV-VIT',
                'latitude' => 42.8467,
                'longitude' => -2.6716,
                'population' => 252571,
                'postal_codes' => ['01001'],
                'translations' => [
                    'lt' => ['name' => 'Vitoria-Gasteiz', 'description' => 'Baskų šalies sostinė'],
                    'en' => ['name' => 'Vitoria-Gasteiz', 'description' => 'Capital of Basque Country'],
                ],
            ],
            [
                'name' => 'San Sebastián',
                'code' => 'ES-PV-SAN',
                'latitude' => 43.3183,
                'longitude' => -1.9812,
                'population' => 188240,
                'postal_codes' => ['20001'],
                'translations' => [
                    'lt' => ['name' => 'San Sebastianas', 'description' => 'Kurortinis miestas'],
                    'en' => ['name' => 'San Sebastián', 'description' => 'Resort city'],
                ],
            ],
            // Canary Islands
            [
                'name' => 'Las Palmas de Gran Canaria',
                'code' => 'ES-CN-LAS',
                'latitude' => 28.1248,
                'longitude' => -15.43,
                'population' => 381123,
                'postal_codes' => ['35001'],
                'translations' => [
                    'lt' => ['name' => 'Las Palmas de Gran Canaria', 'description' => 'Kanarų salų sostinė'],
                    'en' => ['name' => 'Las Palmas de Gran Canaria', 'description' => 'Capital of Canary Islands'],
                ],
            ],
            [
                'name' => 'Santa Cruz de Tenerife',
                'code' => 'ES-CN-SAN',
                'latitude' => 28.4698,
                'longitude' => -16.2549,
                'population' => 204856,
                'postal_codes' => ['38001'],
                'translations' => [
                    'lt' => ['name' => 'Santa Cruz de Tenerife', 'description' => 'Tenerifės sostinė'],
                    'en' => ['name' => 'Santa Cruz de Tenerife', 'description' => 'Capital of Tenerife'],
                ],
            ],
            // Castile-La Mancha
            [
                'name' => 'Toledo',
                'code' => 'ES-CM-TOL',
                'latitude' => 39.8628,
                'longitude' => -4.0273,
                'population' => 85911,
                'postal_codes' => ['45001'],
                'translations' => [
                    'lt' => ['name' => 'Toledo', 'description' => 'UNESCO miestas'],
                    'en' => ['name' => 'Toledo', 'description' => 'UNESCO city'],
                ],
            ],
            [
                'name' => 'Albacete',
                'code' => 'ES-CM-ALB',
                'latitude' => 38.9977,
                'longitude' => -1.8601,
                'population' => 172722,
                'postal_codes' => ['02001'],
                'translations' => [
                    'lt' => ['name' => 'Albacete', 'description' => 'Kastilijos-La Mančos sostinė'],
                    'en' => ['name' => 'Albacete', 'description' => 'Capital of Castile-La Mancha'],
                ],
            ],
            // Murcia
            [
                'name' => 'Murcia',
                'code' => 'ES-MC-MUR',
                'latitude' => 37.9922,
                'longitude' => -1.1307,
                'population' => 459403,
                'postal_codes' => ['30001'],
                'translations' => [
                    'lt' => ['name' => 'Murcija', 'description' => 'Murcijos sostinė'],
                    'en' => ['name' => 'Murcia', 'description' => 'Capital of Murcia'],
                ],
            ],
            // Aragon
            [
                'name' => 'Zaragoza',
                'code' => 'ES-AR-ZAR',
                'latitude' => 41.6488,
                'longitude' => -0.8891,
                'population' => 675301,
                'postal_codes' => ['50001'],
                'translations' => [
                    'lt' => ['name' => 'Saragosa', 'description' => 'Aragono sostinė'],
                    'en' => ['name' => 'Zaragoza', 'description' => 'Capital of Aragon'],
                ],
            ],
            // Extremadura
            [
                'name' => 'Badajoz',
                'code' => 'ES-EX-BAD',
                'latitude' => 38.8794,
                'longitude' => -6.9707,
                'population' => 150610,
                'postal_codes' => ['06001'],
                'translations' => [
                    'lt' => ['name' => 'Badachosas', 'description' => 'Ekstremadūros sostinė'],
                    'en' => ['name' => 'Badajoz', 'description' => 'Capital of Extremadura'],
                ],
            ],
            // Balearic Islands
            [
                'name' => 'Palma',
                'code' => 'ES-IB-PAL',
                'latitude' => 39.5696,
                'longitude' => 2.6502,
                'population' => 416065,
                'postal_codes' => ['07001'],
                'translations' => [
                    'lt' => ['name' => 'Palma', 'description' => 'Balearų salų sostinė'],
                    'en' => ['name' => 'Palma', 'description' => 'Capital of Balearic Islands'],
                ],
            ],
            // Asturias
            [
                'name' => 'Oviedo',
                'code' => 'ES-AS-OVI',
                'latitude' => 43.3614,
                'longitude' => -5.8593,
                'population' => 220301,
                'postal_codes' => ['33001'],
                'translations' => [
                    'lt' => ['name' => 'Oviedas', 'description' => 'Astūrijos sostinė'],
                    'en' => ['name' => 'Oviedo', 'description' => 'Capital of Asturias'],
                ],
            ],
            // Navarre
            [
                'name' => 'Pamplona',
                'code' => 'ES-NC-PAM',
                'latitude' => 42.8182,
                'longitude' => -1.6443,
                'population' => 203081,
                'postal_codes' => ['31001'],
                'translations' => [
                    'lt' => ['name' => 'Pamplona', 'description' => 'Navaros sostinė'],
                    'en' => ['name' => 'Pamplona', 'description' => 'Capital of Navarre'],
                ],
            ],
            // Cantabria
            [
                'name' => 'Santander',
                'code' => 'ES-CB-SAN',
                'latitude' => 43.4623,
                'longitude' => -3.8099,
                'population' => 172044,
                'postal_codes' => ['39001'],
                'translations' => [
                    'lt' => ['name' => 'Santanderis', 'description' => 'Kantabrijos sostinė'],
                    'en' => ['name' => 'Santander', 'description' => 'Capital of Cantabria'],
                ],
            ],
            // La Rioja
            [
                'name' => 'Logroño',
                'code' => 'ES-RI-LOG',
                'latitude' => 42.4627,
                'longitude' => -2.4449,
                'population' => 151113,
                'postal_codes' => ['26001'],
                'translations' => [
                    'lt' => ['name' => 'Logronjas', 'description' => 'La Riochos sostinė'],
                    'en' => ['name' => 'Logroño', 'description' => 'Capital of La Rioja'],
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
                    'country_id' => $spain->id,
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
