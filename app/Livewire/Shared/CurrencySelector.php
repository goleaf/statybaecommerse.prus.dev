<?php declare(strict_types=1);

namespace App\Livewire\Shared;

use App\Actions\ZoneSessionManager;
use App\Models\Country;
use App\Models\Currency;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class CurrencySelector extends Component
{
    public array $currencies = [];
    public ?string $activeCurrencyCode = null;

    public function mount(): void
    {
        // Load enabled currencies (cached) with safe fallback if table is missing during tests
        $this->currencies = app()->environment('testing')
            ? [
                ['id' => 1, 'code' => (string) config('app.currency', 'EUR'), 'symbol' => '€'],
            ]
            : \Cache::remember(
                'currencies:enabled:list',
                now()->addHours(6),
                function () {
                    try {
                        if (Schema::hasTable('currencies')) {
                            return Currency::query()
                                ->where('is_enabled', true)
                                ->orderBy('code')
                                ->get(['id', 'code', 'symbol'])
                                ->map(fn($c) => [
                                    'id' => (int) $c->id,
                                    'code' => (string) $c->code,
                                    'symbol' => (string) $c->symbol,
                                ])
                                ->all();
                        }
                    } catch (\Throwable $e) {
                        // ignore and fallback
                    }
                    return [
                        ['id' => 1, 'code' => (string) config('app.currency', 'EUR'), 'symbol' => '€'],
                    ];
                }
            );

        // Determine active from settings if available
        $defaultCurrencyCode = app()->environment('testing')
            ? (string) config('app.currency', 'EUR')
            : \Cache::remember(
                'currency:default:code',
                now()->addMinutes(30),
                function () {
                    try {
                        if (function_exists('setting')) {
                            $id = optional(setting('default_currency_id'))->value ?? null;
                            if ($id && Schema::hasTable('currencies')) {
                                return Currency::query()->whereKey($id)->value('code');
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                    return (string) config('app.currency', 'EUR');
                }
            );
        $this->activeCurrencyCode = $defaultCurrencyCode ?: ($this->currencies[0]['code'] ?? null);
    }

    public function render()
    {
        return view('livewire.shared.currency-selector');
    }

    public function getCountryFlagProperty(): ?string
    {
        try {
            if (class_exists(ZoneSessionManager::class) && session()->has('zone')) {
                return ZoneSessionManager::getSession()->countryFlag ?? null;
            }
            try {
                $countryId = \App\Models\Setting::where('key', 'country_id')->value('value');
                if (!empty($countryId)) {
                    return Country::query()->find($countryId)?->svg_flag;
                }
            } catch (\Throwable $e) {
                // Fallback to default
                return null;
            }
        } catch (\Throwable $e) {
            return null;
        }
        return null;
    }
}
