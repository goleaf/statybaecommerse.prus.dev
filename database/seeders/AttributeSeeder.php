<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Translations\AttributeTranslation;
use Illuminate\Database\Seeder;

final class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = collect([
            [
                'slug'         => 'color',
                'type'         => 'select',
                'sort_order'   => 1,
                'translations' => [
                    'lt' => 'Spalva',
                    'en' => 'Color',
                    'lv' => 'Krāsa',
                ],
            ],
            [
                'slug'         => 'size',
                'type'         => 'select',
                'sort_order'   => 2,
                'translations' => [
                    'lt' => 'Dydis',
                    'en' => 'Size',
                    'lv' => 'Izmērs',
                ],
            ],
            [
                'slug'         => 'weight',
                'type'         => 'number',
                'sort_order'   => 3,
                'translations' => [
                    'lt' => 'Svoris',
                    'en' => 'Weight',
                    'lv' => 'Svars',
                ],
            ],
            [
                'slug'         => 'material',
                'type'         => 'text',
                'sort_order'   => 4,
                'translations' => [
                    'lt' => 'Medžiaga',
                    'en' => 'Material',
                    'lv' => 'Materiāls',
                ],
            ],
            [
                'slug'         => 'length',
                'type'         => 'number',
                'sort_order'   => 5,
                'translations' => [
                    'lt' => 'Ilgis',
                    'en' => 'Length',
                    'lv' => 'Garums',
                ],
            ],
            [
                'slug'         => 'width',
                'type'         => 'number',
                'sort_order'   => 6,
                'translations' => [
                    'lt' => 'Plotis',
                    'en' => 'Width',
                    'lv' => 'Platums',
                ],
            ],
            [
                'slug'         => 'height',
                'type'         => 'number',
                'sort_order'   => 7,
                'translations' => [
                    'lt' => 'Aukštis',
                    'en' => 'Height',
                    'lv' => 'Augstums',
                ],
            ],
            [
                'slug'         => 'color-palette',
                'type'         => 'multiselect',
                'sort_order'   => 8,
                'translations' => [
                    'lt' => 'Spalvų paletė',
                    'en' => 'Color Palette',
                    'lv' => 'Krāsu palete',
                ],
            ],
            [
                'slug'         => 'voltage',
                'type'         => 'number',
                'sort_order'   => 9,
                'translations' => [
                    'lt' => 'Įtampa',
                    'en' => 'Voltage',
                    'lv' => 'Spriegums',
                ],
            ],
            [
                'slug'         => 'battery-capacity',
                'type'         => 'text',
                'sort_order'   => 10,
                'translations' => [
                    'lt' => 'Akumuliatoriaus talpa',
                    'en' => 'Battery Capacity',
                    'lv' => 'Akumulatora ietilpība',
                ],
            ],
            [
                'slug'         => 'power-source',
                'type'         => 'select',
                'sort_order'   => 11,
                'translations' => [
                    'lt' => 'Maitinimo šaltinis',
                    'en' => 'Power Source',
                    'lv' => 'Barošanas avots',
                ],
            ],
            [
                'slug'         => 'tool-count',
                'type'         => 'number',
                'sort_order'   => 12,
                'translations' => [
                    'lt' => 'Įrankių kiekis rinkinyje',
                    'en' => 'Tool Count',
                    'lv' => 'Instrumentu skaits komplektā',
                ],
            ],
            [
                'slug'         => 'lumens',
                'type'         => 'number',
                'sort_order'   => 13,
                'translations' => [
                    'lt' => 'Šviesos srautas (lm)',
                    'en' => 'Luminous Flux (lm)',
                    'lv' => 'Gaismas plūsma (lm)',
                ],
            ],
            [
                'slug'         => 'ip-rating',
                'type'         => 'text',
                'sort_order'   => 14,
                'translations' => [
                    'lt' => 'IP klasė',
                    'en' => 'IP Rating',
                    'lv' => 'IP aizsardzības klase',
                ],
            ],
            [
                'slug'         => 'range',
                'type'         => 'text',
                'sort_order'   => 15,
                'translations' => [
                    'lt' => 'Darbo nuotolis',
                    'en' => 'Operating Range',
                    'lv' => 'Darbības attālums',
                ],
            ],
            [
                'slug'         => 'safety-rating',
                'type'         => 'text',
                'sort_order'   => 16,
                'translations' => [
                    'lt' => 'Saugos klasė',
                    'en' => 'Safety Rating',
                    'lv' => 'Drošības klase',
                ],
            ],
        ]);

        $attributes->each(function (array $definition): void {
            $attribute = Attribute::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name'          => $definition['translations']['en'],
                    'type'          => $definition['type'],
                    'is_required'   => false,
                    'is_filterable' => true,
                    'is_searchable' => true,
                    'is_enabled'    => true,
                    'sort_order'    => $definition['sort_order'],
                ],
            );

            foreach ($definition['translations'] as $locale => $name) {
                AttributeTranslation::query()->updateOrCreate(
                    [
                        'attribute_id' => $attribute->getKey(),
                        'locale'       => $locale,
                    ],
                    ['name' => $name],
                );
            }
        });
    }
}
