<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockResource\Widgets;

use App\Models\Stock;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class StockOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalStock = Stock::sum('quantity');
        $totalValue = Stock::join('products', 'stocks.product_id', '=', 'products.id')
            ->join('prices', 'products.id', '=', 'prices.product_id')
            ->selectRaw('SUM(stocks.quantity * prices.price) as total_value')
            ->value('total_value') ?? 0;

        $lowStockItems = Stock::where('quantity', '<=', 10)->count();
        $outOfStockItems = Stock::where('quantity', '=', 0)->count();

        return [
            Stat::make(__('stocks.stats.total_quantity'), number_format($totalStock))
                ->description(__('stocks.stats.total_quantity_description'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make(__('stocks.stats.total_value'), '€'.number_format($totalValue, 2))
                ->description(__('stocks.stats.total_value_description'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),

            Stat::make(__('stocks.stats.low_stock_items'), $lowStockItems)
                ->description(__('stocks.stats.low_stock_items_description'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make(__('stocks.stats.out_of_stock_items'), $outOfStockItems)
                ->description(__('stocks.stats.out_of_stock_items_description'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
