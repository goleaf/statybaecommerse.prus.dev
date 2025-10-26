<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AttributeValue;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

final class ProductVariantAttributeMatrixService
{
    /**
     * Synchronize variant attribute relations based on matrix or legacy selections.
     */
    public static function sync(ProductVariant $variant, array $matrix = [], array $legacySelections = []): void
    {
        $selections = self::normalizeSelections($matrix, $legacySelections);

        if ($selections->isEmpty()) {
            $variant->attributes()->detach();
            $variant->variantAttributeValues()->delete();

            return;
        }

        $attributeValues = AttributeValue::query()
            ->with('attribute')
            ->whereIn('id', $selections->pluck('attribute_value_id')->all())
            ->get()
            ->keyBy(static fn (AttributeValue $value) => $value->getKey());

        $pivotData = [];
        $variantAttributeValues = [];

        foreach ($selections as $index => $selection) {
            $attributeValue = $attributeValues->get($selection['attribute_value_id']);

            if (! $attributeValue) {
                continue;
            }

            $attribute = $attributeValue->attribute;
            $attributeId = $attribute?->getKey() ?? $selection['attribute_id'];

            if (! $attributeId) {
                continue;
            }

            $pivotData[$attributeValue->getKey()] = [
                'attribute_id' => $attributeId,
            ];

            $variantAttributeValues[] = [
                'variant_id'              => $variant->getKey(),
                'attribute_id'            => $attributeId,
                'attribute_name'          => $attribute?->name,
                'attribute_value'         => $attributeValue->value,
                'attribute_value_display' => $attributeValue->display_value ?? $attributeValue->value,
                'attribute_value_lt'      => $attributeValue->getTranslation('value', 'lt', false) ?? $attributeValue->value,
                'attribute_value_en'      => $attributeValue->getTranslation('value', 'en', false) ?? $attributeValue->value,
                'attribute_value_slug'    => $attributeValue->slug,
                'sort_order'              => $index,
                'is_filterable'           => (bool) ($attribute?->is_filterable ?? true),
                'is_searchable'           => (bool) ($attribute?->is_searchable ?? true),
            ];
        }

        if (empty($pivotData)) {
            $variant->attributes()->detach();
            $variant->variantAttributeValues()->delete();

            return;
        }

        $variant->attributes()->sync($pivotData);
        $variant->variantAttributeValues()->delete();

        if (! empty($variantAttributeValues)) {
            $variant->variantAttributeValues()->createMany($variantAttributeValues);
        }
    }

    private static function normalizeSelections(array $matrix, array $legacySelections): Collection
    {
        $matrixSelections = self::normalizeMatrix($matrix);
        $legacy = self::normalizeLegacySelections($legacySelections);

        return $matrixSelections
            ->concat($legacy)
            ->filter(fn (array $selection): bool => isset($selection['attribute_id'], $selection['attribute_value_id']))
            ->unique(fn (array $selection): string => $selection['attribute_id'] . '-' . $selection['attribute_value_id'])
            ->values();
    }

    private static function normalizeLegacySelections(array $selections): Collection
    {
        return collect($selections)
            ->map(function ($selection): ?array {
                $attributeId = data_get($selection, 'attribute_id');
                $attributeValueId = data_get($selection, 'attribute_value_id');

                if (! is_numeric($attributeId) || ! is_numeric($attributeValueId)) {
                    return null;
                }

                return [
                    'attribute_id'       => (int) $attributeId,
                    'attribute_value_id' => (int) $attributeValueId,
                ];
            })
            ->filter()
            ->values();
    }

    private static function normalizeMatrix(array $matrix): Collection
    {
        return collect($matrix)
            ->map(fn ($value, $key) => self::extractSelectionsFromMatrixEntry($key, $value))
            ->flatten(1)
            ->filter()
            ->values();
    }

    /**
     * @return array<int, array{attribute_id:int, attribute_value_id:int}>
     */
    private static function extractSelectionsFromMatrixEntry(string|int $attributeKey, mixed $value): array
    {
        $attributeId = self::parseAttributeKey($attributeKey);

        if ($attributeId === null) {
            return [];
        }

        if (is_array($value)) {
            $selectedKeys = self::normalizeArraySelection($value);

            return collect($selectedKeys)
                ->filter(fn ($selected): bool => is_numeric($selected))
                ->map(fn ($selected): array => [
                    'attribute_id'       => $attributeId,
                    'attribute_value_id' => (int) $selected,
                ])
                ->values()
                ->all();
        }

        if (is_numeric($value)) {
            return [[
                'attribute_id'       => $attributeId,
                'attribute_value_id' => (int) $value,
            ]];
        }

        return [];
    }

    /**
     * @return array<int, int|string>
     */
    private static function normalizeArraySelection(array $value): array
    {
        if (array_values($value) === $value) {
            return $value;
        }

        return collect($value)
            ->filter(fn ($selected): bool => (bool) $selected)
            ->keys()
            ->all();
    }

    private static function parseAttributeKey(string|int $key): ?int
    {
        if (is_int($key) || ctype_digit((string) $key)) {
            return (int) $key;
        }

        if (preg_match('/(\d+)/', (string) $key, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }
}
