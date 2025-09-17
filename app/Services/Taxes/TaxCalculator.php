<?php

declare (strict_types=1);
namespace App\Services\Taxes;

/**
 * TaxCalculator
 * 
 * Service class containing TaxCalculator business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 */
final class TaxCalculator
{
    /**
     * Handle compute functionality with proper error handling.
     * @param float $amount
     * @param string|null $zoneCode
     * @return float
     */
    public function compute(float $amount, ?string $zoneCode = null): float
    {
        $rate = (float) config('tax.default_rate', 0);
        $zones = (array) config('tax.zones', []);
        if ($zoneCode && isset($zones[$zoneCode])) {
            $rate = (float) $zones[$zoneCode];
        }
        if ($rate <= 0 || $amount <= 0) {
            return 0.0;
        }
        return round($amount * ($rate / 100), 2);
    }
}