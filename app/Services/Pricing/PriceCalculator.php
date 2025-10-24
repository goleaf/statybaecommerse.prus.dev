<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Data\Pricing\PriceBreakdown;

final class PriceCalculator
{
    public function __construct(private readonly PriceConfiguration $configuration) {}

    public function breakdown(float $subtotal, float $discount = 0.0, ?float $shipping = null, ?float $vatRate = null): PriceBreakdown
    {
        $subtotal = max(0.0, $subtotal);
        $discount = max(0.0, min($discount, $subtotal));
        $taxable = max(0.0, $subtotal - $discount);
        $rate = $this->normalizeRate($vatRate ?? $this->configuration->vatRate());
        $tax = $this->configuration->round($taxable * $rate);
        $shipping = $shipping ?? $this->resolveShipping($subtotal);
        $shipping = $this->configuration->round(max(0.0, $shipping));
        $total = $this->configuration->round($taxable + $tax + $shipping);

        return new PriceBreakdown(
            subtotal: $this->configuration->round($subtotal),
            discount: $this->configuration->round($discount),
            taxableAmount: $this->configuration->round($taxable),
            tax: $tax,
            shipping: $shipping,
            total: $total,
            currency: $this->configuration->currency(),
            vatRate: $rate,
        );
    }

    private function resolveShipping(float $subtotal): float
    {
        $threshold = $this->configuration->freeShippingThreshold();

        if ($threshold > 0.0 && $subtotal >= $threshold) {
            return 0.0;
        }

        return $this->configuration->shippingFlatRate();
    }

    private function normalizeRate(float $rate): float
    {
        if ($rate > 1.0) {
            $rate /= 100;
        }

        return max(0.0, $rate);
    }

    public function formatAmount(float $amount): string
    {
        $rounded = $this->configuration->round($amount);

        return app_money_format($rounded, $this->configuration->currency());
    }
}
