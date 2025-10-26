<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Data\Pricing\VariantPriceResult;
use App\Models\Currency;
use App\Models\PriceListItem;
use App\Models\ProductVariant;
use App\Models\VariantPriceHistory;
use App\Models\VariantPricingRule;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Centralised pricing engine for variants that merges base prices, price lists,
 * dynamic rules, and currency conversion into a single deterministic result.
 */
final class VariantPriceService
{
    /**
     * Cache currency rates in memory for the lifetime of the service instance so repeated
     * conversions (for example when rendering variant grids) do not perform extra queries.
     *
     * @var array<string, float>
     */
    private array $currencyRateCache = [];

    public function __construct(private readonly PriceConfiguration $configuration) {}

    /**
     * Calculate the final variant price using the provided context (quantity, customer groups,
     * and preferred currency). The result always contains a full breakdown of each adjustment.
     *
     * @param  array{quantity?: int, customer_group_ids?: array<int>, currency?: string, base_currency?: string, now?: CarbonInterface, record_history?: bool, history_reason?: string, history_price_type?: string, changed_by?: int|null}  $context
     */
    public function calculate(ProductVariant $variant, array $context = []): VariantPriceResult
    {
        // Normalise the evaluation timestamp so scheduled sales and time-boxed rules behave deterministically.
        $moment = $context['now'] ?? now();
        if (! $moment instanceof CarbonInterface) {
            $moment = now();
        }

        // Quantities drive quantity-based price list items and dynamic pricing rules; enforce sane bounds.
        $quantity = max(1, (int) ($context['quantity'] ?? 1));

        // Hydrate customer group identifiers once so the same collection can be reused throughout the routine.
        $groupIds = Collection::make($context['customer_group_ids'] ?? [])
            ->filter(fn ($id) => $id !== null)
            ->map(fn ($id) => (int) $id)
            ->values();

        // Resolve the storage currency (base) and the requested display currency (target).
        $baseCurrency = strtoupper((string) ($context['base_currency'] ?? $this->resolveBaseCurrency()));
        $targetCurrency = strtoupper((string) ($context['currency'] ?? current_currency()));
        if ($targetCurrency === '') {
            $targetCurrency = $baseCurrency;
        }
        if ($baseCurrency === '') {
            $baseCurrency = $targetCurrency;
        }

        // Capture the core price signals from the variant so adjustments can refer back to the source values.
        $regularPrice = $this->configuration->round((float) $variant->price);
        $salePrice = null;
        if ($variant->is_on_sale && $variant->isCurrentlyOnSale()) {
            $promo = $variant->promotional_price !== null ? (float) $variant->promotional_price : null;
            $salePrice = $promo !== null && $promo > 0
                ? $this->configuration->round($promo)
                : $this->configuration->round($regularPrice);
        }
        $basePrice = $salePrice ?? $regularPrice;
        $compareAt = $variant->compare_price !== null ? $this->configuration->round((float) $variant->compare_price) : null;
        $costPrice = $variant->cost_price !== null ? $this->configuration->round((float) $variant->cost_price) : null;

        // Size/variant modifiers are always applied in the storage currency, but we can optionally suppress them
        // if a variant-specific price list item already includes the adjustment.
        $rawVariantModifier = $this->configuration->round((float) ($variant->size_price_modifier ?? 0.0));
        $workingPrice = $basePrice;

        // Look up the most applicable price list entry for the provided customer groups and quantity.
        $priceListItem = $this->resolvePriceListItem($variant, $groupIds, $quantity, $moment);
        $priceListOverridesVariantModifier = false;
        $priceListPrice = null;
        $priceListId = null;

        if ($priceListItem instanceof PriceListItem) {
            $priceListId = (int) $priceListItem->price_list_id;
            $priceListCurrency = strtoupper((string) optional($priceListItem->priceList->currency)->code ?: $baseCurrency);
            $netAmount = $priceListItem->net_amount ?? $priceListItem->price ?? $priceListItem->compare_amount ?? 0.0;
            $priceListPriceBase = $this->convertAmount((float) $netAmount, $priceListCurrency, $baseCurrency);
            $priceListPrice = $priceListPriceBase;
            $workingPrice = $priceListPriceBase;
            $priceListOverridesVariantModifier = $priceListItem->variant_id !== null;
        }

        $variantModifier = $priceListOverridesVariantModifier ? 0.0 : $rawVariantModifier;
        $workingPrice += $variantModifier;

        // Evaluate dynamic pricing rules (time-based, quantity-based, and per-variant overrides).
        $dynamicAdjustments = 0.0;
        $appliedRuleIds = [];
        $rules = $this->fetchVariantPricingRules($variant);
        foreach ($rules as $rule) {
            if ($rule->customer_group_id && $groupIds->isNotEmpty() && ! $groupIds->contains((int) $rule->customer_group_id)) {
                continue;
            }

            $modifier = $rule->calculatePriceModifier($variant, $quantity, $moment);
            if (abs($modifier) < 0.0001) {
                continue;
            }

            $dynamicAdjustments += $modifier;
            $appliedRuleIds[] = (int) $rule->getKey();

            if (! $rule->is_cumulative) {
                // Non-cumulative rules short-circuit any remaining modifiers to honour exclusivity.
                break;
            }
        }
        $workingPrice += $dynamicAdjustments;

        // Clamp and round the final amount in the storage currency before converting for presentation.
        $finalPriceBase = $this->configuration->round(max(0.0, $workingPrice));

        // Convert every monetary signal into the requested display currency so analytics/reporting consumers receive
        // consistent data regardless of the visitor's locale.
        $convertedFinal = $this->convertAmount($finalPriceBase, $baseCurrency, $targetCurrency);
        $convertedRegular = $this->convertAmount($regularPrice, $baseCurrency, $targetCurrency);
        $convertedSale = $salePrice !== null ? $this->convertAmount($salePrice, $baseCurrency, $targetCurrency) : null;
        $convertedPriceList = $priceListPrice !== null ? $this->convertAmount($priceListPrice, $baseCurrency, $targetCurrency) : null;
        $convertedVariantModifier = $this->convertAmount($variantModifier, $baseCurrency, $targetCurrency);
        $convertedDynamic = $this->convertAmount($dynamicAdjustments, $baseCurrency, $targetCurrency);
        $convertedCompare = $compareAt !== null ? $this->convertAmount($compareAt, $baseCurrency, $targetCurrency) : null;
        $convertedCost = $costPrice !== null ? $this->convertAmount($costPrice, $baseCurrency, $targetCurrency) : null;

        // Optionally record a price history entry so merchandising teams can audit automated changes.
        $historyRecorded = false;
        if (! empty($context['record_history']) && abs($finalPriceBase - (float) $variant->price) >= 0.0001) {
            $effectiveFrom = $moment instanceof DateTimeInterface ? $moment->toDateTime() : null;
            VariantPriceHistory::recordPriceChange(
                variantId: (int) $variant->getKey(),
                oldPrice: (float) $variant->price,
                newPrice: $finalPriceBase,
                priceType: (string) ($context['history_price_type'] ?? 'regular'),
                changeReason: (string) ($context['history_reason'] ?? 'automatic'),
                changedBy: Arr::get($context, 'changed_by'),
                effectiveFrom: $effectiveFrom,
                effectiveUntil: null
            );
            $historyRecorded = true;
        }

        return new VariantPriceResult(
            regularPrice: $convertedRegular,
            salePrice: $convertedSale,
            priceListPrice: $convertedPriceList,
            variantModifiers: $convertedVariantModifier,
            dynamicAdjustments: $convertedDynamic,
            finalPrice: $convertedFinal,
            currency: $targetCurrency,
            priceListId: $priceListId,
            appliedRuleIds: $appliedRuleIds,
            compareAtPrice: $convertedCompare,
            costPrice: $convertedCost,
            historyRecorded: $historyRecorded,
        );
    }

    /**
     * Resolve the applicable price list entry for the provided context, preferring variant-specific
     * rows and falling back to product-level defaults when required.
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
     * Convert amounts between currencies using the exchange rates stored in the database.
     */
    private function convertAmount(float $amount, string $fromCurrency, string $toCurrency): float
    {
        if (abs($amount) < 0.0001 || $fromCurrency === $toCurrency) {
            return $this->configuration->round($amount);
        }

        $fromRate = $this->resolveCurrencyRate($fromCurrency);
        $toRate = $this->resolveCurrencyRate($toCurrency);
        if ($fromRate === null || $toRate === null || $fromRate <= 0.0 || $toRate <= 0.0) {
            return $this->configuration->round($amount);
        }

        // First normalise the amount back to the base currency before converting to the target.
        $amountInBase = $amount / $fromRate;
        $converted = $amountInBase * $toRate;

        return $this->configuration->round($converted);
    }

    /**
     * Resolve the configured base currency (defaults to the pricing configuration).
     */
    private function resolveBaseCurrency(): string
    {
        $configured = $this->configuration->currency();
        if (is_string($configured) && $configured !== '') {
            return strtoupper($configured);
        }

        $default = Currency::query()->where('is_default', true)->value('code');

        return is_string($default) && $default !== '' ? strtoupper($default) : 'EUR';
    }

    /**
     * Retrieve a currency's exchange rate, caching the result in memory for the request lifecycle.
     */
    private function resolveCurrencyRate(string $code): ?float
    {
        $code = strtoupper($code);
        if (array_key_exists($code, $this->currencyRateCache)) {
            return $this->currencyRateCache[$code];
        }

        $rate = Currency::query()->where('code', $code)->value('exchange_rate');
        if ($rate === null) {
            return $this->currencyRateCache[$code] = null;
        }

        return $this->currencyRateCache[$code] = (float) $rate;
    }
}
