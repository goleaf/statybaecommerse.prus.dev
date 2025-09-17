<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Region;
use App\Models\Translations\RegionTranslation;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        // Get countries and zones
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

        $locales = $this->supportedLocales();

        $regions = [
            // Lithuania regions
            [
                'slug' => 'vilniaus-apskritis',
                'code' => 'LT-VL',
                'country_id' => $lithuania?->id,
                'zone_id' => $ltZone?->id,
                'level' => 1,
                'sort_order' => 1,
                'translations' => [
                    'lt' => [
                        'name' => 'Vilniaus apskritis',
                        'description' => 'Vilniaus apskritis su sostine Vilniumi',
                    ],
                    'en' => [
                        'name' => 'Vilnius County',
                        'description' => 'Vilnius County with capital Vilnius',
                    ],
                ],
            ],
            [
                'name' => ['lt' => 'Kauno apskritis', 'en' => 'Kaunas County'],
                'slug' => 'kauno-apskritis',
                'code' => 'LT-KA',
                'description' => ['lt' => 'Kauno apskritis su sostine Kaunu', 'en' => 'Kaunas County with capital Kaunas'],
                'country_id' => $lithuania?->id,
                'zone_id' => $ltZone?->id,
                'level' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => ['lt' => 'Klaipėdos apskritis', 'en' => 'Klaipėda County'],
                'slug' => 'klaipedos-apskritis',
                'code' => 'LT-KL',
                'description' => ['lt' => 'Klaipėdos apskritis su sostine Klaipėda', 'en' => 'Klaipėda County with capital Klaipėda'],
                'country_id' => $lithuania?->id,
                'zone_id' => $ltZone?->id,
                'level' => 1,
                'sort_order' => 3,
            ],
            [
                'name' => ['lt' => 'Šiaulių apskritis', 'en' => 'Šiauliai County'],
                'slug' => 'siauliu-apskritis',
                'code' => 'LT-SA',
                'description' => ['lt' => 'Šiaulių apskritis su sostine Šiauliais', 'en' => 'Šiauliai County with capital Šiauliai'],
                'country_id' => $lithuania?->id,
                'zone_id' => $ltZone?->id,
                'level' => 1,
                'sort_order' => 4,
            ],
            [
                'name' => ['lt' => 'Panevėžio apskritis', 'en' => 'Panevėžys County'],
                'slug' => 'panevezio-apskritis',
                'code' => 'LT-PN',
                'description' => ['lt' => 'Panevėžio apskritis su sostine Panevėžiu', 'en' => 'Panevėžys County with capital Panevėžys'],
                'country_id' => $lithuania?->id,
                'zone_id' => $ltZone?->id,
                'level' => 1,
                'sort_order' => 5,
            ],
            [
                'name' => ['lt' => 'Alytaus apskritis', 'en' => 'Alytus County'],
                'slug' => 'alytaus-apskritis',
                'code' => 'LT-AL',
                'description' => ['lt' => 'Alytaus apskritis su sostine Alytum', 'en' => 'Alytus County with capital Alytus'],
                'country_id' => $lithuania?->id,
                'zone_id' => $ltZone?->id,
                'level' => 1,
                'sort_order' => 6,
            ],
            [
                'name' => ['lt' => 'Marijampolės apskritis', 'en' => 'Marijampolė County'],
                'slug' => 'marijampoles-apskritis',
                'code' => 'LT-MR',
                'description' => ['lt' => 'Marijampolės apskritis su sostine Marijampole', 'en' => 'Marijampolė County with capital Marijampolė'],
                'country_id' => $lithuania?->id,
                'zone_id' => $ltZone?->id,
                'level' => 1,
                'sort_order' => 7,
            ],
            [
                'name' => ['lt' => 'Tauragės apskritis', 'en' => 'Tauragė County'],
                'slug' => 'taurages-apskritis',
                'code' => 'LT-TA',
                'description' => ['lt' => 'Tauragės apskritis su sostine Taurage', 'en' => 'Tauragė County with capital Tauragė'],
                'country_id' => $lithuania?->id,
                'zone_id' => $ltZone?->id,
                'level' => 1,
                'sort_order' => 8,
            ],
            [
                'name' => ['lt' => 'Telšių apskritis', 'en' => 'Telšiai County'],
                'slug' => 'telsiu-apskritis',
                'code' => 'LT-TE',
                'description' => ['lt' => 'Telšių apskritis su sostine Telšiais', 'en' => 'Telšiai County with capital Telšiai'],
                'country_id' => $lithuania?->id,
                'zone_id' => $ltZone?->id,
                'level' => 1,
                'sort_order' => 9,
            ],
            [
                'name' => ['lt' => 'Utenos apskritis', 'en' => 'Utena County'],
                'slug' => 'utenos-apskritis',
                'code' => 'LT-UT',
                'description' => ['lt' => 'Utenos apskritis su sostine Utena', 'en' => 'Utena County with capital Utena'],
                'country_id' => $lithuania?->id,
                'zone_id' => $ltZone?->id,
                'level' => 1,
                'sort_order' => 10,
            ],
            // Latvia regions
            [
                'name' => ['lt' => 'Rygos regionas', 'en' => 'Riga Region'],
                'slug' => 'rygos-regionas',
                'code' => 'LV-RI',
                'description' => ['lt' => 'Rygos regionas su sostine Ryga', 'en' => 'Riga Region with capital Riga'],
                'country_id' => $latvia?->id,
                'zone_id' => $euZone?->id,
                'level' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Kurzemės regionas', 'en' => 'Kurzeme Region'],
                'slug' => 'kurzemes-regionas',
                'code' => 'LV-KU',
                'description' => ['lt' => 'Kurzemės regionas', 'en' => 'Kurzeme Region'],
                'country_id' => $latvia?->id,
                'zone_id' => $euZone?->id,
                'level' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => ['lt' => 'Latgalijos regionas', 'en' => 'Latgale Region'],
                'slug' => 'latgalijos-regionas',
                'code' => 'LV-LA',
                'description' => ['lt' => 'Latgalijos regionas', 'en' => 'Latgale Region'],
                'country_id' => $latvia?->id,
                'zone_id' => $euZone?->id,
                'level' => 1,
                'sort_order' => 3,
            ],
            [
                'name' => ['lt' => 'Vidžemės regionas', 'en' => 'Vidzeme Region'],
                'slug' => 'vidzemes-regionas',
                'code' => 'LV-VI',
                'description' => ['lt' => 'Vidžemės regionas', 'en' => 'Vidzeme Region'],
                'country_id' => $latvia?->id,
                'zone_id' => $euZone?->id,
                'level' => 1,
                'sort_order' => 4,
            ],
            [
                'name' => ['lt' => 'Zemgalijos regionas', 'en' => 'Zemgale Region'],
                'slug' => 'zemgalijos-regionas',
                'code' => 'LV-ZE',
                'description' => ['lt' => 'Zemgalijos regionas', 'en' => 'Zemgale Region'],
                'country_id' => $latvia?->id,
                'zone_id' => $euZone?->id,
                'level' => 1,
                'sort_order' => 5,
            ],
            // Estonia regions
            [
                'name' => ['lt' => 'Harju regionas', 'en' => 'Harju County'],
                'slug' => 'harju-regionas',
                'code' => 'EE-37',
                'description' => ['lt' => 'Harju regionas su sostine Talinu', 'en' => 'Harju County with capital Tallinn'],
                'country_id' => $estonia?->id,
                'zone_id' => $euZone?->id,
                'level' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Tartu regionas', 'en' => 'Tartu County'],
                'slug' => 'tartu-regionas',
                'code' => 'EE-78',
                'description' => ['lt' => 'Tartu regionas su sostine Tartu', 'en' => 'Tartu County with capital Tartu'],
                'country_id' => $estonia?->id,
                'zone_id' => $euZone?->id,
                'level' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => ['lt' => 'Ida-Viru regionas', 'en' => 'Ida-Viru County'],
                'slug' => 'ida-viru-regionas',
                'code' => 'EE-44',
                'description' => ['lt' => 'Ida-Viru regionas', 'en' => 'Ida-Viru County'],
                'country_id' => $estonia?->id,
                'zone_id' => $euZone?->id,
                'level' => 1,
                'sort_order' => 3,
            ],
            // Poland regions
            [
                'name' => ['lt' => 'Mazovijos vaivadija', 'en' => 'Masovian Voivodeship'],
                'slug' => 'mazovijos-vaivadija',
                'code' => 'PL-MZ',
                'description' => ['lt' => 'Mazovijos vaivadija su sostine Varšuva', 'en' => 'Masovian Voivodeship with capital Warsaw'],
                'country_id' => $poland?->id,
                'zone_id' => $euZone?->id,
                'level' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Mažosios Lenkijos vaivadija', 'en' => 'Lesser Poland Voivodeship'],
                'slug' => 'mazosios-lenkijos-vaivadija',
                'code' => 'PL-MA',
                'description' => ['lt' => 'Mažosios Lenkijos vaivadija su sostine Krokuva', 'en' => 'Lesser Poland Voivodeship with capital Krakow'],
                'country_id' => $poland?->id,
                'zone_id' => $euZone?->id,
                'level' => 1,
                'sort_order' => 2,
            ],
            // Germany regions
            [
                'name' => ['lt' => 'Bavarijos žemė', 'en' => 'Bavaria'],
                'slug' => 'bavarijos-zeme',
                'code' => 'DE-BY',
                'description' => ['lt' => 'Bavarijos žemė su sostine Miunchenu', 'en' => 'Bavaria with capital Munich'],
                'country_id' => $germany?->id,
                'zone_id' => $euZone?->id,
                'level' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Šiaurės Reino-Vestfalijos žemė', 'en' => 'North Rhine-Westphalia'],
                'slug' => 'siaures-reino-vestfalijos-zeme',
                'code' => 'DE-NW',
                'description' => ['lt' => 'Šiaurės Reino-Vestfalijos žemė su sostine Diuseldorfu', 'en' => 'North Rhine-Westphalia with capital Düsseldorf'],
                'country_id' => $germany?->id,
                'zone_id' => $euZone?->id,
                'level' => 1,
                'sort_order' => 2,
            ],
            // France regions
            [
                'name' => ['lt' => 'Ile-de-France regionas', 'en' => 'Île-de-France'],
                'slug' => 'ile-de-france-regionas',
                'code' => 'FR-IDF',
                'description' => ['lt' => 'Ile-de-France regionas su sostine Paryžium', 'en' => 'Île-de-France with capital Paris'],
                'country_id' => $france?->id,
                'zone_id' => $euZone?->id,
                'level' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => "Provence-Alpes-Côte d'Azur regionas", 'en' => "Provence-Alpes-Côte d'Azur"],
                'slug' => 'provence-alpes-cote-dazur-regionas',
                'code' => 'FR-PAC',
                'description' => ['lt' => "Provence-Alpes-Côte d'Azur regionas su sostine Marseliu", 'en' => "Provence-Alpes-Côte d'Azur with capital Marseille"],
                'country_id' => $france?->id,
                'zone_id' => $euZone?->id,
                'level' => 1,
                'sort_order' => 2,
            ],
            // Spain regions
            [
                'name' => ['lt' => 'Madrido regionas', 'en' => 'Community of Madrid'],
                'slug' => 'madrido-regionas',
                'code' => 'ES-MD',
                'description' => ['lt' => 'Madrido regionas su sostine Madridu', 'en' => 'Community of Madrid with capital Madrid'],
                'country_id' => $spain?->id,
                'zone_id' => $euZone?->id,
                'level' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Katalonijos regionas', 'en' => 'Catalonia'],
                'slug' => 'katalonijos-regionas',
                'code' => 'ES-CT',
                'description' => ['lt' => 'Katalonijos regionas su sostine Barselona', 'en' => 'Catalonia with capital Barcelona'],
                'country_id' => $spain?->id,
                'zone_id' => $euZone?->id,
                'level' => 1,
                'sort_order' => 2,
            ],
            // Italy regions
            [
                'name' => ['lt' => 'Lombardijos regionas', 'en' => 'Lombardy'],
                'slug' => 'lombardijos-regionas',
                'code' => 'IT-LO',
                'description' => ['lt' => 'Lombardijos regionas su sostine Milanu', 'en' => 'Lombardy with capital Milan'],
                'country_id' => $italy?->id,
                'zone_id' => $euZone?->id,
                'level' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Lacijaus regionas', 'en' => 'Lazio'],
                'slug' => 'lacijaus-regionas',
                'code' => 'IT-LA',
                'description' => ['lt' => 'Lacijaus regionas su sostine Roma', 'en' => 'Lazio with capital Rome'],
                'country_id' => $italy?->id,
                'zone_id' => $euZone?->id,
                'level' => 1,
                'sort_order' => 2,
            ],
            // USA regions
            [
                'name' => ['lt' => 'Kalifornijos valstija', 'en' => 'California'],
                'slug' => 'kalifornijos-valstija',
                'code' => 'US-CA',
                'description' => ['lt' => 'Kalifornijos valstija su sostine Sakramentu', 'en' => 'California with capital Sacramento'],
                'country_id' => $usa?->id,
                'zone_id' => $naZone?->id,
                'level' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Niujorko valstija', 'en' => 'New York'],
                'slug' => 'niujorko-valstija',
                'code' => 'US-NY',
                'description' => ['lt' => 'Niujorko valstija su sostine Olbaniu', 'en' => 'New York with capital Albany'],
                'country_id' => $usa?->id,
                'zone_id' => $naZone?->id,
                'level' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => ['lt' => 'Teksaso valstija', 'en' => 'Texas'],
                'slug' => 'teksaso-valstija',
                'code' => 'US-TX',
                'description' => ['lt' => 'Teksaso valstija su sostine Ostinu', 'en' => 'Texas with capital Austin'],
                'country_id' => $usa?->id,
                'zone_id' => $naZone?->id,
                'level' => 1,
                'sort_order' => 3,
            ],
            // Canada regions
            [
                'name' => ['lt' => 'Ontario provincija', 'en' => 'Ontario'],
                'slug' => 'ontario-provincija',
                'code' => 'CA-ON',
                'description' => ['lt' => 'Ontario provincija su sostine Torontu', 'en' => 'Ontario with capital Toronto'],
                'country_id' => $canada?->id,
                'zone_id' => $naZone?->id,
                'level' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Kvebeko provincija', 'en' => 'Quebec'],
                'slug' => 'kvebeko-provincija',
                'code' => 'CA-QC',
                'description' => ['lt' => 'Kvebeko provincija su sostine Kvebeku', 'en' => 'Quebec with capital Quebec City'],
                'country_id' => $canada?->id,
                'zone_id' => $naZone?->id,
                'level' => 1,
                'sort_order' => 2,
            ],
            // UK regions
            [
                'name' => ['lt' => 'Anglija', 'en' => 'England'],
                'slug' => 'anglija',
                'code' => 'GB-ENG',
                'description' => ['lt' => 'Anglija su sostine Londonu', 'en' => 'England with capital London'],
                'country_id' => $uk?->id,
                'zone_id' => $ukZone?->id,
                'level' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Škotija', 'en' => 'Scotland'],
                'slug' => 'skotija',
                'code' => 'GB-SCT',
                'description' => ['lt' => 'Škotija su sostine Edinburgu', 'en' => 'Scotland with capital Edinburgh'],
                'country_id' => $uk?->id,
                'zone_id' => $ukZone?->id,
                'level' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => ['lt' => 'Velsas', 'en' => 'Wales'],
                'slug' => 'velsas',
                'code' => 'GB-WLS',
                'description' => ['lt' => 'Velsas su sostine Kardifu', 'en' => 'Wales with capital Cardiff'],
                'country_id' => $uk?->id,
                'zone_id' => $ukZone?->id,
                'level' => 1,
                'sort_order' => 3,
            ],
            [
                'name' => ['lt' => 'Šiaurės Airija', 'en' => 'Northern Ireland'],
                'slug' => 'siaures-airija',
                'code' => 'GB-NIR',
                'description' => ['lt' => 'Šiaurės Airija su sostine Belfastu', 'en' => 'Northern Ireland with capital Belfast'],
                'country_id' => $uk?->id,
                'zone_id' => $ukZone?->id,
                'level' => 1,
                'sort_order' => 4,
            ],
        ];

        foreach ($regions as $regionData) {
            // Handle both old format (name/description arrays) and new format (translations)
            $translations = [];
            if (isset($regionData['translations'])) {
                $translations = $regionData['translations'];
                unset($regionData['translations']);
                $defaultName = $translations['en']['name'] ?? $regionData['code'];
            } elseif (isset($regionData['name']) && is_array($regionData['name'])) {
                $translations = [
                    'lt' => [
                        'name' => $regionData['name']['lt'] ?? 'Region',
                        'description' => $regionData['description']['lt'] ?? '',
                    ],
                    'en' => [
                        'name' => $regionData['name']['en'] ?? 'Region',
                        'description' => $regionData['description']['en'] ?? '',
                    ],
                ];
                $defaultName = $regionData['name']['en'] ?? $regionData['code'];
                unset($regionData['name'], $regionData['description']);
            } else {
                $defaultName = $regionData['code'];
                $translations = [];
            }

            $region = Region::updateOrCreate(
                ['code' => $regionData['code']],
                array_merge($regionData, [
                    'name' => $defaultName,
                    'is_enabled' => true,
                    'is_default' => false,
                ])
            );

            // Create translations for each locale
            foreach ($locales as $locale) {
                $translationData = $translations[$locale] ?? [];
                RegionTranslation::updateOrCreate([
                    'region_id' => $region->id,
                    'locale' => $locale,
                ], [
                    'name' => $translationData['name'] ?? 'Region',
                    'description' => $translationData['description'] ?? '',
                ]);
            }

            $regionName = $translations['en']['name'] ?? $translations['lt']['name'] ?? 'Region';
            $this->command->info("Upserted region: {$regionData['code']} - {$regionName}");
        }

        $this->command->info('Region seeding completed successfully with translations (locales: '.implode(',', $locales).')!');
    }

    private function supportedLocales(): array
    {
        return collect(explode(',', (string) config('app.supported_locales', 'lt,en')))
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}
