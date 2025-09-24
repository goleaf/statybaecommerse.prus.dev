<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\NewsImage;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class NewsImageStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('admin.news_images.total_images'), NewsImage::count())
                ->description(__('admin.news_images.total_images_description'))
                ->descriptionIcon('heroicon-m-photo')
                ->color('primary'),
            Stat::make(__('admin.news_images.featured_images'), NewsImage::featured()->count())
                ->description(__('admin.news_images.featured_images_description'))
                ->descriptionIcon('heroicon-m-star')
                ->color('success'),
            Stat::make(__('admin.news_images.recent_uploads'), NewsImage::where('created_at', '>=', now()->subDays(7))->count())
                ->description(__('admin.news_images.recent_uploads_description'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
            Stat::make(__('admin.news_images.large_files'), NewsImage::where('file_size', '>', 1024 * 1024)->count())
                ->description(__('admin.news_images.large_files_description'))
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('warning'),
            Stat::make(__('admin.news_images.without_alt_text'), NewsImage::whereNull('alt_text')->orWhere('alt_text', '')->count())
                ->description(__('admin.news_images.without_alt_text_description'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
            Stat::make(__('admin.news_images.total_file_size'), $this->getTotalFileSize())
                ->description(__('admin.news_images.total_file_size_description'))
                ->descriptionIcon('heroicon-m-server')
                ->color('gray'),
        ];
    }

    private function getTotalFileSize(): string
    {
        $totalBytes = NewsImage::sum('file_size');

        if ($totalBytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $totalBytes;

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
