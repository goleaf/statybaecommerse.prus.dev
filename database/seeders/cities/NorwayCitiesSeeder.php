<?php declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\Translations\CityTranslation;
use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class NorwayCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $norway = Country::where('cca2', 'NO')->first();
        $euZone = Zone::where('code', 'EU')->first();

        // Get regions
        $osloRegion = Region::where('code', 'NO-03')->first();
        $rogalandRegion = Region::where('code', 'NO-11')->first();
        $hordalandRegion = Region::where('code', 'NO-12')->first();
        $sorTrondelagRegion = Region::where('code', 'NO-16')->first();
        $nordTrondelagRegion = Region::where('code', 'NO-17')->first();
        $tromsRegion = Region::where('code', 'NO-19')->first();
        $finnmarkRegion = Region::where('code', 'NO-20')->first();
        $akershusRegion = Region::where('code', 'NO-02')->first();
        $ostfoldRegion = Region::where('code', 'NO-01')->first();
        $vestfoldRegion = Region::where('code', 'NO-07')->first();
        $telemarkRegion = Region::where('code', 'NO-08')->first();
        $austAgderRegion = Region::where('code', 'NO-09')->first();
        $vestAgderRegion = Region::where('code', 'NO-10')->first();
        $opplandRegion = Region::where('code', 'NO-05')->first();
        $hedmarkRegion = Region::where('code', 'NO-04')->first();
        $buskerudRegion = Region::where('code', 'NO-06')->first();
        $vestfoldRegion2 = Region::where('code', 'NO-07')->first();
        $sognOgFjordaneRegion = Region::where('code', 'NO-14')->first();
        $moreOgRomsdalRegion = Region::where('code', 'NO-15')->first();
        $nordlandRegion = Region::where('code', 'NO-18')->first();

        $cities = [
            // Oslo
            [
                'name' => 'Oslo',
                'code' => 'NO-03-OSL',
                'is_capital' => true,
                'is_default' => true,
                'region_id' => $osloRegion?->id,
                'latitude' => 59.9139,
                'longitude' => 10.7522,
                'population' => 697010,
                'postal_codes' => ['0001', '0002', '0003'],
                'translations' => [
                    'lt' => ['name' => 'Oslas', 'description' => 'Norvegijos sostinė'],
                    'en' => ['name' => 'Oslo', 'description' => 'Capital of Norway'],
                ],
            ],

            // Akershus
            [
                'name' => 'Bærum',
                'code' => 'NO-02-BAE',
                'region_id' => $akershusRegion?->id,
                'latitude' => 59.9333,
                'longitude' => 10.5167,
                'population' => 127000,
                'postal_codes' => ['1300'],
                'translations' => [
                    'lt' => ['name' => 'Berumas', 'description' => 'Akershuso miestas'],
                    'en' => ['name' => 'Bærum', 'description' => 'Akershus city'],
                ],
            ],
            [
                'name' => 'Lillestrøm',
                'code' => 'NO-02-LIL',
                'region_id' => $akershusRegion?->id,
                'latitude' => 59.9556,
                'longitude' => 11.0472,
                'population' => 87000,
                'postal_codes' => ['2000'],
                'translations' => [
                    'lt' => ['name' => 'Lilestromas', 'description' => 'Akershuso sostinė'],
                    'en' => ['name' => 'Lillestrøm', 'description' => 'Capital of Akershus'],
                ],
            ],

            // Østfold
            [
                'name' => 'Fredrikstad',
                'code' => 'NO-01-FRE',
                'region_id' => $ostfoldRegion?->id,
                'latitude' => 59.2167,
                'longitude' => 10.9500,
                'population' => 83000,
                'postal_codes' => ['1600'],
                'translations' => [
                    'lt' => ['name' => 'Fredrikstadas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Fredrikstad', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Sarpsborg',
                'code' => 'NO-01-SAR',
                'region_id' => $ostfoldRegion?->id,
                'latitude' => 59.2833,
                'longitude' => 11.1167,
                'population' => 57000,
                'postal_codes' => ['1700'],
                'translations' => [
                    'lt' => ['name' => 'Sarpsborgas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Sarpsborg', 'description' => 'Industrial city'],
                ],
            ],

            // Vestfold
            [
                'name' => 'Tønsberg',
                'code' => 'NO-07-TON',
                'region_id' => $vestfoldRegion?->id,
                'latitude' => 59.2667,
                'longitude' => 10.4167,
                'population' => 57000,
                'postal_codes' => ['3100'],
                'translations' => [
                    'lt' => ['name' => 'Tonsbergas', 'description' => 'Vestfoldo sostinė'],
                    'en' => ['name' => 'Tønsberg', 'description' => 'Capital of Vestfold'],
                ],
            ],
            [
                'name' => 'Sandefjord',
                'code' => 'NO-07-SAN',
                'region_id' => $vestfoldRegion?->id,
                'latitude' => 59.1333,
                'longitude' => 10.2167,
                'population' => 65000,
                'postal_codes' => ['3200'],
                'translations' => [
                    'lt' => ['name' => 'Sandefjordas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Sandefjord', 'description' => 'Port city'],
                ],
            ],

            // Telemark
            [
                'name' => 'Skien',
                'code' => 'NO-08-SKI',
                'region_id' => $telemarkRegion?->id,
                'latitude' => 59.2000,
                'longitude' => 9.6000,
                'population' => 55000,
                'postal_codes' => ['3700'],
                'translations' => [
                    'lt' => ['name' => 'Skienas', 'description' => 'Telemarko sostinė'],
                    'en' => ['name' => 'Skien', 'description' => 'Capital of Telemark'],
                ],
            ],
            [
                'name' => 'Porsgrunn',
                'code' => 'NO-08-POR',
                'region_id' => $telemarkRegion?->id,
                'latitude' => 59.1333,
                'longitude' => 9.6500,
                'population' => 36000,
                'postal_codes' => ['3900'],
                'translations' => [
                    'lt' => ['name' => 'Porsgrunas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Porsgrunn', 'description' => 'Industrial city'],
                ],
            ],

            // Aust-Agder
            [
                'name' => 'Arendal',
                'code' => 'NO-09-ARE',
                'region_id' => $austAgderRegion?->id,
                'latitude' => 58.4667,
                'longitude' => 8.7667,
                'population' => 45000,
                'postal_codes' => ['4800'],
                'translations' => [
                    'lt' => ['name' => 'Arendalas', 'description' => 'Aust-Agderio sostinė'],
                    'en' => ['name' => 'Arendal', 'description' => 'Capital of Aust-Agder'],
                ],
            ],

            // Vest-Agder
            [
                'name' => 'Kristiansand',
                'code' => 'NO-10-KRI',
                'region_id' => $vestAgderRegion?->id,
                'latitude' => 58.1467,
                'longitude' => 7.9956,
                'population' => 112000,
                'postal_codes' => ['4600'],
                'translations' => [
                    'lt' => ['name' => 'Kristiansandas', 'description' => 'Vest-Agderio sostinė'],
                    'en' => ['name' => 'Kristiansand', 'description' => 'Capital of Vest-Agder'],
                ],
            ],

            // Rogaland
            [
                'name' => 'Stavanger',
                'code' => 'NO-11-STA',
                'region_id' => $rogalandRegion?->id,
                'latitude' => 58.9700,
                'longitude' => 5.7331,
                'population' => 144699,
                'postal_codes' => ['4000'],
                'translations' => [
                    'lt' => ['name' => 'Stavangeras', 'description' => 'Rogalando sostinė'],
                    'en' => ['name' => 'Stavanger', 'description' => 'Capital of Rogaland'],
                ],
            ],
            [
                'name' => 'Sandnes',
                'code' => 'NO-11-SAN',
                'region_id' => $rogalandRegion?->id,
                'latitude' => 58.8500,
                'longitude' => 5.7333,
                'population' => 80000,
                'postal_codes' => ['4300'],
                'translations' => [
                    'lt' => ['name' => 'Sandnesas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Sandnes', 'description' => 'Industrial city'],
                ],
            ],

            // Hordaland
            [
                'name' => 'Bergen',
                'code' => 'NO-12-BER',
                'region_id' => $hordalandRegion?->id,
                'latitude' => 60.3913,
                'longitude' => 5.3221,
                'population' => 286930,
                'postal_codes' => ['5000'],
                'translations' => [
                    'lt' => ['name' => 'Bergenas', 'description' => 'Hordalando sostinė'],
                    'en' => ['name' => 'Bergen', 'description' => 'Capital of Hordaland'],
                ],
            ],

            // Sogn og Fjordane
            [
                'name' => 'Førde',
                'code' => 'NO-14-FOR',
                'region_id' => $sognOgFjordaneRegion?->id,
                'latitude' => 61.4500,
                'longitude' => 5.8500,
                'population' => 13000,
                'postal_codes' => ['6800'],
                'translations' => [
                    'lt' => ['name' => 'Forde', 'description' => 'Sogn og Fjordane sostinė'],
                    'en' => ['name' => 'Førde', 'description' => 'Capital of Sogn og Fjordane'],
                ],
            ],

            // Møre og Romsdal
            [
                'name' => 'Ålesund',
                'code' => 'NO-15-ALE',
                'region_id' => $moreOgRomsdalRegion?->id,
                'latitude' => 62.4722,
                'longitude' => 6.1549,
                'population' => 67000,
                'postal_codes' => ['6000'],
                'translations' => [
                    'lt' => ['name' => 'Olesundas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Ålesund', 'description' => 'Port city'],
                ],
            ],
            [
                'name' => 'Molde',
                'code' => 'NO-15-MOL',
                'region_id' => $moreOgRomsdalRegion?->id,
                'latitude' => 62.7333,
                'longitude' => 7.1833,
                'population' => 26000,
                'postal_codes' => ['6400'],
                'translations' => [
                    'lt' => ['name' => 'Moldė', 'description' => 'Møre og Romsdal sostinė'],
                    'en' => ['name' => 'Molde', 'description' => 'Capital of Møre og Romsdal'],
                ],
            ],

            // Sør-Trøndelag
            [
                'name' => 'Trondheim',
                'code' => 'NO-16-TRO',
                'region_id' => $sorTrondelagRegion?->id,
                'latitude' => 63.4305,
                'longitude' => 10.3951,
                'population' => 205332,
                'postal_codes' => ['7000'],
                'translations' => [
                    'lt' => ['name' => 'Trondheimas', 'description' => 'Sør-Trøndelag sostinė'],
                    'en' => ['name' => 'Trondheim', 'description' => 'Capital of Sør-Trøndelag'],
                ],
            ],

            // Nord-Trøndelag
            [
                'name' => 'Steinkjer',
                'code' => 'NO-17-STE',
                'region_id' => $nordTrondelagRegion?->id,
                'latitude' => 64.0167,
                'longitude' => 11.5000,
                'population' => 21000,
                'postal_codes' => ['7700'],
                'translations' => [
                    'lt' => ['name' => 'Steinkjeras', 'description' => 'Nord-Trøndelag sostinė'],
                    'en' => ['name' => 'Steinkjer', 'description' => 'Capital of Nord-Trøndelag'],
                ],
            ],

            // Nordland
            [
                'name' => 'Bodø',
                'code' => 'NO-18-BOD',
                'region_id' => $nordlandRegion?->id,
                'latitude' => 67.2833,
                'longitude' => 14.3833,
                'population' => 52000,
                'postal_codes' => ['8000'],
                'translations' => [
                    'lt' => ['name' => 'Bodė', 'description' => 'Nordlando sostinė'],
                    'en' => ['name' => 'Bodø', 'description' => 'Capital of Nordland'],
                ],
            ],
            [
                'name' => 'Narvik',
                'code' => 'NO-18-NAR',
                'region_id' => $nordlandRegion?->id,
                'latitude' => 68.4381,
                'longitude' => 17.4278,
                'population' => 21000,
                'postal_codes' => ['8500'],
                'translations' => [
                    'lt' => ['name' => 'Narvikas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Narvik', 'description' => 'Port city'],
                ],
            ],

            // Troms
            [
                'name' => 'Tromsø',
                'code' => 'NO-19-TRO',
                'region_id' => $tromsRegion?->id,
                'latitude' => 69.6492,
                'longitude' => 18.9553,
                'population' => 77000,
                'postal_codes' => ['9000'],
                'translations' => [
                    'lt' => ['name' => 'Tromsė', 'description' => 'Tromso sostinė'],
                    'en' => ['name' => 'Tromsø', 'description' => 'Capital of Troms'],
                ],
            ],

            // Finnmark
            [
                'name' => 'Alta',
                'code' => 'NO-20-ALT',
                'region_id' => $finnmarkRegion?->id,
                'latitude' => 69.9689,
                'longitude' => 23.2717,
                'population' => 21000,
                'postal_codes' => ['9500'],
                'translations' => [
                    'lt' => ['name' => 'Alta', 'description' => 'Finnmarko miestas'],
                    'en' => ['name' => 'Alta', 'description' => 'Finnmark city'],
                ],
            ],
            [
                'name' => 'Vadsø',
                'code' => 'NO-20-VAD',
                'region_id' => $finnmarkRegion?->id,
                'latitude' => 70.0736,
                'longitude' => 29.7494,
                'population' => 6000,
                'postal_codes' => ['9800'],
                'translations' => [
                    'lt' => ['name' => 'Vadsė', 'description' => 'Finnmarko sostinė'],
                    'en' => ['name' => 'Vadsø', 'description' => 'Capital of Finnmark'],
                ],
            ],

            // Oppland
            [
                'name' => 'Lillehammer',
                'code' => 'NO-05-LIL',
                'region_id' => $opplandRegion?->id,
                'latitude' => 61.1167,
                'longitude' => 10.4667,
                'population' => 28000,
                'postal_codes' => ['2600'],
                'translations' => [
                    'lt' => ['name' => 'Lilehameras', 'description' => 'Žiemos sporto miestas'],
                    'en' => ['name' => 'Lillehammer', 'description' => 'Winter sports city'],
                ],
            ],
            [
                'name' => 'Gjøvik',
                'code' => 'NO-05-GJO',
                'region_id' => $opplandRegion?->id,
                'latitude' => 60.8000,
                'longitude' => 10.7000,
                'population' => 30000,
                'postal_codes' => ['2800'],
                'translations' => [
                    'lt' => ['name' => 'Gjovikas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Gjøvik', 'description' => 'Industrial city'],
                ],
            ],

            // Hedmark
            [
                'name' => 'Hamar',
                'code' => 'NO-04-HAM',
                'region_id' => $hedmarkRegion?->id,
                'latitude' => 60.8000,
                'longitude' => 11.0667,
                'population' => 31000,
                'postal_codes' => ['2300'],
                'translations' => [
                    'lt' => ['name' => 'Hamaras', 'description' => 'Hedmarko sostinė'],
                    'en' => ['name' => 'Hamar', 'description' => 'Capital of Hedmark'],
                ],
            ],
            [
                'name' => 'Kongsvinger',
                'code' => 'NO-04-KON',
                'region_id' => $hedmarkRegion?->id,
                'latitude' => 60.1833,
                'longitude' => 12.0000,
                'population' => 18000,
                'postal_codes' => ['2200'],
                'translations' => [
                    'lt' => ['name' => 'Kongsvingeras', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Kongsvinger', 'description' => 'Historic city'],
                ],
            ],

            // Buskerud
            [
                'name' => 'Drammen',
                'code' => 'NO-06-DRA',
                'region_id' => $buskerudRegion?->id,
                'latitude' => 59.7439,
                'longitude' => 10.2044,
                'population' => 100000,
                'postal_codes' => ['3000'],
                'translations' => [
                    'lt' => ['name' => 'Dramenas', 'description' => 'Buskerudo sostinė'],
                    'en' => ['name' => 'Drammen', 'description' => 'Capital of Buskerud'],
                ],
            ],
            [
                'name' => 'Kongsberg',
                'code' => 'NO-06-KON',
                'region_id' => $buskerudRegion?->id,
                'latitude' => 59.6667,
                'longitude' => 9.6500,
                'population' => 28000,
                'postal_codes' => ['3600'],
                'translations' => [
                    'lt' => ['name' => 'Kongsbergas', 'description' => 'Kasybos miestas'],
                    'en' => ['name' => 'Kongsberg', 'description' => 'Mining city'],
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
                    'country_id' => $norway->id,
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
