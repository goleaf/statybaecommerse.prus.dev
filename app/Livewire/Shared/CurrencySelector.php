<?php

declare(strict_types=1);

namespace App\Livewire\Shared;

use App\Data\Currency\CurrencyOptionData;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Setting;
use App\Support\Cache\CacheKeys;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Throwable;

/**
 * CurrencySelector
 *
 * Livewire component for CurrencySelector with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property array<int, array{id:int, code:string, symbol:string}> $currencies
 * @property string|null                                           $activeCurrencyCode
 */
class CurrencySelector extends Component
{
    /**
     * @var array<int, array{id:int, code:string, symbol:string}>
     */
    public array $currencies = [];

    public ?string $activeCurrencyCode = null;

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        $this->currencies = $this->loadCurrencies();
        $this->activeCurrencyCode = $this->resolveActiveCurrencyCode($this->currencies);
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.shared.currency-selector');
    }

    /**
     * Expose the configured country flag, if any, for display next to the selector.
     */
    public function getCountryFlagProperty(): ?string
    {
        try {
            if (! Schema::hasTable('settings')) {
                return null;
            }

            $countryId = Setting::query()->where('key', 'country_id')->value('value');

            if ($countryId === null) {
                return null;
            }

            /** @var Country|null $country */
            $country = Country::query()->find($countryId);

            return $country?->svg_flag;
        } catch (Throwable $exception) {
            // In the event of a transient database error we silently fall back to no flag.
            return null;
        }
    }

    /**
     * Load the list of enabled currencies from cache or storage.
     *
     * @return array<int, array{id:int, code:string, symbol:string}>
     */
    private function loadCurrencies(): array
    {
        $defaultCode = (string) config('app.currency', 'EUR');

        if (app()->environment('testing')) {
            return [$this->defaultCurrencyEntry($defaultCode)->toArray()];
        }

        /** @var array<int, CurrencyOptionData> $currencies */
        $currencies = Cache::remember(
            CacheKeys::currencyEnabledList(),
            now()->addHours(6),
            function (): array {
                $fallbackCode = (string) config('app.currency', 'EUR');

                if (! Schema::hasTable('currencies')) {
                    return [$this->defaultCurrencyEntry($fallbackCode)];
                }

                return Currency::query()
                    ->where('is_enabled', true)
                    ->orderBy('code')
                    ->get(['id', 'code', 'symbol'])
                    ->map(static function (Currency $currency): CurrencyOptionData {
                        return new CurrencyOptionData(
                            id: (int) $currency->id,
                            code: (string) $currency->code,
                            symbol: (string) $currency->symbol,
                        );
                    })
                    ->values()
                    ->all();
            }
        );

        return array_map(
            static fn (CurrencyOptionData $option): array => $option->toArray(),
            $currencies
        );
    }

    /**
     * Determine which currency code should be considered active.
     *
     * @param array<int, array{id:int, code:string, symbol:string}> $currencies
     */
    private function resolveActiveCurrencyCode(array $currencies): ?string
    {
        $fallbackCode = $currencies[0]['code'] ?? (string) config('app.currency', 'EUR');

        if (app()->environment('testing')) {
            return $fallbackCode;
        }

        /** @var string $activeCode */
        $activeCode = Cache::remember(
            CacheKeys::currencyDefaultCode(),
            now()->addMinutes(30),
            function () use ($fallbackCode): string {
                if (! function_exists('setting')) {
                    return $fallbackCode;
                }

                try {
                    $setting = setting('default_currency_id');
                    $currencyId = is_object($setting) && property_exists($setting, 'value') ? $setting->value : $setting;

                    if ($currencyId && Schema::hasTable('currencies')) {
                        $code = Currency::query()->whereKey($currencyId)->value('code');

                        if (is_string($code) && $code !== '') {
                            return $code;
                        }
                    }
                } catch (Throwable $exception) {
                    // Ignore and fall back to the configured default code.
                }

                return $fallbackCode;
            }
        );

        return $activeCode;
    }

    /**
     * Build a deterministic currency entry structure for fallback usage.
     *
     * @return CurrencyOptionData
     */
    private function defaultCurrencyEntry(string $code): CurrencyOptionData
    {
        return new CurrencyOptionData(
            id: 1,
            code: $code,
            symbol: (string) config('app.currency_symbol', '€'),
        );
    }
}
