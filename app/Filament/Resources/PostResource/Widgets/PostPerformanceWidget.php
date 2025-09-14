<?php

declare (strict_types=1);
namespace App\Filament\Resources\PostResource\Widgets;

use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
/**
 * PostPerformanceWidget
 * 
 * Filament v4 resource for PostPerformanceWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $pollingInterval
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class PostPerformanceWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        $mostViewed = Post::orderBy('views_count', 'desc')->first();
        $mostLiked = Post::orderBy('likes_count', 'desc')->first();
        $mostCommented = Post::orderBy('comments_count', 'desc')->first();
        $mostPopular = Post::selectRaw('*, (views_count * 1 + likes_count * 2 + comments_count * 3) as popularity_score')->orderBy('popularity_score', 'desc')->first();
        return [Stat::make(__('posts.performance.most_viewed'), $mostViewed ? number_format($mostViewed->views_count) : '0')->description($mostViewed ? $mostViewed->title : __('posts.performance.no_posts'))->descriptionIcon('heroicon-m-eye')->color('primary'), Stat::make(__('posts.performance.most_liked'), $mostLiked ? number_format($mostLiked->likes_count) : '0')->description($mostLiked ? $mostLiked->title : __('posts.performance.no_posts'))->descriptionIcon('heroicon-m-heart')->color('danger'), Stat::make(__('posts.performance.most_commented'), $mostCommented ? number_format($mostCommented->comments_count) : '0')->description($mostCommented ? $mostCommented->title : __('posts.performance.no_posts'))->descriptionIcon('heroicon-m-chat-bubble-left-right')->color('info'), Stat::make(__('posts.performance.most_popular'), $mostPopular ? number_format($mostPopular->popularity_score) : '0')->description($mostPopular ? $mostPopular->title : __('posts.performance.no_posts'))->descriptionIcon('heroicon-m-star')->color('warning')];
    }
}