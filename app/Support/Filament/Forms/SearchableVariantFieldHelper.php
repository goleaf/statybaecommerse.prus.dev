<?php

declare(strict_types=1);

namespace App\Support\Filament\Forms;

use App\Models\ProductVariant;
use App\Support\Search\ProductVariantSearch;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Forms\Get;
use Filament\Forms\Set;

/**
 * Centralised helper for hydrating and clearing product variant searchable inputs.
 *
 * Detailed metadata and usage guidelines live in docs/forms/SEARCHABLE_INPUT_METADATA.md.
 */
final class SearchableVariantFieldHelper
{
    /**
     * Prevent instantiation because this helper only exposes static utility methods.
     */
    private function __construct()
    {
    }

    /**
     * Hydrate the searchable input state so Filament keeps the cached option list in sync.
     */
    public static function hydrate(SearchableInput $component, ?int $state, ?ProductVariant $resolvedVariant = null): void
    {
        if ($state === null) {
            return;
        }

        $variant = self::resolveVariant($state, $resolvedVariant);

        if (! $variant instanceof ProductVariant) {
            return;
        }

        $component
            ->state((string) $variant->getKey())
            ->options([
                (string) $variant->getKey() => ProductVariantSearch::label($variant),
            ]);
    }

    /**
     * Apply lookup metadata to dependent fields or clear them entirely when nothing is selected.
     */
    public static function handleUpdated(?string $state, Set $set, Get $get): void
    {
        if ($state === null || $state === '') {
            self::clearDependentFields($set, $get);

            return;
        }

        $variant = self::resolveVariant((int) $state);

        if (! $variant instanceof ProductVariant) {
            return;
        }

        $set('product_variant_id', $variant->getKey());
        $set('product_id', $variant->getAttribute('product_id'));

        $name = $variant->getAttribute('name') ?? optional($variant->product)->name ?? '';
        $sku = $variant->getAttribute('sku') ?? optional($variant->product)->sku ?? '';

        $set('name', is_string($name) ? $name : '');
        $set('sku', is_string($sku) ? $sku : '');

        $price = (float) ($variant->getAttribute('price') ?? 0);
        $set('unit_price', $price);

        $quantity = (int) ($get('quantity') ?? 1);
        $discount = (float) ($get('discount_amount') ?? 0);
        $set('total', self::calculateTotal($price, $quantity, $discount));
    }

    /**
     * Resolve the product variant either from an injected instance or the database.
     */
    private static function resolveVariant(int $variantId, ?ProductVariant $resolvedVariant = null): ?ProductVariant
    {
        if ($resolvedVariant instanceof ProductVariant && $resolvedVariant->getKey() === $variantId) {
            return $resolvedVariant;
        }

        return ProductVariant::query()
            ->select(['id', 'product_id', 'sku', 'name', 'price'])
            ->with(['product:id,sku,name'])
            ->find($variantId);
    }

    /**
     * Clear dependent fields when no variant is selected to avoid stale metadata persisting.
     */
    private static function clearDependentFields(Set $set, Get $get): void
    {
        $set('product_variant_id', null);
        $set('product_id', null);
        $set('name', '');
        $set('sku', '');
        $set('unit_price', 0.0);

        $quantity = (int) ($get('quantity') ?? 1);
        $discount = (float) ($get('discount_amount') ?? 0);
        $set('total', self::calculateTotal(0.0, $quantity, $discount));
    }

    /**
     * Calculate the line total with guard rails to avoid negative numbers when discounts exceed price.
     */
    private static function calculateTotal(float $price, int $quantity, float $discount): float
    {
        $rawTotal = ($price * $quantity) - $discount;

        return max(0.0, $rawTotal);
    }
}
