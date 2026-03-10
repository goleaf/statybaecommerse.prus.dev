<?php

declare(strict_types=1);

namespace App\Services\Taxes;

use App\Services\Pricing\PriceConfiguration;

final class TaxCalculator
{
    public function __construct(private readonly PriceConfiguration $configuration) {}

    public function compute(float $amount, ?float $rate = null): float
    {
        if (! $this->configuration->vatEnabled()) {
            return 0.0;
        }

        $amount = max(0.0, $amount);
        $rate = $rate ?? $this->configuration->vatRate();
        if ($rate > 1.0) {
            $rate /= 100;
        }

        return $this->configuration->round($amount * $rate);
    }

    public function getTaxRate(?float $rate = null, bool $asPercent = true): float
    {
        if (! $this->configuration->vatEnabled()) {
            return 0.0;
        }

        $rate = $rate ?? $this->configuration->vatRate();

        if ($asPercent) {
            return $rate > 1.0 ? $rate : $rate * 100;
        }

        return $rate > 1.0 ? $rate / 100 : $rate;
    }
}
