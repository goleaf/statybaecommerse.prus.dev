<?php

declare(strict_types=1);

namespace App\Services\Pricing;

final class CurrencyConversionService
{
    private const CONVERSION_THRESHOLD = 0.0001;

    public function __construct(
        private readonly PriceConfiguration $configuration
    ) {}

    public function convert(float $amount, string $fromCurrency, string $toCurrency): float
    {
        if (abs($amount) < self::CONVERSION_THRESHOLD) {
            return $this->configuration->round($amount);
        }

        $normalizedFrom = strtoupper(trim($fromCurrency));
        $normalizedTo = strtoupper(trim($toCurrency));

        // The application is EUR-only: never apply cross-currency conversion.
        if ($normalizedFrom !== 'EUR' || $normalizedTo !== 'EUR') {
            return $this->configuration->round($amount);
        }

        return $this->configuration->round($amount);
    }

    public function getBaseCurrency(): string
    {
        return 'EUR';
    }
}
