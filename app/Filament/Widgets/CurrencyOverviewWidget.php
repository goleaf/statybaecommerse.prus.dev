<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Currency;
use App\Models\Zone;
use App\Models\Price;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class CurrencyOverviewWidget extends BaseWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalCurrencies = Currency::count();
        $activeCurrencies = Currency::where('is_enabled', true)->count();
        $defaultCurrency = Currency::where('is_default', true)->first();
        $currenciesWithZones = Currency::whereHas('zones')->count();
        $currenciesWithPrices = Currency::whereHas('prices')->count();
        $averageExchangeRate = Currency::avg('exchange_rate') ?? 0;
        $thisMonthCurrencies = Currency::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            Stat::make(__('currency_title'), $totalCurrencies)
                ->description(__('admin.common.all'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('primary'),
            
            Stat::make(__('currency_is_enabled'), $activeCurrencies)
                ->description(__('admin.common.enabled'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make(__('currency_is_default'), $defaultCurrency ? $defaultCurrency->name : __('admin.common.none'))
                ->description(__('currency_help.is_default'))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
            
            Stat::make(__('currency_zones_count'), $currenciesWithZones)
                ->description(__('currency_tabs.with_zones'))
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('info'),
            
            Stat::make(__('currency_prices_count'), $currenciesWithPrices)
                ->description(__('currency_tabs.with_prices'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
            
            Stat::make(__('currency_exchange_rate'), number_format($averageExchangeRate, 4))
                ->description(__('currency_help.exchange_rate'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('gray'),
            
            Stat::make(__('analytics_this_month'), $thisMonthCurrencies)
                ->description(__('analytics_created'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
        ];
    }
}