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
                'slug' => 'color',
                'type' => 'select',
                'sort_order' => 1,
                'translations' => [
                    'lt' => 'Spalva',
                    'en' => 'Color',
                ],
            ],
            [
                'slug' => 'size',
                'type' => 'select',
                'sort_order' => 2,
                'translations' => [
                    'lt' => 'Dydis',
                    'en' => 'Size',
                ],
            ],
            [
                'slug' => 'weight',
                'type' => 'text',
                'sort_order' => 3,
                'translations' => [
                    'lt' => 'Svoris',
                    'en' => 'Weight',
                ],
            ],
            [
                'slug' => 'material',
                'type' => 'text',
                'sort_order' => 4,
                'translations' => [
                    'lt' => 'Medžiaga',
                    'en' => 'Material',
                ],
            ],
            [
                'slug' => 'length',
                'type' => 'number',
                'sort_order' => 5,
                'translations' => [
                    'lt' => 'Ilgis',
                    'en' => 'Length',
                ],
            ],
            [
                'slug' => 'width',
                'type' => 'number',
                'sort_order' => 6,
                'translations' => [
                    'lt' => 'Plotis',
                    'en' => 'Width',
                ],
            ],
            [
                'slug' => 'height',
                'type' => 'number',
                'sort_order' => 7,
                'translations' => [
                    'lt' => 'Aukštis',
                    'en' => 'Height',
                ],
            ],
            [
                'slug' => 'color-palette',
                'type' => 'multiselect',
                'sort_order' => 8,
                'translations' => [
                    'lt' => 'Spalvų paletė',
                    'en' => 'Color Palette',
                ],
            ],
        ]);

        $attributes->each(function (array $definition): void {
            $existing = Attribute::query()->firstWhere('slug', $definition['slug']);

            $attribute = $existing
                ? tap($existing)->update([
                    'name' => $definition['translations']['lt'],
                    'type' => $definition['type'],
                    'is_required' => false,
                    'is_filterable' => true,
                    'is_searchable' => true,
                    'is_enabled' => true,
                    'sort_order' => $definition['sort_order'],
                ])
                : Attribute::factory()->create([
                    'slug' => $definition['slug'],
                    'name' => $definition['translations']['lt'],
                    'type' => $definition['type'],
                    'is_required' => false,
                    'is_filterable' => true,
                    'is_searchable' => true,
                    'is_enabled' => true,
                    'sort_order' => $definition['sort_order'],
                ]);

            foreach ($definition['translations'] as $locale => $name) {
                $translation = AttributeTranslation::query()->firstOrNew([
                    'attribute_id' => $attribute->getKey(),
                    'locale' => $locale,
                ]);

                if ($translation->exists) {
                    $translation->update(['name' => $name]);
                } else {
                    AttributeTranslation::factory()->create([
                        'attribute_id' => $attribute->getKey(),
                        'locale' => $locale,
                        'name' => $name,
                    ]);
                }
            }
        });
    }
}
