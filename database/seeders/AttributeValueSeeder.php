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
                [
                    'value'        => 'Raudona',
                    'display_value'=> 'R',
                    'color_code'   => '#FF0000',
                    'translations' => [
                        'lt' => 'Raudona',
                        'en' => 'Red',
                        'de' => 'Rot',
                        'ru' => 'Красный',
                    ],
                ],
                [
                    'value'        => 'Mėlyna',
                    'display_value'=> 'M',
                    'color_code'   => '#0000FF',
                    'translations' => [
                        'lt' => 'Mėlyna',
                        'en' => 'Blue',
                        'de' => 'Blau',
                        'ru' => 'Синий',
                    ],
                ],
                [
                    'value'        => 'Žalia',
                    'display_value'=> 'Ž',
                    'color_code'   => '#00FF00',
                    'translations' => [
                        'lt' => 'Žalia',
                        'en' => 'Green',
                        'de' => 'Grün',
                        'ru' => 'Зелёный',
                    ],
                ],
                [
                    'value'        => 'Juoda',
                    'display_value'=> 'J',
                    'color_code'   => '#000000',
                    'translations' => [
                        'lt' => 'Juoda',
                        'en' => 'Black',
                        'de' => 'Schwarz',
                        'ru' => 'Чёрный',
                    ],
                ],
                [
                    'value'        => 'Balta',
                    'display_value'=> 'B',
                    'color_code'   => '#FFFFFF',
                    'translations' => [
                        'lt' => 'Balta',
                        'en' => 'White',
                        'de' => 'Weiß',
                        'ru' => 'Белый',
                    ],
                ],
            ],
            'size' => [
                [
                    'value'        => 'XS',
                    'display_value'=> 'Ekstra mažas',
                    'translations' => [
                        'lt' => 'Ekstra mažas',
                        'en' => 'Extra Small',
                        'de' => 'Extra Klein',
                        'ru' => 'Очень маленький',
                    ],
                ],
                [
                    'value'        => 'S',
                    'display_value'=> 'Mažas',
                    'translations' => [
                        'lt' => 'Mažas',
                        'en' => 'Small',
                        'de' => 'Klein',
                        'ru' => 'Маленький',
                    ],
                ],
                [
                    'value'        => 'M',
                    'display_value'=> 'Vidutinis',
                    'translations' => [
                        'lt' => 'Vidutinis',
                        'en' => 'Medium',
                        'de' => 'Mittel',
                        'ru' => 'Средний',
                    ],
                ],
                [
                    'value'        => 'L',
                    'display_value'=> 'Didelis',
                    'translations' => [
                        'lt' => 'Didelis',
                        'en' => 'Large',
                        'de' => 'Groß',
                        'ru' => 'Большой',
                    ],
                ],
                [
                    'value'        => 'XL',
                    'display_value'=> 'Ekstra didelis',
                    'translations' => [
                        'lt' => 'Ekstra didelis',
                        'en' => 'Extra Large',
                        'de' => 'Extra Groß',
                        'ru' => 'Очень большой',
                    ],
                ],
            ],
            'material' => [
                [
                    'value'        => 'Medvilnė',
                    'display_value'=> 'Medvilnė',
                    'translations' => [
                        'lt' => 'Medvilnė',
                        'en' => 'Cotton',
                        'de' => 'Baumwolle',
                        'ru' => 'Хлопок',
                    ],
                ],
                [
                    'value'        => 'Poliesteris',
                    'display_value'=> 'Poliesteris',
                    'translations' => [
                        'lt' => 'Poliesteris',
                        'en' => 'Polyester',
                        'de' => 'Polyester',
                        'ru' => 'Полиэстер',
                    ],
                ],
                [
                    'value'        => 'Vilna',
                    'display_value'=> 'Vilna',
                    'translations' => [
                        'lt' => 'Vilna',
                        'en' => 'Wool',
                        'de' => 'Wolle',
                        'ru' => 'Шерсть',
                    ],
                ],
                [
                    'value'        => 'Šilkas',
                    'display_value'=> 'Šilkas',
                    'translations' => [
                        'lt' => 'Šilkas',
                        'en' => 'Silk',
                        'de' => 'Seide',
                        'ru' => 'Шёлк',
                    ],
                ],
            ],
        ]);

        $definitions->each(function (array $values, string $attributeSlug): void {
            $attribute = Attribute::query()->firstWhere('slug', $attributeSlug);

            if (! $attribute) {
                return;
            }

            foreach ($values as $index => $value) {
                $attributeValue = AttributeValue::query()->updateOrCreate(
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

                $this->syncTranslations($attributeValue, $value);
            }
        });
    }

    /**
     * @param array{
     *     value: string,
     *     display_value?: string|null,
     *     translations?: array<string, string>
     * } $definition
     */
    private function syncTranslations(AttributeValue $attributeValue, array $definition): void
    {
        if (! method_exists($attributeValue, 'translations')) {
            return;
        }

        $locales = $this->supportedLocales();

        foreach ($locales as $locale) {
            $translatedValue = $definition['translations'][$locale] ?? $definition['display_value'] ?? $definition['value'];

            if (! filled($translatedValue)) {
                continue;
            }

            $attributeValue->translations()->updateOrCreate(
                ['locale' => $locale],
                ['value' => $translatedValue]
            );
        }
    }

    /**
     * @return array<int, string>
     */
    private function supportedLocales(): array
    {
        $locales = config('app.supported_locales', ['lt', 'en']);

        if (is_string($locales)) {
            $locales = explode(',', $locales);
        }

        return collect($locales)
            ->map(static fn ($locale): string => trim((string) $locale))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
