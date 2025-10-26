<?php

declare(strict_types=1);

namespace App\Data\Pricing;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Value object describing the calculated price output for a product variant.
 *
 * @implements Arrayable<string, float|int|string|null|bool>
 */
final class VariantPriceResult implements Arrayable
{
    /**
     * Expose the aggregated pricing data that downstream callers need when rendering
     * variant prices or persisting analytics. All numeric amounts are presented in the
     * resolved display currency so storefront and admin experiences stay in sync.
     */
    public function __construct(
        public readonly float $regularPrice,
        public readonly ?float $salePrice,
        public readonly ?float $priceListPrice,
        public readonly float $variantModifiers,
        public readonly float $dynamicAdjustments,
        public readonly float $finalPrice,
        public readonly string $currency,
        public readonly ?int $priceListId,
        /** @var list<int> */ public readonly array $appliedRuleIds,
        public readonly ?float $compareAtPrice,
        public readonly ?float $costPrice,
        public readonly bool $historyRecorded
    ) {}

    /**
     * Provide an array representation that can be serialised or consumed by Blade/Livewire.
     *
     * @return array<string, float|int|string|null|bool>
     */
    public function toArray(): array
    {
        return [
            'regular_price'        => $this->regularPrice,
            'sale_price'           => $this->salePrice,
            'price_list_price'     => $this->priceListPrice,
            'variant_modifiers'    => $this->variantModifiers,
            'dynamic_adjustments'  => $this->dynamicAdjustments,
            'final_price'          => $this->finalPrice,
            'currency'             => $this->currency,
            'price_list_id'        => $this->priceListId,
            'applied_rule_ids'     => $this->appliedRuleIds,
            'compare_at_price'     => $this->compareAtPrice,
            'cost_price'           => $this->costPrice,
            'history_recorded'     => $this->historyRecorded,
        ];
    }
}
