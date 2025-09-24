<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\ProductVariant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class VariantPriceWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $averagePrice = (float) (ProductVariant::avg('price') ?? 0);
        $highestPrice = (float) (ProductVariant::max('price') ?? 0);
        $lowestPrice = (float) (ProductVariant::min('price') ?? 0);

        $onSaleCount = ProductVariant::where('is_on_sale', true)->count();
        $totalRevenue = (float) (ProductVariant::sum(DB::raw('sold_quantity * price')) ?? 0);

        $averageDiscount = (float) (ProductVariant::where('is_on_sale', true)
            ->whereNotNull('compare_price')
            ->avg(DB::raw('((compare_price - price) / compare_price) * 100')) ?? 0);

        $priceRanges = [
            'under_50' => ProductVariant::where('price', '<', 50)->count(),
            '50_100' => ProductVariant::whereBetween('price', [50, 100])->count(),
            '100_200' => ProductVariant::whereBetween('price', [100, 200])->count(),
            'over_200' => ProductVariant::where('price', '>', 200)->count(),
        ];

        return [
            Stat::make(__('product_variants.stats.average_price'), '€'.number_format($averagePrice, 2))
                ->description(__('product_variants.stats.all_variants'))
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
            Stat::make(__('product_variants.stats.highest_price'), '€'.number_format($highestPrice, 2))
                ->description(__('product_variants.stats.most_expensive'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make(__('product_variants.stats.lowest_price'), '€'.number_format($lowestPrice, 2))
                ->description(__('product_variants.stats.most_affordable'))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('warning'),
            Stat::make(__('product_variants.stats.on_sale'), $onSaleCount)
                ->description(__('product_variants.stats.discounted_variants'))
                ->descriptionIcon('heroicon-m-tag')
                ->color('danger'),
            Stat::make(__('product_variants.stats.average_discount'), number_format($averageDiscount, 1).'%')
                ->description(__('product_variants.stats.sale_discount'))
                ->descriptionIcon('heroicon-m-percent')
                ->color('success'),
            Stat::make(__('product_variants.stats.total_revenue'), '€'.number_format($totalRevenue, 2))
                ->description(__('product_variants.stats.from_sales'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('primary'),
            Stat::make(__('product_variants.stats.price_range_under_50'), $priceRanges['under_50'])
                ->description(__('product_variants.stats.under_50_euros'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('info'),
            Stat::make(__('product_variants.stats.price_range_50_100'), $priceRanges['50_100'])
                ->description(__('product_variants.stats.between_50_100_euros'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),
        ];
    }
}
