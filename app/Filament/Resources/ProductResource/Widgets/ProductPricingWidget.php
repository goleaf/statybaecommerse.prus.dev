<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final /**
 * ProductPricingWidget
 * 
 * Filament resource for admin panel management.
 */
class ProductPricingWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $averagePrice = Product::avg('price');
        $highestPrice = Product::max('price');
        $lowestPrice = Product::min('price');
        $productsOnSale = Product::whereNotNull('sale_price')->count();
        $averageSalePrice = Product::whereNotNull('sale_price')->avg('sale_price');
        $totalInventoryValue = Product::sum(DB::raw('price * stock_quantity'));
        $averageMargin = Product::whereNotNull('cost_price')->avg(DB::raw('price - cost_price'));

        return [
            Stat::make(__('translations.average_product_price'), '€' . number_format($averagePrice, 2))
                ->description(__('translations.mean_price_across_products'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            Stat::make(__('translations.highest_product_price'), '€' . number_format($highestPrice, 2))
                ->description(__('translations.most_expensive_product'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make(__('translations.lowest_product_price'), '€' . number_format($lowestPrice, 2))
                ->description(__('translations.least_expensive_product'))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('info'),

            Stat::make(__('translations.products_on_sale'), $productsOnSale)
                ->description(__('translations.products_with_discount'))
                ->descriptionIcon('heroicon-m-tag')
                ->color('warning'),

            Stat::make(__('translations.average_sale_price'), '€' . number_format($averageSalePrice, 2))
                ->description(__('translations.mean_sale_price'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('gray'),

            Stat::make(__('translations.total_inventory_value'), '€' . number_format($totalInventoryValue, 2))
                ->description(__('translations.current_stock_value'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make(__('translations.average_margin'), '€' . number_format($averageMargin, 2))
                ->description(__('translations.mean_profit_margin'))
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color('info'),
        ];
    }
}
