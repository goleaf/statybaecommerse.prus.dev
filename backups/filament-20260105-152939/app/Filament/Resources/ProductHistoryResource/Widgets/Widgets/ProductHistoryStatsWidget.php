<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductHistoryResource\Widgets;

use App\Models\ProductHistory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class ProductHistoryStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalHistory = ProductHistory::count();
        $todayHistory = ProductHistory::whereDate('created_at', today())->count();
        $thisWeekHistory = ProductHistory::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $priceChanges = ProductHistory::where('action', 'price_changed')->count();

        return [
            Stat::make(__('product_histories.stats.total_history'), $totalHistory)
                ->description(__('product_histories.stats.total_history_description'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),

            Stat::make(__('product_histories.stats.today_history'), $todayHistory)
                ->description(__('product_histories.stats.today_history_description'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),

            Stat::make(__('product_histories.stats.week_history'), $thisWeekHistory)
                ->description(__('product_histories.stats.week_history_description'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make(__('product_histories.stats.price_changes'), $priceChanges)
                ->description(__('product_histories.stats.price_changes_description'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('warning'),
        ];
    }
}
