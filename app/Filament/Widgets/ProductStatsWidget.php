<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

/**
 * ProductStatsWidget
 * 
 * Filament widget for admin panel dashboard.
 */
class ProductStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalProducts = Product::count();
        $publishedProducts = Product::where('status', 'published')
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->count();
        $draftProducts = Product::where('status', 'draft')->count();
        $featuredProducts = Product::where('is_featured', true)->count();
        $lowStockProducts = Product::whereRaw('stock_quantity <= low_stock_threshold')->count();
        $outOfStockProducts = Product::where('stock_quantity', '<=', 0)->count();

        $totalValue = Product::sum(DB::raw('price * stock_quantity'));
        $averagePrice = Product::avg('price');

        return [
            Stat::make(__('translations.total_products'), $totalProducts)
                ->description(__('translations.all_products_in_system'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make(__('translations.published_products'), $publishedProducts)
                ->description(__('translations.visible_to_customers'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('translations.draft_products'), $draftProducts)
                ->description(__('translations.not_yet_published'))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),

            Stat::make(__('translations.featured_products'), $featuredProducts)
                ->description(__('translations.highlighted_products'))
                ->descriptionIcon('heroicon-m-star')
                ->color('info'),

            Stat::make(__('translations.low_stock_products'), $lowStockProducts)
                ->description(__('translations.need_restocking'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make(__('translations.out_of_stock_products'), $outOfStockProducts)
                ->description(__('translations.no_inventory'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make(__('translations.total_inventory_value'), '€'.number_format($totalValue, 2))
                ->description(__('translations.current_stock_value'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),

            Stat::make(__('translations.average_product_price'), '€'.number_format($averagePrice, 2))
                ->description(__('translations.mean_price_across_products'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
        ];
    }
}
