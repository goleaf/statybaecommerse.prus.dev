<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\News;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
/**
 * NewsStatsWidget
 * 
 * Filament v4 widget for NewsStatsWidget dashboard display with real-time data and interactive features.
 * 
 */
final class NewsStatsWidget extends BaseWidget
{
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        $totalNews = News::count();
        $publishedNews = News::published()->count();
        $draftNews = News::where('is_visible', false)->orWhereNull('published_at')->count();
        $featuredNews = News::featured()->count();
        return [Stat::make(__('admin.news.widgets.total_news'), $totalNews)->description(__('admin.news.widgets.total_news_description'))->descriptionIcon('heroicon-m-newspaper')->color('primary'), Stat::make(__('admin.news.widgets.published_news'), $publishedNews)->description(__('admin.news.widgets.published_news_description'))->descriptionIcon('heroicon-m-check-circle')->color('success'), Stat::make(__('admin.news.widgets.draft_news'), $draftNews)->description(__('admin.news.widgets.draft_news_description'))->descriptionIcon('heroicon-m-document-text')->color('warning'), Stat::make(__('admin.news.widgets.featured_news'), $featuredNews)->description(__('admin.news.widgets.featured_news_description'))->descriptionIcon('heroicon-m-star')->color('info')];
    }
}