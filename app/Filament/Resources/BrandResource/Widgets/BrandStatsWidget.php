<?php

declare(strict_types=1);

namespace App\Filament\Resources\BrandResource\Widgets;

use App\Models\Brand;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class BrandStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('admin.brands.stats.total_brands'), Brand::count())
                ->description(__('admin.brands.stats.total_brands_description'))
                ->descriptionIcon('heroicon-m-tag')
                ->color('primary'),

            Stat::make(__('admin.brands.stats.enabled_brands'), Brand::where('is_enabled', true)->count())
                ->description(__('admin.brands.stats.enabled_brands_description'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),

            Stat::make(__('admin.brands.stats.brands_with_products'), Brand::has('products')->count())
                ->description(__('admin.brands.stats.brands_with_products_description'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            Stat::make(__('admin.brands.stats.brands_with_translations'), Brand::has('translations')->count())
                ->description(__('admin.brands.stats.brands_with_translations_description'))
                ->descriptionIcon('heroicon-m-language')
                ->color('warning'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
