<?php

declare(strict_types=1);

namespace App\Support\Filament;

use App\Models\ProductVariant;
use App\Support\Search\ProductVariantSearch;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Forms\Get;
use Filament\Forms\Set;

/**
 * ProductVariantFieldHelper
 *
 * Centralises the logic for Filament searchable inputs that hydrate product variant data.
 */
final class ProductVariantFieldHelper
{
    /**
     * Hydrate the searchable input with the persisted option while editing a record.
     */
    public static function hydrateSearchableVariant(SearchableInput $component, ?int $state): void
    {
        if ($state === null) {
            return;
        }

        $variant = self::resolveVariant($state);

        if (! $variant instanceof ProductVariant) {
            return;
        }

        // Persist the selected option so Filament keeps the lookup pre-filled on edit screens.
        $component->state((string) $variant->getKey())
            ->options([
                (string) $variant->getKey() => ProductVariantSearch::label($variant),
            ]);
    }

    /**
     * Sync related order item fields whenever a product variant is picked via the lookup.
     */
    public static function handleVariantSelection(?string $state, Set $set, Get $get): void
    {
        if ($state === null || $state === '') {
            self::clearVariantSelection($set);
            self::recalculateTotals($set, $get, 0.0);

            return;
        }

        $variant = self::resolveVariant((int) $state);

        if (! $variant instanceof ProductVariant) {
            self::clearVariantSelection($set);
            self::recalculateTotals($set, $get, 0.0);

            return;
        }

        // Store the variant linkage and mirror key product metadata onto the order item snapshot.
        $set('product_variant_id', $variant->getKey());
        $set('product_id', $variant->getAttribute('product_id'));

        $name = $variant->getAttribute('name') ?? optional($variant->product)->name ?? '';
        $sku = $variant->getAttribute('sku') ?? optional($variant->product)->sku ?? '';

        $set('name', is_string($name) ? $name : '');
        $set('sku', is_string($sku) ? $sku : '');

        $price = (float) ($variant->getAttribute('price') ?? 0);
        $set('unit_price', $price);

        self::recalculateTotals($set, $get, $price);
    }

    /**
     * Reset lookup-dependent fields when the variant is cleared.
     */
    private static function clearVariantSelection(Set $set): void
    {
        // Clear identifier fields and any derived metadata so stale details never leak into a new selection.
        $set('product_variant_id', null);
        $set('product_id', null);
        $set('name', '');
        $set('sku', '');
        $set('unit_price', 0.0);
    }

    /**
     * Calculate and persist the order item total after quantity/discount adjustments.
     */
    private static function recalculateTotals(Set $set, Get $get, float $unitPrice): void
    {
        $quantity = (int) ($get('quantity') ?? 1);
        $discount = (float) ($get('discount_amount') ?? 0);

        // Guard against negative totals when the discount exceeds the subtotal by flooring at zero.
        $total = max(0.0, ($unitPrice * $quantity) - $discount);

        $set('total', $total);
    }

    /**
     * Locate the variant with its parent product metadata to minimise duplicate query snippets.
     */
    private static function resolveVariant(int $variantId): ?ProductVariant
    {
        return ProductVariant::query()
            ->select(['id', 'product_id', 'sku', 'name', 'price'])
            ->with(['product:id,sku,name'])
            ->find($variantId);
    }
}
