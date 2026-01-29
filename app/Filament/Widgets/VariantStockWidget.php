<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\ProductVariant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class VariantStockWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        // The Eloquent sum helper already guarantees an integer return value, so we can
        // safely cast without an additional null coalesce that triggered phpstan errors.
        $totalStock = (int) ProductVariant::sum('stock_quantity');
        $availableStock = (int) ProductVariant::sum('available_quantity');
        $reservedStock = (int) ProductVariant::sum('reserved_quantity');
        $soldStock = (int) ProductVariant::sum('sold_quantity');

        $lowStockCount = ProductVariant::whereColumn('available_quantity', '<=', 'low_stock_threshold')
            ->where('track_inventory', true)
            ->count();

        $outOfStockCount = ProductVariant::where('available_quantity', '<=', 0)
            ->where('track_inventory', true)
            ->count();

        // DB::raw aggregates also default to zero, so we avoid redundant null coalescing here.
        $stockValue = (float) ProductVariant::sum(DB::raw('available_quantity * cost_price'));

        return [
            Stat::make(__('product.variants.stats.total_stock'), number_format($totalStock))
                ->description(__('product.variants.stats.all_variants_stock'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),
            Stat::make(__('product.variants.stats.available_stock'), number_format($availableStock))
                ->description(__('product.variants.stats.ready_for_sale'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make(__('product.variants.stats.reserved_stock'), number_format($reservedStock))
                ->description(__('product.variants.stats.pending_orders'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make(__('product.variants.stats.sold_stock'), number_format($soldStock))
                ->description(__('product.variants.stats.total_sold'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),
            Stat::make(__('product.variants.stats.low_stock_alerts'), $lowStockCount)
                ->description(__('product.variants.stats.need_restocking'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
            Stat::make(__('product.variants.stats.out_of_stock'), $outOfStockCount)
                ->description(__('product.variants.stats.unavailable_variants'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
            Stat::make(__('product.variants.stats.stock_value'), '€' . number_format($stockValue, 2))
                ->description(__('product.variants.stats.total_inventory_value'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('primary'),
        ];
    }
}
