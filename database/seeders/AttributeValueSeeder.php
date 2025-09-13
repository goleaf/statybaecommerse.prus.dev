<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class AttributeValueSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'color' => [
                ['slug' => 'black', 'lt' => 'Juoda', 'en' => 'Black'],
                ['slug' => 'white', 'lt' => 'Balta', 'en' => 'White'],
                ['slug' => 'red', 'lt' => 'Raudona', 'en' => 'Red'],
                ['slug' => 'blue', 'lt' => 'Mėlyna', 'en' => 'Blue'],
                ['slug' => 'green', 'lt' => 'Žalia', 'en' => 'Green'],
                ['slug' => 'yellow', 'lt' => 'Geltona', 'en' => 'Yellow'],
                ['slug' => 'orange', 'lt' => 'Oranžinė', 'en' => 'Orange'],
                ['slug' => 'purple', 'lt' => 'Violetinė', 'en' => 'Purple'],
            ],
            'size' => [
                ['slug' => 's', 'lt' => 'Mažas', 'en' => 'Small'],
                ['slug' => 'm', 'lt' => 'Vidutinis', 'en' => 'Medium'],
                ['slug' => 'l', 'lt' => 'Didelis', 'en' => 'Large'],
                ['slug' => 'xl', 'lt' => 'Ypač didelis', 'en' => 'Extra Large'],
                ['slug' => 'xxl', 'lt' => 'Dvigubai ypač didelis', 'en' => 'Double Extra Large'],
            ],
            'material' => [
                ['slug' => 'steel', 'lt' => 'Plienas', 'en' => 'Steel'],
                ['slug' => 'aluminium', 'lt' => 'Aliuminis', 'en' => 'Aluminium'],
                ['slug' => 'plastic', 'lt' => 'Plastikas', 'en' => 'Plastic'],
                ['slug' => 'wood', 'lt' => 'Medis', 'en' => 'Wood'],
                ['slug' => 'rubber', 'lt' => 'Guma', 'en' => 'Rubber'],
            ],
            'color-palette' => [
                ['slug' => 'basic', 'lt' => 'Bazinis', 'en' => 'Basic'],
                ['slug' => 'pastel', 'lt' => 'Pastelinis', 'en' => 'Pastel'],
                ['slug' => 'vivid', 'lt' => 'Ryškus', 'en' => 'Vivid'],
            ],
        ];

        $locales = $this->supportedLocales();
        $now = now();

        foreach ($map as $attrSlug => $values) {
            $attributeId = (int) DB::table('attributes')->where('slug', $attrSlug)->value('id');
            if (! $attributeId) {
                $this->command?->warn("AttributeValueSeeder: attribute '{$attrSlug}' not found, skipping.");

                continue;
            }

            $order = 1;
            foreach ($values as $v) {
                // Upsert base value row
                DB::table('attribute_values')->upsert([
                    [
                        'attribute_id' => $attributeId,
                        'slug' => $v['slug'],
                        'value' => $v['lt'], // base in default locale
                        'sort_order' => $order,
                        'is_enabled' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                ], ['attribute_id', 'slug'], ['value', 'sort_order', 'is_enabled', 'updated_at']);

                $valueId = (int) DB::table('attribute_values')->where('attribute_id', $attributeId)->where('slug', $v['slug'])->value('id');
                if (! $valueId) {
                    $order++;

                    continue;
                }

                // Translations per locale
                $trRows = [];
                foreach ($locales as $loc) {
                    $trRows[] = [
                        'attribute_value_id' => $valueId,
                        'locale' => $loc,
                        'value' => $v[$loc] ?? $v['lt'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                DB::table('attribute_value_translations')->upsert(
                    $trRows,
                    ['attribute_value_id', 'locale'],
                    ['value', 'updated_at']
                );

                $order++;
            }
        }

        $this->command?->info('AttributeValueSeeder: seeded values with translations.');
    }

    private function supportedLocales(): array
    {
        return collect(explode(',', (string) config('app.supported_locales', 'lt')))
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()->values()->all();
    }
}
