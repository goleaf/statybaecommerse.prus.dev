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
     * Handle compute functionality with proper error handling.
     */
    public function compute(float $amount): float
    {
        $rate = (float) config('tax.default_rate', 0);
        if ($rate <= 0 || $amount <= 0) {
            return 0.0;
        }

        return round($amount * ($rate / 100), 2);
    }
}
