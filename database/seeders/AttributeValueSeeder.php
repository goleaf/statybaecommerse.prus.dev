<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;

final class AttributeValueSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = collect([
            'color' => [
                ['value' => 'Raudona', 'display_value' => 'R', 'color_code' => '#FF0000'],
                ['value' => 'Mėlyna', 'display_value' => 'M', 'color_code' => '#0000FF'],
                ['value' => 'Žalia', 'display_value' => 'Ž', 'color_code' => '#00FF00'],
                ['value' => 'Juoda', 'display_value' => 'J', 'color_code' => '#000000'],
                ['value' => 'Balta', 'display_value' => 'B', 'color_code' => '#FFFFFF'],
            ],
            'size' => [
                ['value' => 'XS', 'display_value' => 'Ekstra mažas'],
                ['value' => 'S', 'display_value' => 'Mažas'],
                ['value' => 'M', 'display_value' => 'Vidutinis'],
                ['value' => 'L', 'display_value' => 'Didelis'],
                ['value' => 'XL', 'display_value' => 'Ekstra didelis'],
            ],
            'material' => [
                ['value' => 'Medvilnė', 'display_value' => 'Medvilnė'],
                ['value' => 'Poliesteris', 'display_value' => 'Poliesteris'],
                ['value' => 'Vilna', 'display_value' => 'Vilna'],
                ['value' => 'Šilkas', 'display_value' => 'Šilkas'],
            ],
        ]);

        $definitions->each(function (array $values, string $attributeSlug): void {
            $attribute = Attribute::query()->firstWhere('slug', $attributeSlug);

            if (! $attribute) {
                return;
            }

            foreach ($values as $index => $value) {
                AttributeValue::query()->updateOrCreate(
                    [
                        'attribute_id' => $attribute->getKey(),
                        'slug' => str($value['value'])->slug()->toString(),
                    ],
                    [
                        'value' => $value['value'],
                        'display_value' => $value['display_value'],
                        'color_code' => $value['color_code'] ?? null,
                        'is_enabled' => true,
                        'is_active' => true,
                        'sort_order' => $index + 1,
                    ],
                );
            }
        });
    }
}
