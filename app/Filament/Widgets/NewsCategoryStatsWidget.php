<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\News;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class NewsCategoryStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // News category functionality has been removed
        $totalCategories = 0;
        $visibleCategories = 0;
        $categoriesWithNews = 0;
        $totalNews = News::count();
        $averageNewsPerCategory = 0;

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
