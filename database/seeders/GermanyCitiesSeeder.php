<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\Translations\CityTranslation;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class GermanyCitiesSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create Germany and EU zone using factories
        $germany = Country::factory()->state(['cca2' => 'DE', 'name' => 'Germany'])->create();
        $euZone = Zone::factory()->state(['code' => 'EU', 'name' => 'European Union'])->create();

        // Create German regions using factories
        $regions = $this->createGermanRegions($germany, $euZone);

        // Create major German cities using factories
        $this->createGermanCities($germany, $euZone, $regions);
    }

    private function createGermanRegions(Country $germany, Zone $euZone): array
    {
        $regionData = [
            'DE-BE' => ['name' => 'Berlin', 'type' => 'state'],
            'DE-HH' => ['name' => 'Hamburg', 'type' => 'state'],
            'DE-BY' => ['name' => 'Bavaria', 'type' => 'state'],
            'DE-BW' => ['name' => 'Baden-Württemberg', 'type' => 'state'],
            'DE-NW' => ['name' => 'North Rhine-Westphalia', 'type' => 'state'],
            'DE-HE' => ['name' => 'Hesse', 'type' => 'state'],
            'DE-SN' => ['name' => 'Saxony', 'type' => 'state'],
            'DE-NI' => ['name' => 'Lower Saxony', 'type' => 'state'],
            'DE-BB' => ['name' => 'Brandenburg', 'type' => 'state'],
            'DE-ST' => ['name' => 'Saxony-Anhalt', 'type' => 'state'],
            'DE-TH' => ['name' => 'Thuringia', 'type' => 'state'],
            'DE-MV' => ['name' => 'Mecklenburg-Vorpommern', 'type' => 'state'],
            'DE-SH' => ['name' => 'Schleswig-Holstein', 'type' => 'state'],
            'DE-SL' => ['name' => 'Saarland', 'type' => 'state'],
            'DE-HB' => ['name' => 'Bremen', 'type' => 'state'],
            'DE-RP' => ['name' => 'Rhineland-Palatinate', 'type' => 'state'],
        ];

        $regions = [];
        foreach ($regionData as $code => $data) {
            $regions[$code] = Region::factory()
                ->for($germany, 'country')
                ->for($euZone, 'zone')
                ->state([
                    'code' => $code,
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'is_enabled' => true,
                ])
                ->create();
        }

        return $regions;
    }

    private function createGermanCities(Country $germany, Zone $euZone, array $regions): void
    {
        $cityData = [
            // Berlin
            [
                'name' => 'Berlin',
                'code' => 'DE-BE-BER',
                'region_code' => 'DE-BE',
                'is_capital' => true,
                'is_default' => true,
                'latitude' => 52.52,
                'longitude' => 13.405,
                'population' => 3669491,
                'postal_codes' => ['10115', '10117', '10119'],
                'translations' => [
                    'lt' => ['name' => 'Berlynas', 'description' => 'Vokietijos sostinė'],
                    'en' => ['name' => 'Berlin', 'description' => 'Capital of Germany'],
                ],
            ],
            // Hamburg
            [
                'name' => 'Hamburg',
                'code' => 'DE-HH-HAM',
                'region_code' => 'DE-HH',
                'latitude' => 53.5511,
                'longitude' => 9.9937,
                'population' => 1899160,
                'postal_codes' => ['20095', '20097', '20099'],
                'translations' => [
                    'lt' => ['name' => 'Hamburgas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Hamburg', 'description' => 'Port city'],
                ],
            ],
            // Major cities only - using factory pattern for scalability
            [
                'name' => 'Munich',
                'code' => 'DE-BY-MUN',
                'region_code' => 'DE-BY',
                'latitude' => 48.1351,
                'longitude' => 11.582,
                'population' => 1484226,
                'postal_codes' => ['80331', '80333', '80335'],
                'translations' => [
                    'lt' => ['name' => 'Miunchenas', 'description' => 'Bavarijos sostinė'],
                    'en' => ['name' => 'Munich', 'description' => 'Capital of Bavaria'],
                ],
            ],
            [
                'name' => 'Cologne',
                'code' => 'DE-NW-COL',
                'region_code' => 'DE-NW',
                'latitude' => 50.9375,
                'longitude' => 6.9603,
                'population' => 1085664,
                'postal_codes' => ['50667', '50668', '50670'],
                'translations' => [
                    'lt' => ['name' => 'Kelnas', 'description' => 'Katedros miestas'],
                    'en' => ['name' => 'Cologne', 'description' => 'Cathedral city'],
                ],
            ],
            [
                'name' => 'Frankfurt',
                'code' => 'DE-HE-FRA',
                'region_code' => 'DE-HE',
                'latitude' => 50.1109,
                'longitude' => 8.6821,
                'population' => 753056,
                'postal_codes' => ['60311', '60313', '60316'],
                'translations' => [
                    'lt' => ['name' => 'Frankfurtas', 'description' => 'Finansų centras'],
                    'en' => ['name' => 'Frankfurt', 'description' => 'Financial center'],
                ],
            ],
            [
                'name' => 'Stuttgart',
                'code' => 'DE-BW-STU',
                'region_code' => 'DE-BW',
                'latitude' => 48.7758,
                'longitude' => 9.1829,
                'population' => 634830,
                'postal_codes' => ['70173', '70174', '70176'],
                'translations' => [
                    'lt' => ['name' => 'Štutgartas', 'description' => 'Badeno-Viurtembergo sostinė'],
                    'en' => ['name' => 'Stuttgart', 'description' => 'Capital of Baden-Württemberg'],
                ],
            ],
            [
                'name' => 'Dresden',
                'code' => 'DE-SN-DRE',
                'region_code' => 'DE-SN',
                'latitude' => 51.0504,
                'longitude' => 13.7373,
                'population' => 556780,
                'postal_codes' => ['01067', '01069', '01097'],
                'translations' => [
                    'lt' => ['name' => 'Drezdenas', 'description' => 'Saksonijos sostinė'],
                    'en' => ['name' => 'Dresden', 'description' => 'Capital of Saxony'],
                ],
            ],
            [
                'name' => 'Hanover',
                'code' => 'DE-NI-HAN',
                'region_code' => 'DE-NI',
                'latitude' => 52.3759,
                'longitude' => 9.732,
                'population' => 535061,
                'postal_codes' => ['30159', '30161', '30163'],
                'translations' => [
                    'lt' => ['name' => 'Hanoveris', 'description' => 'Žemutinės Saksonijos sostinė'],
                    'en' => ['name' => 'Hanover', 'description' => 'Capital of Lower Saxony'],
                ],
            ],
        ];

        // Create cities using factories with relationships
        foreach ($cityData as $data) {
            $region = $regions[$data['region_code']] ?? null;

            $city = City::factory()
                ->for($germany, 'country')
                ->for($euZone, 'zone')
                ->when($region, fn ($factory) => $factory->for($region, 'region'))
                ->state([
                    'name' => $data['name'],
                    'code' => $data['code'],
                    'slug' => \Str::slug($data['name']),
                    'is_enabled' => true,
                    'is_default' => $data['is_default'] ?? false,
                    'is_capital' => $data['is_capital'] ?? false,
                    'level' => 1,
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'population' => $data['population'],
                    'postal_codes' => $data['postal_codes'],
                    'sort_order' => 0,
                ])
                ->has(
                    CityTranslation::factory()
                        ->count(count($data['translations']))
                        ->sequence(...collect($data['translations'])->map(fn ($translation, $locale) => [
                            'locale' => $locale,
                            'name' => $translation['name'],
                            'description' => $translation['description'],
                        ])->values()->toArray()),
                    'translations'
                )
                ->create();
        }
    }
}
