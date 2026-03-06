<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Support\Str;

final class WarehouseSeeder extends BaseSeeder
{
    public function run(): void
    {
        $definitions = [
            [
                'code'           => 'WH-001',
                'address_line_1' => 'Sandelio g. 1',
                'city'           => 'Vilnius',
                'postal_code'    => '01100',
                'country_code'   => 'LT',
                'phone'          => '+37060000001',
                'email'          => 'info@egisstatyba.lt',
                'is_default'     => true,
                'name'           => ['lt' => 'Pagrindinis sandelis', 'en' => 'Main Warehouse'],
                'description'    => ['lt' => 'Pagrindine prekiu saugojimo vieta.', 'en' => 'Primary storage location.'],
            ],
            [
                'code'           => 'WH-002',
                'address_line_1' => 'Pramones g. 10',
                'city'           => 'Kaunas',
                'postal_code'    => '44100',
                'country_code'   => 'LT',
                'phone'          => '+37060000002',
                'email'          => 'info@egisstatyba.lt',
                'is_default'     => false,
                'name'           => ['lt' => 'Atsarginis sandelis', 'en' => 'Backup Warehouse'],
                'description'    => ['lt' => 'Papildoma atsargu vieta.', 'en' => 'Secondary stock location.'],
            ],
        ];

        foreach ($definitions as $definition) {
            $defaultName = (string) ($definition['name']['en'] ?? $definition['code']);
            $defaultDescription = (string) ($definition['description']['en'] ?? '');
            $defaultSlug = Str::slug($defaultName);

            $warehouse = Location::query()->firstOrNew(['code' => $definition['code']]);
            $warehouse->fill([
                'name'           => $defaultName,
                'slug'           => $defaultSlug,
                'description'    => $defaultDescription,
                'address_line_1' => $definition['address_line_1'],
                'city'           => $definition['city'],
                'postal_code'    => $definition['postal_code'],
                'country_code'   => $definition['country_code'],
                'phone'          => $definition['phone'],
                'email'          => $definition['email'],
                'is_enabled'     => true,
                'is_default'     => (bool) $definition['is_default'],
                'type'           => 'warehouse',
            ]);
            $warehouse->save();

            foreach ($definition['name'] as $locale => $name) {
                $warehouse->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'name'        => $name,
                        'slug'        => Str::slug($name),
                        'description' => (string) ($definition['description'][$locale] ?? $defaultDescription),
                    ]
                );
            }
        }

        $this->command?->info('WarehouseSeeder: ensured warehouse locations.');
    }
}
