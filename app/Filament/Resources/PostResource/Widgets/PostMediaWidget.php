<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Widgets;

use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final /**
 * PostMediaWidget
 * 
 * Filament resource for admin panel management.
 */
class PostMediaWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $postsWithMedia = Post::whereHas('media')->count();
        $postsWithoutMedia = Post::whereDoesntHave('media')->count();
        $postsWithFeaturedImage = Post::whereHas('media', function ($query) {
            $query->where('collection_name', 'images');
        })->count();
        $postsWithGallery = Post::whereHas('media', function ($query) {
            $query->where('collection_name', 'gallery');
        })->count();
        $totalMediaFiles = DB::table('media')
            ->where('model_type', Post::class)
            ->count();
        $averageMediaPerPost = $postsWithMedia > 0 ? round($totalMediaFiles / $postsWithMedia, 1) : 0;

        return [
            Stat::make(__('posts.media.posts_with_media'), $postsWithMedia)
                ->description(__('posts.media.posts_with_media_description'))
                ->descriptionIcon('heroicon-m-photo')
                ->color('success'),

            Stat::make(__('posts.media.posts_without_media'), $postsWithoutMedia)
                ->description(__('posts.media.posts_without_media_description'))
                ->descriptionIcon('heroicon-m-photo')
                ->color('gray'),

            Stat::make(__('posts.media.posts_with_featured_image'), $postsWithFeaturedImage)
                ->description(__('posts.media.posts_with_featured_image_description'))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make(__('posts.media.posts_with_gallery'), $postsWithGallery)
                ->description(__('posts.media.posts_with_gallery_description'))
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('info'),

            Stat::make(__('posts.media.total_media_files'), $totalMediaFiles)
                ->description(__('posts.media.total_media_files_description'))
                ->descriptionIcon('heroicon-m-document')
                ->color('primary'),

            Stat::make(__('posts.media.average_media_per_post'), $averageMediaPerPost)
                ->description(__('posts.media.average_media_per_post_description'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('gray'),
        ];
    }
}
