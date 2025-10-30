<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\CurrencyRateProvider;
use App\Models\Currency;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service responsible for synchronising stored exchange rates with an injected provider.
 */
final class CurrencyRateSyncService
{
    /**
     * @param CurrencyRateProvider $provider Service that knows how to fetch the most recent rate data.
     * @param string $defaultBaseCurrency Base currency code used when a record does not provide its own value.
     */
    public function __construct(
        private readonly CurrencyRateProvider $provider,
        private readonly string $defaultBaseCurrency = 'EUR',
    ) {
    }

    /**
     * Update a single currency model with the latest available rate.
     */
    public function sync(Currency $currency): ?float
    {
        // Determine the base currency from the record or fall back to the configured default.
        $baseCurrency = $currency->base_currency !== null && $currency->base_currency !== ''
            ? strtoupper((string) $currency->base_currency)
            : strtoupper($this->defaultBaseCurrency);

        // Attempt to fetch a fresh rate from the provider.
        $resolvedRate = $this->provider->getRate(strtoupper($currency->code), $baseCurrency);
        if ($resolvedRate === null) {
            return null;
        }

        // Persist the resolved rate to the model without triggering unnecessary attribute casting.
        $currency->forceFill(['exchange_rate' => $resolvedRate]);
        $currency->save();

        return $resolvedRate;
    }

    /**
     * Update a collection of currencies, returning a map of the successfully synchronised identifiers to their rates.
     *
     * @return array<int, float>
     */
    public function syncMany(Collection $currencies): array
    {
        $updated = [];

        foreach ($currencies as $currency) {
            if (! $currency instanceof Currency) {
                continue;
            }

            // Reuse the single synchronisation path so provider logic remains centralised.
            $rate = $this->sync($currency);
            if ($rate === null) {
                continue;
            }

            $updated[$currency->getKey()] = $rate;
        }

        return $updated;
    }
}
