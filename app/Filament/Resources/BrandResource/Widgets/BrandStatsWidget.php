<?php

declare (strict_types=1);
namespace App\Filament\Resources\BrandResource\Widgets;

use App\Models\Brand;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
/**
 * BrandStatsWidget
 * 
 * Filament v4 resource for BrandStatsWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class BrandStatsWidget extends BaseWidget
{
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        return [Stat::make(__('admin.brands.stats.total_brands'), Brand::count())->description(__('admin.brands.stats.total_brands_description'))->descriptionIcon('heroicon-m-tag')->color('primary'), Stat::make(__('admin.brands.stats.enabled_brands'), Brand::where('is_enabled', true)->count())->description(__('admin.brands.stats.enabled_brands_description'))->descriptionIcon('heroicon-m-eye')->color('success'), Stat::make(__('admin.brands.stats.brands_with_products'), Brand::has('products')->count())->description(__('admin.brands.stats.brands_with_products_description'))->descriptionIcon('heroicon-m-shopping-bag')->color('info'), Stat::make(__('admin.brands.stats.brands_with_translations'), Brand::has('translations')->count())->description(__('admin.brands.stats.brands_with_translations_description'))->descriptionIcon('heroicon-m-language')->color('warning')];
    }
    /**
     * Handle getColumns functionality with proper error handling.
     * @return int
     */
    protected function getColumns(): int
    {
        return 4;
    }
}