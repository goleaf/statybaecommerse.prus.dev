<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\Widgets;

use App\Models\Category;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * CategoryStatsWidget
 * 
 * Filament resource for admin panel management.
 */
class CategoryStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalCategories = Category::count();
        $enabledCategories = Category::where('is_enabled', true)->count();
        $featuredCategories = Category::where('is_featured', true)->count();
        $rootCategories = Category::whereNull('parent_id')->count();
        $categoriesWithProducts = Category::whereHas('products')->count();
        $categoriesWithoutProducts = Category::whereDoesntHave('products')->count();
        $visibleCategories = Category::where('is_visible', true)->count();

        return [
            Stat::make(__('admin.categories.stats.total_categories'), $totalCategories)
                ->description(__('admin.categories.stats.total_categories_description'))
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make(__('admin.categories.stats.enabled_categories'), $enabledCategories)
                ->description(__('admin.categories.stats.enabled_categories_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('admin.categories.stats.featured_categories'), $featuredCategories)
                ->description(__('admin.categories.stats.featured_categories_description'))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make(__('admin.categories.stats.root_categories'), $rootCategories)
                ->description(__('admin.categories.stats.root_categories_description'))
                ->descriptionIcon('heroicon-m-folder')
                ->color('info'),

            Stat::make(__('admin.categories.stats.categories_with_products'), $categoriesWithProducts)
                ->description(__('admin.categories.stats.categories_with_products_description'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),

            Stat::make(__('admin.categories.stats.categories_without_products'), $categoriesWithoutProducts)
                ->description(__('admin.categories.stats.categories_without_products_description'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make(__('admin.categories.stats.visible_categories'), $visibleCategories)
                ->description(__('admin.categories.stats.visible_categories_description'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('gray'),
        ];
    }
}
