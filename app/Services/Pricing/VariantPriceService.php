<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Data\Pricing\PricingContext;
use App\Data\Pricing\VariantPriceResult;
use App\Models\PriceListItem;
use App\Models\ProductVariant;
use App\Models\VariantPricingRule;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Centralised pricing engine for variants that merges base prices, price lists,
 * dynamic rules, and currency conversion into a single deterministic result.
 */
final class VariantPriceService
{
    public function __construct(
        private readonly PriceConfiguration $configuration,
        private readonly CurrencyConversionService $currencyService
    ) {}

    /**
     * Calculate the final variant price using the provided context.
     *
     * @param array{quantity?: int, customer_group_ids?: array<int>, currency?: string, base_currency?: string, now?: CarbonInterface, record_history?: bool, history_reason?: string, history_price_type?: string, changed_by?: int|null} $context
     */
    public function calculate(ProductVariant $variant, array $context = []): VariantPriceResult
    {
        $pricingContext = PricingContext::fromArray($context);

        return $this->calculateWithContext($variant, $pricingContext);
    }

    private function calculateWithContext(ProductVariant $variant, PricingContext $context): VariantPriceResult
    {
        // Get base pricing signals
        $basePrices = $this->extractBasePrices($variant);

        // Apply price list adjustments
        $priceListResult = $this->applyPriceListAdjustments($variant, $context, $basePrices);

        // Apply dynamic pricing rules
        $dynamicResult = $this->applyDynamicPricingRules(
            $variant,
            $context,
            $priceListResult['workingPrice']
        );

        // Finalize and convert currencies
        return $this->finalizePrice(
            $variant,
            $context,
            $basePrices,
            $priceListResult,
            $dynamicResult
        );
    }

    /**
     * Resolve the applicable price list entry for the provided context.
     */
    private function resolvePriceListItem(
        ProductVariant $variant,
        Collection $groupIds,
        int $quantity,
        CarbonInterface $moment
    ): ?PriceListItem {
        if ($groupIds->isEmpty()) {
            return null;
        }

        return PriceListItem::query()
            ->with(['priceList.currency'])
            ->where(static function (Builder $query) use ($variant): void {
                $query->where('variant_id', $variant->getKey())
                    ->orWhere(static function (Builder $subQuery) use ($variant): void {
                        $subQuery->whereNull('variant_id')
                            ->where('product_id', $variant->product_id);
                    });
            })
            ->where('is_active', true)
            ->where(function (Builder $query) use ($moment): void {
                $query->whereNull('valid_from')->orWhere('valid_from', '<=', $moment);
            })
            ->where(function (Builder $query) use ($moment): void {
                $query->whereNull('valid_until')->orWhere('valid_until', '>=', $moment);
            })
            ->where(function (Builder $query) use ($quantity): void {
                $query->whereNull('min_quantity')->orWhere('min_quantity', '<=', $quantity);
            })
            ->where(function (Builder $query) use ($quantity): void {
                $query->whereNull('max_quantity')->orWhere('max_quantity', '>=', $quantity);
            })
            ->whereHas('priceList', function (Builder $query) use ($groupIds, $moment): void {
                $query->where('is_enabled', true)
                    ->where(function (Builder $q) use ($moment): void {
                        $q->whereNull('starts_at')->orWhere('starts_at', '<=', $moment);
                    })
                    ->where(function (Builder $q) use ($moment): void {
                        $q->whereNull('ends_at')->orWhere('ends_at', '>=', $moment);
                    })
                    ->whereHas('customerGroups', static function (Builder $relation) use ($groupIds): void {
                        $relation->whereIn('customer_groups.id', $groupIds);
                    });
            })
            ->orderByDesc('variant_id')
            ->orderBy('priority')
            ->first();
    }

    /**
     * Fetch the ordered list of dynamic pricing rules that may apply to the provided variant.
     *
     * @return Collection<int, VariantPricingRule>
     */
    private function fetchVariantPricingRules(ProductVariant $variant): Collection
    {
        return VariantPricingRule::query()
            ->where(function (Builder $query) use ($variant): void {
                $query->where('product_id', $variant->product_id)
                    ->orWhere('product_variant_id', $variant->getKey());
            })
            ->active()
            ->orderedByPriority()
            ->get();
    }

    /**
     * Extract base pricing signals from the variant.
     */
    private function extractBasePrices(ProductVariant $variant): array
    {
        $regularPrice = $this->configuration->round((float) $variant->price);
        $salePrice = null;

        if ($variant->is_on_sale && $variant->isCurrentlyOnSale()) {
            $promo = $variant->promotional_price !== null ? (float) $variant->promotional_price : null;
            $salePrice = $promo !== null && $promo > 0
                ? $this->configuration->round($promo)
                : $this->configuration->round($regularPrice);
        }

        return [
            'regular'          => $regularPrice,
            'sale'             => $salePrice,
            'base'             => $salePrice ?? $regularPrice,
            'cost'             => $variant->cost_price !== null ? $this->configuration->round((float) $variant->cost_price) : null,
            'variant_modifier' => $this->configuration->round((float) ($variant->size_price_modifier ?? 0.0)),
        ];
    }

    /**
     * Apply price list adjustments to the base price.
     */
    private function applyPriceListAdjustments(ProductVariant $variant, PricingContext $context, array $basePrices): array
    {
        $priceListItem = $this->resolvePriceListItem($variant, $context->customerGroupIds, $context->quantity, $context->moment);
        $workingPrice = $basePrices['base'];
        $priceListPrice = null;
        $priceListId = null;
        $priceListOverridesVariantModifier = false;

        if ($priceListItem instanceof PriceListItem) {
            $priceListId = (int) $priceListItem->price_list_id;
            $priceListCurrency = strtoupper((string) optional($priceListItem->priceList->currency)->code ?: $context->baseCurrency);
            $netAmount = $priceListItem->net_amount ?? $priceListItem->price ?? $priceListItem->compare_amount ?? 0.0;
            $priceListPriceBase = $this->currencyService->convert((float) $netAmount, $priceListCurrency, $context->baseCurrency);
            $priceListPrice = $priceListPriceBase;
            $workingPrice = $priceListPriceBase;
            $priceListOverridesVariantModifier = $priceListItem->variant_id !== null;
        }

        $variantModifier = $priceListOverridesVariantModifier ? 0.0 : $basePrices['variant_modifier'];
        $workingPrice += $variantModifier;

        return [
            'workingPrice'    => $workingPrice,
            'priceListPrice'  => $priceListPrice,
            'priceListId'     => $priceListId,
            'variantModifier' => $variantModifier,
        ];
    }

    /**
     * Apply dynamic pricing rules to the working price.
     */
    private function applyDynamicPricingRules(ProductVariant $variant, PricingContext $context, float $workingPrice): array
    {
        $dynamicAdjustments = 0.0;
        $appliedRuleIds = [];
        $rules = $this->fetchVariantPricingRules($variant);

        foreach ($rules as $rule) {
            if ($rule->customer_group_id && $context->customerGroupIds->isNotEmpty() && ! $context->customerGroupIds->contains((int) $rule->customer_group_id)) {
                continue;
            }

            $modifier = $rule->calculatePriceModifier($variant, $context->quantity, $context->moment);
            if (abs($modifier) < 0.0001) {
                continue;
            }

            $dynamicAdjustments += $modifier;
            $appliedRuleIds[] = (int) $rule->getKey();

            if (! $rule->is_cumulative) {
                break;
            }
        }

        return [
            'adjustments'       => $dynamicAdjustments,
            'appliedRuleIds'    => $appliedRuleIds,
            'finalWorkingPrice' => $workingPrice + $dynamicAdjustments,
        ];
    }

    /**
     * Finalize the price calculation and convert to target currency.
     */
    private function finalizePrice(
        ProductVariant $variant,
        PricingContext $context,
        array $basePrices,
        array $priceListResult,
        array $dynamicResult
    ): VariantPriceResult {
        $finalPriceBase = $this->configuration->round(max(0.0, $dynamicResult['finalWorkingPrice']));

        // Convert all monetary values to target currency
        $convertedFinal = $this->currencyService->convert($finalPriceBase, $context->baseCurrency, $context->targetCurrency);
        $convertedRegular = $this->currencyService->convert($basePrices['regular'], $context->baseCurrency, $context->targetCurrency);
        $convertedPriceList = $priceListResult['priceListPrice'] !== null ? $this->currencyService->convert($priceListResult['priceListPrice'], $context->baseCurrency, $context->targetCurrency) : null;
        $convertedVariantModifier = $this->currencyService->convert($priceListResult['variantModifier'], $context->baseCurrency, $context->targetCurrency);
        $convertedDynamic = $this->currencyService->convert($dynamicResult['adjustments'], $context->baseCurrency, $context->targetCurrency);
        $convertedCost = $basePrices['cost'] !== null ? $this->currencyService->convert($basePrices['cost'], $context->baseCurrency, $context->targetCurrency) : null;

        return new VariantPriceResult(
            regularPrice: $convertedRegular,
            priceListPrice: $convertedPriceList,
            variantModifiers: $convertedVariantModifier,
            dynamicAdjustments: $convertedDynamic,
            finalPrice: $convertedFinal,
            currency: $context->targetCurrency,
            priceListId: $priceListResult['priceListId'],
            appliedRuleIds: $dynamicResult['appliedRuleIds'],
            compareAtPrice: null,
            costPrice: $convertedCost,
            historyRecorded: false, // Price history recording has been removed
        );
    }
}
