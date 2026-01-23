<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Models\Currency;

final class CurrencyConversionService
{
    private const CONVERSION_THRESHOLD = 0.0001;

    /**
     * @var array<string, float|null>
     */
    private array $currencyRateCache = [];

    public function __construct(
        private readonly PriceConfiguration $configuration
    ) {}

    public function convert(float $amount, string $fromCurrency, string $toCurrency): float
    {
        if (abs($amount) < self::CONVERSION_THRESHOLD || $fromCurrency === $toCurrency) {
            return $this->configuration->round($amount);
        }

        $fromRate = $this->getCurrencyRate($fromCurrency);
        $toRate = $this->getCurrencyRate($toCurrency);

        if ($fromRate === null || $toRate === null || $fromRate <= 0.0 || $toRate <= 0.0) {
            return $this->configuration->round($amount);
        }

        // Normalize to base currency, then convert to target
        $amountInBase = $amount / $fromRate;
        $converted = $amountInBase * $toRate;

        return $this->configuration->round($converted);
    }

    public function getBaseCurrency(): string
    {
        $configured = $this->configuration->currency();
        if (is_string($configured) && $configured !== '') {
            return strtoupper($configured);
        }

        $default = Currency::query()->where('is_default', true)->value('code');

        return is_string($default) && $default !== '' ? strtoupper($default) : 'EUR';
    }

    private function getCurrencyRate(string $code): ?float
    {
        $code = strtoupper($code);

        if (array_key_exists($code, $this->currencyRateCache)) {
            return $this->currencyRateCache[$code];
        }

        $rate = Currency::query()->where('code', $code)->value('exchange_rate');

        if ($rate === null) {
            return $this->currencyRateCache[$code] = null;
        }

        return $this->currencyRateCache[$code] = (float) $rate;
    }
}
