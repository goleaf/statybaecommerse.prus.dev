<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * ProductStatsWidget
 *
 * Comprehensive statistics widget for products showing key metrics and insights
 */
final class ProductStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalProducts = Product::count();
        $activeProducts = Product::where('is_visible', true)->count();
        $featuredProducts = Product::where('is_featured', true)->count();
        $lowStockProducts = Product::whereHas('inventory', function ($query) {
            $query->whereColumn('quantity', '<=', 'low_stock_threshold');
        })->count();

        $avgRating = Product::avg('average_rating') ?? 0;
        $totalReviews = Product::sum('reviews_count') ?? 0;
        $totalRevenue = Product::sum('revenue') ?? 0;

        return [
            Stat::make(__('products.widgets.total_products'), $totalProducts)
                ->description(__('products.widgets.total_products_description'))
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),
            Stat::make(__('products.widgets.active_products'), $activeProducts)
                ->description(__('products.widgets.active_products_description'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),
            Stat::make(__('products.widgets.featured_products'), $featuredProducts)
                ->description(__('products.widgets.featured_products_description'))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
            Stat::make(__('products.widgets.low_stock_products'), $lowStockProducts)
                ->description(__('products.widgets.low_stock_products_description'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockProducts > 0 ? 'danger' : 'success'),
            Stat::make(__('products.widgets.average_rating'), number_format($avgRating, 2))
                ->description(__('products.widgets.average_rating_description'))
                ->descriptionIcon('heroicon-m-star')
                ->color('info'),
            Stat::make(__('products.widgets.total_reviews'), number_format($totalReviews))
                ->description(__('products.widgets.total_reviews_description'))
                ->descriptionIcon('heroicon-m-chat-bubble-left-ellipsis')
                ->color('info'),
            Stat::make(__('products.widgets.total_revenue'), '€'.number_format($totalRevenue, 2))
                ->description(__('products.widgets.total_revenue_description'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),
        ];
    }
}
