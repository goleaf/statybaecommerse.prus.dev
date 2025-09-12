<?php declare(strict_types=1);

namespace App\Filament\Resources\PriceListResource\Widgets;

use App\Models\PriceList;
use App\Models\PriceListItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class PriceListStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalPriceLists = PriceList::count();
        $activePriceLists = PriceList::where('is_enabled', true)->count();
        $defaultPriceLists = PriceList::where('is_default', true)->count();
        $totalItems = PriceListItem::count();

        $avgItemsPerList = $totalPriceLists > 0 ? round($totalItems / $totalPriceLists, 1) : 0;

        return [
            Stat::make(__('admin.price_lists.stats.total_price_lists'), $totalPriceLists)
                ->description(__('admin.price_lists.stats.total_price_lists_description'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make(__('admin.price_lists.stats.active_price_lists'), $activePriceLists)
                ->description(__('admin.price_lists.stats.active_price_lists_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('admin.price_lists.stats.default_price_lists'), $defaultPriceLists)
                ->description(__('admin.price_lists.stats.default_price_lists_description'))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make(__('admin.price_lists.stats.total_items'), $totalItems)
                ->description(__('admin.price_lists.stats.avg_items_per_list', ['count' => $avgItemsPerList]))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),
        ];
    }
}
