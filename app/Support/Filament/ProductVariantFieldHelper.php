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
        // Delegate to the shared searchable helper so UI state, payloads, and docs stay aligned.
        // See docs/filament/searchable-inputs.md for the helper contract and normaliser expectations.
        SearchableComponentHelper::hydrate(
            $component,
            $state,
            static fn (int|string $identifier): ?ProductVariant => self::resolveVariant((int) $identifier),
            static fn (ProductVariant $variant): array => self::normaliseVariant($variant),
        );
    }

    /**
     * Sync related order item fields whenever a product variant is picked via the lookup.
     */
    public static function handleVariantSelection(?string $state, Set $set, Get $get, ?SearchableInput $component = null): void
    {
        if ($state === null || $state === '') {
            self::clearVariantComponent($component, $set, $get);

            return;
        }

        $variant = self::resolveVariant((int) $state);

        if (! $variant instanceof ProductVariant) {
            self::clearVariantComponent($component, $set, $get);

            return;
        }

        $normalised = self::normaliseVariant($variant);

        if ($component instanceof SearchableInput) {
            // Normalise the component payload so DefStudio's widget mirrors persisted metadata.
            $component
                ->state((string) $normalised['value'])
                ->options([
                    (string) $normalised['value'] => $normalised['label'],
                ])
                ->payload($normalised['payload']);
        }

        // Store the variant linkage and mirror key product metadata onto the order item snapshot.
        $set('product_variant_id', $variant->getKey());
        $set('product_id', $variant->getAttribute('product_id'));

        $name = $normalised['payload']['name'] ?? '';
        $sku = $normalised['payload']['sku'] ?? '';

        $set('name', is_string($name) ? $name : '');
        $set('sku', is_string($sku) ? $sku : '');

        $price = (float) ($normalised['payload']['unit_price'] ?? 0);
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

    /**
     * Clear the variant component UI alongside dependent snapshot fields.
     */
    private static function clearVariantComponent(?SearchableInput $component, Set $set, Get $get): void
    {
        $clearSnapshot = static function () use ($set): void {
            self::clearVariantSelection($set);
        };

        $resetTotals = static function () use ($set, $get): void {
            self::recalculateTotals($set, $get, 0.0);
        };

        if ($component instanceof SearchableInput) {
            SearchableComponentHelper::clear(
                $component,
                $clearSnapshot,
                $resetTotals,
            );

            return;
        }

        $clearSnapshot();
        $resetTotals();
    }

    /**
     * Normalise the variant into the SearchableInput tuple consumed by the helper contract.
     *
     * @return array{value:int, label:string, payload: array{product_id:int|null, name:string, sku:string, unit_price:float}}
     */
    private static function normaliseVariant(ProductVariant $variant): array
    {
        $name = $variant->getAttribute('name') ?? optional($variant->product)->name ?? '';
        $sku = $variant->getAttribute('sku') ?? optional($variant->product)->sku ?? '';

        return [
            'value'   => $variant->getKey(),
            'label'   => ProductVariantSearch::label($variant),
            'payload' => [
                'product_id' => $variant->getAttribute('product_id'),
                'name'       => is_string($name) ? $name : '',
                'sku'        => is_string($sku) ? $sku : '',
                'unit_price' => (float) ($variant->getAttribute('price') ?? 0),
            ],
        ];
    }
}
