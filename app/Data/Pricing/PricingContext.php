<?php

declare(strict_types=1);

namespace App\Data\Pricing;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class PricingContext extends Data
{
    public function __construct(
        public readonly int $quantity,
        public readonly Collection $customerGroupIds,
        public readonly string $baseCurrency,
        public readonly string $targetCurrency,
        public readonly CarbonInterface $moment,
        public readonly bool $recordHistory = false,
        public readonly ?string $historyReason = null,
        public readonly ?string $historyPriceType = null,
        public readonly ?int $changedBy = null,
    ) {}

    public static function fromArray(array $context): self
    {
        $moment = $context['now'] ?? now();
        if (! $moment instanceof CarbonInterface) {
            $moment = now();
        }

        $quantity = max(1, (int) ($context['quantity'] ?? 1));

        $groupIds = Collection::make($context['customer_group_ids'] ?? [])
            ->filter(fn ($id) => $id !== null)
            ->map(fn ($id) => (int) $id)
            ->values();

        $baseCurrency = strtoupper((string) ($context['base_currency'] ?? config('pricing.base_currency', 'EUR')));
        $targetCurrency = strtoupper((string) ($context['currency'] ?? current_currency()));

        if ($targetCurrency === '') {
            $targetCurrency = $baseCurrency;
        }
        if ($baseCurrency === '') {
            $baseCurrency = $targetCurrency;
        }

        return new self(
            quantity: $quantity,
            customerGroupIds: $groupIds,
            baseCurrency: $baseCurrency,
            targetCurrency: $targetCurrency,
            moment: $moment,
            recordHistory: (bool) ($context['record_history'] ?? false),
            historyReason: $context['history_reason'] ?? null,
            historyPriceType: $context['history_price_type'] ?? null,
            changedBy: $context['changed_by'] ?? null,
        );
    }
}
