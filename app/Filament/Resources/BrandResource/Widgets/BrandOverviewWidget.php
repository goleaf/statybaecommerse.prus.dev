<?php

declare(strict_types=1);

namespace App\Filament\Resources\BrandResource\Widgets;

use App\Models\Brand;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class BrandOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalBrands = Brand::count();
        $activeBrands = Brand::where('is_active', true)->count();
        $featuredBrands = Brand::where('is_featured', true)->count();
        $brandsWithProducts = Brand::whereHas('products')->count();

        return [
            Stat::make(__('brands.stats.total_brands'), $totalBrands)
                ->description(__('brands.stats.total_brands_description'))
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary'),

            Stat::make(__('brands.stats.active_brands'), $activeBrands)
                ->description(__('brands.stats.active_brands_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('brands.stats.featured_brands'), $featuredBrands)
                ->description(__('brands.stats.featured_brands_description'))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make(__('brands.stats.brands_with_products'), $brandsWithProducts)
                ->description(__('brands.stats.brands_with_products_description'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),
        ];
    }
}
