<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Translations\LocationTranslation;
use Illuminate\Database\Seeder;

final class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locales = $this->supportedLocales();

        $locations = [
            [
                'code' => 'WH-001',
                'address_line_1' => 'Sandėlio g. 1',
                'city' => 'Vilnius',
                'postal_code' => '01100',
                'country_code' => 'LT',
                'phone' => '+37060000001',
                'email' => 'warehouse@shop.lt',
                'is_enabled' => true,
                'is_default' => true,
                'type' => 'warehouse',
                'translations' => [
                    'lt' => [
                        'name' => 'Pagrindinis sandėlis',
                        'slug' => 'pagrindinis-sandelys',
                        'description' => 'Pagrindinė prekių saugojimo vieta',
                    ],
                    'en' => [
                        'name' => 'Main Warehouse',
                        'slug' => 'main-warehouse',
                        'description' => 'Primary storage location',
                    ],
                ],
            ],
            [
                'code' => 'WH-002',
                'address_line_1' => 'Pramonės g. 10',
                'city' => 'Kaunas',
                'postal_code' => '44100',
                'country_code' => 'LT',
                'phone' => '+37060000002',
                'email' => 'backup@shop.lt',
                'is_enabled' => true,
                'is_default' => false,
                'type' => 'warehouse',
                'translations' => [
                    'lt' => [
                        'name' => 'Atsarginis sandėlis',
                        'slug' => 'atsarginis-sandelys',
                        'description' => 'Papildoma atsargų vieta',
                    ],
                    'en' => [
                        'name' => 'Backup Warehouse',
                        'slug' => 'backup-warehouse',
                        'description' => 'Secondary stock location',
                    ],
                ],
            ],
        ];

        foreach ($locations as $data) {
            $translations = $data['translations'] ?? [];
            unset($data['translations']);

            $location = Location::updateOrCreate(['code' => $data['code']], $data);

            // Create translations for each locale
            foreach ($locales as $locale) {
                $translationData = $translations[$locale] ?? [];
                $slug = $translationData['slug'] ?? $location->code.'-'.$locale;
                LocationTranslation::updateOrCreate([
                    'location_id' => $location->id,
                    'locale' => $locale,
                ], [
                    'name' => $translationData['name'] ?? 'Location',
                    'slug' => $slug,
                    'description' => $translationData['description'] ?? '',
                ]);
            }
        }

        $this->command?->info('LocationSeeder: seeded locations with translations (locales: '.implode(',', $locales).').');
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
