<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
/**
 * PostStatsWidget
 * 
 * Filament v4 widget for PostStatsWidget dashboard display with real-time data and interactive features.
 * 
 */
final class PostStatsWidget extends BaseWidget
{
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        return [Stat::make(__('posts.widgets.total_posts'), Post::count())->description(__('posts.widgets.total_posts'))->descriptionIcon('heroicon-m-document-text')->color('primary'), Stat::make(__('posts.widgets.published_posts'), Post::where('status', 'published')->count())->description(__('posts.widgets.published_posts'))->descriptionIcon('heroicon-m-check-circle')->color('success'), Stat::make(__('posts.widgets.draft_posts'), Post::where('status', 'draft')->count())->description(__('posts.widgets.draft_posts'))->descriptionIcon('heroicon-m-pencil')->color('warning'), Stat::make(__('posts.widgets.featured_posts'), Post::where('featured', true)->count())->description(__('posts.widgets.featured_posts'))->descriptionIcon('heroicon-m-star')->color('info')];
    }
}