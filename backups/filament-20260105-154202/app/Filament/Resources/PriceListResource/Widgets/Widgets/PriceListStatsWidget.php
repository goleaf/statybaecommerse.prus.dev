<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceListResource\Widgets;

use App\Models\PriceList;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class PriceListStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalPriceLists = PriceList::count();
        $activePriceLists = PriceList::query()->active()->count();
        $enabledPriceLists = PriceList::query()->where('is_enabled', true)->count();
        $defaultPriceLists = PriceList::query()->where('is_default', true)->count();

        return [
            Stat::make(__('price_lists.stats.total_price_lists'), $totalPriceLists)
                ->description(__('price_lists.stats.total_price_lists_description'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('primary'),

            Stat::make(__('price_lists.stats.enabled_price_lists'), $enabledPriceLists)
                ->description(__('price_lists.stats.enabled_price_lists_description'))
                ->descriptionIcon('heroicon-m-bolt')
                ->color('success'),

            Stat::make(__('price_lists.stats.active_price_lists'), $activePriceLists)
                ->description(__('price_lists.stats.active_price_lists_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('price_lists.stats.enabled_price_lists'), $enabledPriceLists)
                ->description(__('price_lists.stats.enabled_price_lists_description'))
                ->descriptionIcon('heroicon-m-bolt')
                ->color('info'),

            Stat::make(__('price_lists.stats.default_price_lists'), $defaultPriceLists)
                ->description(__('price_lists.stats.default_price_lists_description'))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make(__('price_lists.stats.auto_apply_price_lists'), $autoApplyPriceLists)
                ->description(__('price_lists.stats.auto_apply_price_lists_description'))
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('info'),
        ];
    }
}
