<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Widgets;

use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class PostEngagementWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $totalViews = Post::sum('views_count');
        $totalLikes = Post::sum('likes_count');
        $totalComments = Post::sum('comments_count');
        $averageViews = Post::avg('views_count');
        $averageLikes = Post::avg('likes_count');
        $averageComments = Post::avg('comments_count');
        $totalEngagement = $totalLikes + $totalComments;
        $averageEngagementRate = Post::where('views_count', '>', 0)
            ->avg(DB::raw('((likes_count + comments_count) / views_count) * 100'));

        return [
            Stat::make(__('posts.engagement.total_views'), number_format($totalViews))
                ->description(__('posts.engagement.total_views_description'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('primary'),

            Stat::make(__('posts.engagement.total_likes'), number_format($totalLikes))
                ->description(__('posts.engagement.total_likes_description'))
                ->descriptionIcon('heroicon-m-heart')
                ->color('danger'),

            Stat::make(__('posts.engagement.total_comments'), number_format($totalComments))
                ->description(__('posts.engagement.total_comments_description'))
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('info'),

            Stat::make(__('posts.engagement.total_engagement'), number_format($totalEngagement))
                ->description(__('posts.engagement.total_engagement_description'))
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make(__('posts.engagement.average_views'), number_format($averageViews, 0))
                ->description(__('posts.engagement.average_views_description'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('gray'),

            Stat::make(__('posts.engagement.average_likes'), number_format($averageLikes, 1))
                ->description(__('posts.engagement.average_likes_description'))
                ->descriptionIcon('heroicon-m-heart')
                ->color('warning'),

            Stat::make(__('posts.engagement.average_comments'), number_format($averageComments, 1))
                ->description(__('posts.engagement.average_comments_description'))
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('info'),

            Stat::make(__('posts.engagement.average_engagement_rate'), number_format($averageEngagementRate, 1) . '%')
                ->description(__('posts.engagement.average_engagement_rate_description'))
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color('success'),
        ];
    }
}
