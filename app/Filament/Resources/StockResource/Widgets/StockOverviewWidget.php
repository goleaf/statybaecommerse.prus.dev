<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockResource\Widgets;

use App\Models\VariantInventory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StockOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalItems = VariantInventory::count();
        $lowStockItems = VariantInventory::lowStock()->count();
        $outOfStockItems = VariantInventory::outOfStock()->count();
        $needsReorderItems = VariantInventory::needsReorder()->count();
        $totalStockValue = VariantInventory::sum(DB::raw('stock * cost_per_unit'));
        $totalReservedValue = VariantInventory::sum(DB::raw('reserved * cost_per_unit'));

        return [
            Stat::make(__('inventory.total_items'), $totalItems)
                ->description(__('inventory.total_items_description'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make(__('inventory.low_stock_items'), $lowStockItems)
                ->description(__('inventory.low_stock_items_description'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockItems > 0 ? 'warning' : 'success'),

            Stat::make(__('inventory.out_of_stock_items'), $outOfStockItems)
                ->description(__('inventory.out_of_stock_items_description'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($outOfStockItems > 0 ? 'danger' : 'success'),

            Stat::make(__('inventory.needs_reorder_items'), $needsReorderItems)
                ->description(__('inventory.needs_reorder_items_description'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color($needsReorderItems > 0 ? 'info' : 'success'),

            Stat::make(__('inventory.total_stock_value'), '€'.number_format($totalStockValue, 2))
                ->description(__('inventory.total_stock_value_description'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),

            Stat::make(__('inventory.reserved_stock_value'), '€'.number_format($totalReservedValue, 2))
                ->description(__('inventory.reserved_stock_value_description'))
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color('warning'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
