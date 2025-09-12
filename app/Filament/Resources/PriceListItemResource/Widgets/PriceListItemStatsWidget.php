<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceListItemResource\Widgets;

use App\Models\PriceListItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class PriceListItemStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalItems = PriceListItem::count();
        $activeItems = PriceListItem::where('is_active', true)->count();
        $itemsWithDiscount = PriceListItem::whereNotNull('compare_amount')
            ->whereColumn('compare_amount', '>', 'net_amount')
            ->count();

        $avgDiscount = PriceListItem::whereNotNull('compare_amount')
            ->whereColumn('compare_amount', '>', 'net_amount')
            ->selectRaw('AVG(((compare_amount - net_amount) / compare_amount) * 100) as avg_discount')
            ->value('avg_discount');

        return [
            Stat::make(__('admin.price_list_items.stats.total_items'), $totalItems)
                ->description(__('admin.price_list_items.stats.total_items_description'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Stat::make(__('admin.price_list_items.stats.active_items'), $activeItems)
                ->description(__('admin.price_list_items.stats.active_items_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('admin.price_list_items.stats.items_with_discount'), $itemsWithDiscount)
                ->description(__('admin.price_list_items.stats.items_with_discount_description'))
                ->descriptionIcon('heroicon-m-tag')
                ->color('warning'),

            Stat::make(__('admin.price_list_items.stats.average_discount'), $avgDiscount ? round($avgDiscount, 1).'%' : '0%')
                ->description(__('admin.price_list_items.stats.average_discount_description'))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('info'),
        ];
    }
}
