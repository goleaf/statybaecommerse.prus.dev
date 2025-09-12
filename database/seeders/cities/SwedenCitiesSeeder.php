<?php declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\Translations\CityTranslation;
use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class SwedenCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $sweden = Country::where('cca2', 'SE')->first();
        $euZone = Zone::where('code', 'EU')->first();

        // Get regions
        $stockholmRegion = Region::where('code', 'SE-AB')->first();
        $vastraGotalandRegion = Region::where('code', 'SE-O')->first();
        $skaneRegion = Region::where('code', 'SE-M')->first();
        $uppsalaRegion = Region::where('code', 'SE-C')->first();
        $ostergotlandRegion = Region::where('code', 'SE-E')->first();
        $jonkopingRegion = Region::where('code', 'SE-F')->first();
        $kronobergRegion = Region::where('code', 'SE-G')->first();
        $kalmarRegion = Region::where('code', 'SE-H')->first();
        $gotlandRegion = Region::where('code', 'SE-I')->first();
        $blekingeRegion = Region::where('code', 'SE-K')->first();
        $skaneRegion2 = Region::where('code', 'SE-M')->first();
        $hallandRegion = Region::where('code', 'SE-N')->first();
        $varmlandRegion = Region::where('code', 'SE-S')->first();
        $orebroRegion = Region::where('code', 'SE-T')->first();
        $vastmanlandRegion = Region::where('code', 'SE-U')->first();
        $dalarnaRegion = Region::where('code', 'SE-W')->first();
        $gavleborgRegion = Region::where('code', 'SE-X')->first();
        $vasternorrlandRegion = Region::where('code', 'SE-Y')->first();
        $jamtlandRegion = Region::where('code', 'SE-Z')->first();
        $vasterbottenRegion = Region::where('code', 'SE-AC')->first();
        $norrbottenRegion = Region::where('code', 'SE-BD')->first();

        $cities = [
            // Stockholm
            [
                'name' => 'Stockholm',
                'code' => 'SE-AB-STO',
                'is_capital' => true,
                'is_default' => true,
                'region_id' => $stockholmRegion?->id,
                'latitude' => 59.3293,
                'longitude' => 18.0686,
                'population' => 975551,
                'postal_codes' => ['10000', '10001', '10002'],
                'translations' => [
                    'lt' => ['name' => 'Stokholmas', 'description' => 'Švedijos sostinė'],
                    'en' => ['name' => 'Stockholm', 'description' => 'Capital of Sweden'],
                ],
            ],
            [
                'name' => 'Södertälje',
                'code' => 'SE-AB-SOD',
                'region_id' => $stockholmRegion?->id,
                'latitude' => 59.1956,
                'longitude' => 17.6253,
                'population' => 100000,
                'postal_codes' => ['15100'],
                'translations' => [
                    'lt' => ['name' => 'Soderteljė', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Södertälje', 'description' => 'Industrial city'],
                ],
            ],
            // Västra Götaland
            [
                'name' => 'Gothenburg',
                'code' => 'SE-O-GOT',
                'region_id' => $vastraGotalandRegion?->id,
                'latitude' => 57.7089,
                'longitude' => 11.9746,
                'population' => 579281,
                'postal_codes' => ['40000'],
                'translations' => [
                    'lt' => ['name' => 'Geteborgas', 'description' => 'Antrasis didžiausias Švedijos miestas'],
                    'en' => ['name' => 'Gothenburg', 'description' => 'Second largest city in Sweden'],
                ],
            ],
            [
                'name' => 'Malmö',
                'code' => 'SE-O-MAL',
                'region_id' => $vastraGotalandRegion?->id,
                'latitude' => 55.605,
                'longitude' => 13.0038,
                'population' => 347949,
                'postal_codes' => ['20000'],
                'translations' => [
                    'lt' => ['name' => 'Malmiu', 'description' => 'Pietų Švedijos centras'],
                    'en' => ['name' => 'Malmö', 'description' => 'Center of Southern Sweden'],
                ],
            ],
            [
                'name' => 'Västerås',
                'code' => 'SE-O-VAS',
                'region_id' => $vastraGotalandRegion?->id,
                'latitude' => 59.6162,
                'longitude' => 16.5528,
                'population' => 150000,
                'postal_codes' => ['72000'],
                'translations' => [
                    'lt' => ['name' => 'Vesterosas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Västerås', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Örebro',
                'code' => 'SE-O-ORE',
                'region_id' => $vastraGotalandRegion?->id,
                'latitude' => 59.2741,
                'longitude' => 15.2066,
                'population' => 156000,
                'postal_codes' => ['70000'],
                'translations' => [
                    'lt' => ['name' => 'Orebro', 'description' => 'Centrinės Švedijos miestas'],
                    'en' => ['name' => 'Örebro', 'description' => 'Central Swedish city'],
                ],
            ],
            // Skåne
            [
                'name' => 'Lund',
                'code' => 'SE-M-LUN',
                'region_id' => $skaneRegion?->id,
                'latitude' => 55.7047,
                'longitude' => 13.191,
                'population' => 121893,
                'postal_codes' => ['22000'],
                'translations' => [
                    'lt' => ['name' => 'Lundas', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Lund', 'description' => 'University city'],
                ],
            ],
            [
                'name' => 'Helsingborg',
                'code' => 'SE-M-HEL',
                'region_id' => $skaneRegion?->id,
                'latitude' => 56.0465,
                'longitude' => 12.6945,
                'population' => 149280,
                'postal_codes' => ['25000'],
                'translations' => [
                    'lt' => ['name' => 'Helsingborgas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Helsingborg', 'description' => 'Port city'],
                ],
            ],
            [
                'name' => 'Kristianstad',
                'code' => 'SE-M-KRI',
                'region_id' => $skaneRegion?->id,
                'latitude' => 56.0294,
                'longitude' => 14.1567,
                'population' => 41000,
                'postal_codes' => ['29000'],
                'translations' => [
                    'lt' => ['name' => 'Kristianstadas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Kristianstad', 'description' => 'Historic city'],
                ],
            ],
            // Uppsala
            [
                'name' => 'Uppsala',
                'code' => 'SE-C-UPP',
                'region_id' => $uppsalaRegion?->id,
                'latitude' => 59.8586,
                'longitude' => 17.6389,
                'population' => 230767,
                'postal_codes' => ['75000'],
                'translations' => [
                    'lt' => ['name' => 'Upsala', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Uppsala', 'description' => 'University city'],
                ],
            ],
            // Östergötland
            [
                'name' => 'Linköping',
                'code' => 'SE-E-LIN',
                'region_id' => $ostergotlandRegion?->id,
                'latitude' => 58.4108,
                'longitude' => 15.6214,
                'population' => 164616,
                'postal_codes' => ['58000'],
                'translations' => [
                    'lt' => ['name' => 'Linkiopingas', 'description' => 'Technologijų centras'],
                    'en' => ['name' => 'Linköping', 'description' => 'Technology center'],
                ],
            ],
            [
                'name' => 'Norrköping',
                'code' => 'SE-E-NOR',
                'region_id' => $ostergotlandRegion?->id,
                'latitude' => 58.5877,
                'longitude' => 16.1924,
                'population' => 143171,
                'postal_codes' => ['60000'],
                'translations' => [
                    'lt' => ['name' => 'Norkiopingas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Norrköping', 'description' => 'Industrial city'],
                ],
            ],
            // Jönköping
            [
                'name' => 'Jönköping',
                'code' => 'SE-F-JON',
                'region_id' => $jonkopingRegion?->id,
                'latitude' => 57.7826,
                'longitude' => 14.1618,
                'population' => 141081,
                'postal_codes' => ['55000'],
                'translations' => [
                    'lt' => ['name' => 'Jonkiopingas', 'description' => 'Junkopingo sostinė'],
                    'en' => ['name' => 'Jönköping', 'description' => 'Capital of Jönköping'],
                ],
            ],
            // Kronoberg
            [
                'name' => 'Växjö',
                'code' => 'SE-G-VAX',
                'region_id' => $kronobergRegion?->id,
                'latitude' => 56.8777,
                'longitude' => 14.8091,
                'population' => 90000,
                'postal_codes' => ['35000'],
                'translations' => [
                    'lt' => ['name' => 'Veksio', 'description' => 'Kronobergo sostinė'],
                    'en' => ['name' => 'Växjö', 'description' => 'Capital of Kronoberg'],
                ],
            ],
            // Kalmar
            [
                'name' => 'Kalmar',
                'code' => 'SE-H-KAL',
                'region_id' => $kalmarRegion?->id,
                'latitude' => 56.6616,
                'longitude' => 16.3616,
                'population' => 41000,
                'postal_codes' => ['39000'],
                'translations' => [
                    'lt' => ['name' => 'Kalmaras', 'description' => 'Kalmaro sostinė'],
                    'en' => ['name' => 'Kalmar', 'description' => 'Capital of Kalmar'],
                ],
            ],
            // Gotland
            [
                'name' => 'Visby',
                'code' => 'SE-I-VIS',
                'region_id' => $gotlandRegion?->id,
                'latitude' => 57.6344,
                'longitude' => 18.2947,
                'population' => 24000,
                'postal_codes' => ['62000'],
                'translations' => [
                    'lt' => ['name' => 'Visbis', 'description' => 'Gotlando sostinė'],
                    'en' => ['name' => 'Visby', 'description' => 'Capital of Gotland'],
                ],
            ],
            // Blekinge
            [
                'name' => 'Karlskrona',
                'code' => 'SE-K-KAR',
                'region_id' => $blekingeRegion?->id,
                'latitude' => 56.1612,
                'longitude' => 15.5869,
                'population' => 66000,
                'postal_codes' => ['37000'],
                'translations' => [
                    'lt' => ['name' => 'Karlskrona', 'description' => 'Blekingės sostinė'],
                    'en' => ['name' => 'Karlskrona', 'description' => 'Capital of Blekinge'],
                ],
            ],
            // Halland
            [
                'name' => 'Halmstad',
                'code' => 'SE-N-HAL',
                'region_id' => $hallandRegion?->id,
                'latitude' => 56.6745,
                'longitude' => 12.8572,
                'population' => 70000,
                'postal_codes' => ['30000'],
                'translations' => [
                    'lt' => ['name' => 'Halmstadas', 'description' => 'Hallando sostinė'],
                    'en' => ['name' => 'Halmstad', 'description' => 'Capital of Halland'],
                ],
            ],
            // Värmland
            [
                'name' => 'Karlstad',
                'code' => 'SE-S-KAR',
                'region_id' => $varmlandRegion?->id,
                'latitude' => 59.3793,
                'longitude' => 13.5036,
                'population' => 95000,
                'postal_codes' => ['65000'],
                'translations' => [
                    'lt' => ['name' => 'Karlstadas', 'description' => 'Vermlando sostinė'],
                    'en' => ['name' => 'Karlstad', 'description' => 'Capital of Värmland'],
                ],
            ],
            // Örebro
            [
                'name' => 'Örebro',
                'code' => 'SE-T-ORE',
                'region_id' => $orebroRegion?->id,
                'latitude' => 59.2741,
                'longitude' => 15.2066,
                'population' => 156000,
                'postal_codes' => ['70000'],
                'translations' => [
                    'lt' => ['name' => 'Orebro', 'description' => 'Orebro sostinė'],
                    'en' => ['name' => 'Örebro', 'description' => 'Capital of Örebro'],
                ],
            ],
            // Västmanland
            [
                'name' => 'Västerås',
                'code' => 'SE-U-VAS',
                'region_id' => $vastmanlandRegion?->id,
                'latitude' => 59.6162,
                'longitude' => 16.5528,
                'population' => 150000,
                'postal_codes' => ['72000'],
                'translations' => [
                    'lt' => ['name' => 'Vesterosas', 'description' => 'Vestmanlando sostinė'],
                    'en' => ['name' => 'Västerås', 'description' => 'Capital of Västmanland'],
                ],
            ],
            // Dalarna
            [
                'name' => 'Falun',
                'code' => 'SE-W-FAL',
                'region_id' => $dalarnaRegion?->id,
                'latitude' => 60.6036,
                'longitude' => 15.626,
                'population' => 58000,
                'postal_codes' => ['79000'],
                'translations' => [
                    'lt' => ['name' => 'Falunas', 'description' => 'Dalarnos sostinė'],
                    'en' => ['name' => 'Falun', 'description' => 'Capital of Dalarna'],
                ],
            ],
            // Gävleborg
            [
                'name' => 'Gävle',
                'code' => 'SE-X-GAV',
                'region_id' => $gavleborgRegion?->id,
                'latitude' => 60.6745,
                'longitude' => 17.1417,
                'population' => 100000,
                'postal_codes' => ['80000'],
                'translations' => [
                    'lt' => ['name' => 'Gevlė', 'description' => 'Gevleborgo sostinė'],
                    'en' => ['name' => 'Gävle', 'description' => 'Capital of Gävleborg'],
                ],
            ],
            // Västernorrland
            [
                'name' => 'Sundsvall',
                'code' => 'SE-Y-SUN',
                'region_id' => $vasternorrlandRegion?->id,
                'latitude' => 62.3908,
                'longitude' => 17.3069,
                'population' => 100000,
                'postal_codes' => ['85000'],
                'translations' => [
                    'lt' => ['name' => 'Sundsvallas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Sundsvall', 'description' => 'Industrial city'],
                ],
            ],
            // Jämtland
            [
                'name' => 'Östersund',
                'code' => 'SE-Z-OST',
                'region_id' => $jamtlandRegion?->id,
                'latitude' => 63.1792,
                'longitude' => 14.6356,
                'population' => 65000,
                'postal_codes' => ['83000'],
                'translations' => [
                    'lt' => ['name' => 'Osterstundas', 'description' => 'Jemtlando sostinė'],
                    'en' => ['name' => 'Östersund', 'description' => 'Capital of Jämtland'],
                ],
            ],
            // Västerbotten
            [
                'name' => 'Umeå',
                'code' => 'SE-AC-UME',
                'region_id' => $vasterbottenRegion?->id,
                'latitude' => 63.8258,
                'longitude' => 20.263,
                'population' => 130000,
                'postal_codes' => ['90000'],
                'translations' => [
                    'lt' => ['name' => 'Umeo', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Umeå', 'description' => 'University city'],
                ],
            ],
            // Norrbotten
            [
                'name' => 'Luleå',
                'code' => 'SE-BD-LUL',
                'region_id' => $norrbottenRegion?->id,
                'latitude' => 65.5842,
                'longitude' => 22.1547,
                'population' => 78000,
                'postal_codes' => ['95000'],
                'translations' => [
                    'lt' => ['name' => 'Luleo', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Luleå', 'description' => 'Industrial city'],
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
                    'country_id' => $sweden->id,
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
