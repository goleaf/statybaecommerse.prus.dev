<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            [
                'name' => ['lt' => 'Spalva', 'en' => 'Color'],
                'slug' => 'color',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_searchable' => true,
                'is_enabled' => true,
                'sort_order' => 1,
            ],
            [
                'name' => ['lt' => 'Dydis', 'en' => 'Size'],
                'slug' => 'size',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_searchable' => true,
                'is_enabled' => true,
                'sort_order' => 2,
            ],
            [
                'name' => ['lt' => 'Svoris', 'en' => 'Weight'],
                'slug' => 'weight',
                'type' => 'text',
                'is_required' => false,
                'is_filterable' => false,
                'is_searchable' => false,
                'is_enabled' => true,
                'sort_order' => 3,
            ],
            [
                'name' => ['lt' => 'Medžiaga', 'en' => 'Material'],
                'slug' => 'material',
                'type' => 'text',
                'is_required' => false,
                'is_filterable' => true,
                'is_searchable' => true,
                'is_enabled' => true,
                'sort_order' => 4,
            ],
            [
                'name' => ['lt' => 'Ilgis', 'en' => 'Length'],
                'slug' => 'length',
                'type' => 'number',
                'is_required' => false,
                'is_filterable' => true,
                'is_searchable' => false,
                'is_enabled' => true,
                'sort_order' => 5,
            ],
            [
                'name' => ['lt' => 'Plotis', 'en' => 'Width'],
                'slug' => 'width',
                'type' => 'number',
                'is_required' => false,
                'is_filterable' => true,
                'is_searchable' => false,
                'is_enabled' => true,
                'sort_order' => 6,
            ],
            [
                'name' => ['lt' => 'Aukštis', 'en' => 'Height'],
                'slug' => 'height',
                'type' => 'number',
                'is_required' => false,
                'is_filterable' => true,
                'is_searchable' => false,
                'is_enabled' => true,
                'sort_order' => 7,
            ],
            [
                'name' => ['lt' => 'Spalvų paletė', 'en' => 'Color Palette'],
                'slug' => 'color-palette',
                'type' => 'multiselect',
                'is_required' => false,
                'is_filterable' => true,
                'is_searchable' => true,
                'is_enabled' => true,
                'sort_order' => 8,
            ],
        ];

        $locales = $this->supportedLocales();
        $now = now();

        foreach ($attributes as $data) {
            $baseName = $data['name']['lt'] ?? (is_array($data['name']) ? reset($data['name']) : (string) $data['name']);

            // Upsert base attribute row (string name)
            DB::table('attributes')->upsert([
                [
                    'slug' => $data['slug'],
                    'name' => $baseName,
                    'type' => $data['type'],
                    'is_required' => (bool) $data['is_required'],
                    'is_filterable' => (bool) $data['is_filterable'],
                    'is_searchable' => (bool) $data['is_searchable'],
                    'is_enabled' => (bool) $data['is_enabled'],
                    'sort_order' => (int) $data['sort_order'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            ], ['slug'], ['name', 'type', 'is_required', 'is_filterable', 'is_searchable', 'is_enabled', 'sort_order', 'updated_at']);

            $attrId = (int) DB::table('attributes')->where('slug', $data['slug'])->value('id');
            if (! $attrId) {
                continue;
            }

            // Translations per locale
            $trRows = [];
            foreach ($locales as $loc) {
                $trRows[] = [
                    'attribute_id' => $attrId,
                    'locale' => $loc,
                    'name' => $data['name'][$loc] ?? ($data['name']['lt'] ?? $baseName),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('attribute_translations')->upsert($trRows, ['attribute_id', 'locale'], ['name', 'updated_at']);
        }

        $this->command?->info('AttributeSeeder: seeded attributes with translations (locales: '.implode(',', $locales).').');
    }

    private function supportedLocales(): array
    {
        return collect(explode(',', (string) config('app.supported_locales', 'lt')))
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()->values()->all();
    }
}
