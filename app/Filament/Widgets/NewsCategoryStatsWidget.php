<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\News;
use App\Models\NewsCategory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class NewsCategoryStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalCategories = NewsCategory::count();
        $visibleCategories = NewsCategory::where('is_visible', true)->count();
        $categoriesWithNews = NewsCategory::has('news')->count();
        $totalNews = News::count();
        $averageNewsPerCategory = $categoriesWithNews > 0 ? round($totalNews / $categoriesWithNews, 1) : 0;

        return [
            Stat::make(__('Total Categories'), $totalCategories)
                ->description(__('All news categories'))
                ->descriptionIcon('heroicon-m-tag')
                ->color('primary'),
            Stat::make(__('Visible Categories'), $visibleCategories)
                ->description(__('Publicly visible categories'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),
            Stat::make(__('Categories with News'), $categoriesWithNews)
                ->description(__('Categories containing news articles'))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
            Stat::make(__('Avg News per Category'), $averageNewsPerCategory)
                ->description(__('Average news articles per category'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),
        ];
    }
}
