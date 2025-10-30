<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Contract for services capable of resolving exchange rates between the base currency and a target code.
 */
interface CurrencyRateProvider
{
    /**
     * Resolve the latest exchange rate for the provided currency code relative to the supplied base currency.
     */
    public function getRate(string $targetCurrency, string $baseCurrency): ?float;
}
