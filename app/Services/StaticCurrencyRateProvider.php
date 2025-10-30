<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\CurrencyRateProvider;

/**
 * Lightweight provider that resolves rates from the local configuration array.
 */
final class StaticCurrencyRateProvider implements CurrencyRateProvider
{
    /**
     * @param array<string, float|int|string> $configuredRates Optional set of preloaded rates for dependency injection.
     */
    public function __construct(private readonly array $configuredRates = [])
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getRate(string $targetCurrency, string $baseCurrency): ?float
    {
        // Merge any injected configuration with the config helper at runtime so tests can override values.
        $rates = array_change_key_case(
            array_merge($this->configuredRates, config('currency.static_rates', [])),
            CASE_UPPER,
        );

        $target = strtoupper($targetCurrency);
        $base = strtoupper($baseCurrency);

        // When the requested currency matches the base we always return the neutral multiplier.
        if ($target === $base) {
            return 1.0;
        }

        if (! array_key_exists($target, $rates)) {
            return null;
        }

        $resolved = $rates[$target];

        // Normalise numeric strings to floats for downstream persistence.
        if (is_string($resolved)) {
            $resolved = (float) $resolved;
        }

        return is_numeric($resolved) ? (float) $resolved : null;
    }
}
