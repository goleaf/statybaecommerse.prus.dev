<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final /**
 * ProductInventoryWidget
 * 
 * Filament resource for admin panel management.
 */
class ProductInventoryWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $totalStock = Product::sum('stock_quantity');
        $totalValue = Product::sum(DB::raw('price * stock_quantity'));
        $averageStock = Product::avg('stock_quantity');
        $productsWithStock = Product::where('stock_quantity', '>', 0)->count();
        $productsOutOfStock = Product::where('stock_quantity', '<=', 0)->count();
        $productsLowStock = Product::whereRaw('stock_quantity <= low_stock_threshold')->count();
        $productsManageStock = Product::where('manage_stock', true)->count();

        return [
            Stat::make(__('translations.total_stock_quantity'), number_format($totalStock))
                ->description(__('translations.all_products_inventory'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make(__('translations.total_inventory_value'), '€' . number_format($totalValue, 2))
                ->description(__('translations.current_stock_value'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),

            Stat::make(__('translations.average_stock_per_product'), number_format($averageStock, 1))
                ->description(__('translations.mean_stock_quantity'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make(__('translations.products_with_stock'), $productsWithStock)
                ->description(__('translations.products_available'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('translations.products_out_of_stock'), $productsOutOfStock)
                ->description(__('translations.products_unavailable'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make(__('translations.products_low_stock'), $productsLowStock)
                ->description(__('translations.products_need_restocking'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make(__('translations.products_manage_stock'), $productsManageStock)
                ->description(__('translations.products_tracking_inventory'))
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('gray'),
        ];
    }
}
