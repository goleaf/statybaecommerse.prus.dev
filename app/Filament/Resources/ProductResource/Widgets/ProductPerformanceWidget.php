<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class ProductPerformanceWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalProducts = Product::count();
        $activeProducts = Product::where('is_active', true)->count();
        $featuredProducts = Product::where('is_featured', true)->count();
        $lowStockProducts = Product::whereHas('inventories', function ($query) {
            $query->where('quantity', '<=', 10);
        })->count();

        return [
            Stat::make(__('products.stats.total_products'), $totalProducts)
                ->description(__('products.stats.total_products_description'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make(__('products.stats.active_products'), $activeProducts)
                ->description(__('products.stats.active_products_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('products.stats.featured_products'), $featuredProducts)
                ->description(__('products.stats.featured_products_description'))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make(__('products.stats.low_stock_products'), $lowStockProducts)
                ->description(__('products.stats.low_stock_products_description'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
