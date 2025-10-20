<?php

declare(strict_types=1);

namespace App\Services\Taxes;

use App\Services\Pricing\PriceConfiguration;

final class TaxCalculator
{
    public function __construct(private readonly PriceConfiguration $configuration) {}

    public function compute(float $amount, ?float $rate = null): float
    {
        $amount = max(0.0, $amount);
        $rate = $rate ?? $this->configuration->vatRate();
        if ($rate > 1.0) {
            $rate /= 100;
        }

        return $this->configuration->round($amount * $rate);
    }
}
