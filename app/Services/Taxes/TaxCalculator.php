<?php

declare(strict_types=1);

namespace App\Services\Taxes;

/**
 * TaxCalculator
 *
 * Service class containing TaxCalculator business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class TaxCalculator
{
    /**
     * Calculate tax for a taxable amount using configured or overridden rates.
     */
    public function compute(float $amount, ?string $zone = null, ?float $overrideRate = null): float
    {
        $rate = $this->resolveRate($zone, $overrideRate);
        if ($rate <= 0.0 || $amount <= 0.0) {
            return 0.0;
        }

        return round($amount * $rate, (int) config('pricing.rounding_precision', 2));
    }

    /**
     * Retrieve the tax rate for a zone.
     */
    public function getTaxRate(?string $zone = null, bool $asPercent = true): float
    {
        $rate = $this->resolveRate($zone, null);

        return $asPercent ? $rate * 100 : $rate;
    }

    private function resolveRate(?string $zone, ?float $overrideRate): float
    {
        if ($overrideRate !== null) {
            return $this->normalizeRate($overrideRate);
        }

        $zones = config('tax.zones', []);
        if ($zone !== null && array_key_exists($zone, $zones)) {
            return $this->normalizeRate((float) $zones[$zone]);
        }

        return $this->normalizeRate((float) config('tax.default_rate', 0));
    }

    private function normalizeRate(float $rate): float
    {
        return $rate > 1 ? $rate / 100 : $rate;
    }
}
