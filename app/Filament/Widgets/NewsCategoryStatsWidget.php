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
            Stat::make(__('ui.total_categories'), $totalCategories)
                ->description(__('ui.all_news_categories'))
                ->descriptionIcon('heroicon-m-tag')
                ->color('primary'),
            Stat::make(__('ui.visible_categories'), $visibleCategories)
                ->description(__('ui.publicly_visible_categories'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),
            Stat::make(__('ui.categories_with_news'), $categoriesWithNews)
                ->description(__('ui.categories_containing_news_articles'))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
            Stat::make(__('ui.avg_news_per_category'), $averageNewsPerCategory)
                ->description(__('ui.average_news_articles_per_category'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),
        ];
    }
}
