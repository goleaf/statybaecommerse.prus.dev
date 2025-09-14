<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Models\Product;
use App\Models\OrderItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final /**
 * ProductPerformanceWidget
 * 
 * Filament resource for admin panel management.
 */
class ProductPerformanceWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        // Get top selling products
        $topSellingProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->with('product:id,name')
            ->groupBy('product_id')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        // Get revenue by product
        $topRevenueProducts = OrderItem::select('product_id', DB::raw('SUM(quantity * price) as total_revenue'))
            ->with('product:id,name')
            ->groupBy('product_id')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();

        // Get most viewed products (if you have a views tracking system)
        $mostViewedProducts = Product::orderBy('views_count', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'views_count']);

        // Get products with best ratings
        $bestRatedProducts = Product::withAvg('reviews', 'rating')
            ->having('reviews_avg_rating', '>', 0)
            ->orderBy('reviews_avg_rating', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'reviews_avg_rating']);

        return [
            Stat::make(__('translations.top_selling_product'), $topSellingProducts->first()?->product?->name ?? __('translations.no_data'))
                ->description(__('translations.most_sold_item'))
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success'),

            Stat::make(__('translations.top_revenue_product'), $topRevenueProducts->first()?->product?->name ?? __('translations.no_data'))
                ->description(__('translations.highest_revenue_item'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('primary'),

            Stat::make(__('translations.most_viewed_product'), $mostViewedProducts->first()?->name ?? __('translations.no_data'))
                ->description(__('translations.highest_views'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),

            Stat::make(__('translations.best_rated_product'), $bestRatedProducts->first()?->name ?? __('translations.no_data'))
                ->description(__('translations.highest_rating'))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
        ];
    }
}
