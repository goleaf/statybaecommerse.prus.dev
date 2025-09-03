<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\Images\ImageStatsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class ProductImageStatsWidget extends BaseWidget
{

    protected function getStats(): array
    {
        $statsService = app(ImageStatsService::class);
        $stats = $statsService->getImageStatistics();
        $sizeStats = $statsService->getImageSizeBreakdown();

        return [
            Stat::make(__('translations.total_images'), $stats['total_images'])
                ->description(__('translations.images_in_system'))
                ->descriptionIcon('heroicon-m-photo')
                ->color('primary')
                ->chart([10, 15, 8, 20, 25, 18, 30]),

            Stat::make(__('translations.generated_images'), $stats['generated_images'])
                ->description($stats['total_images'] > 0 ? round(($stats['generated_images'] / $stats['total_images']) * 100, 1) . '% ' . __('translations.of_total') : '0%')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('success')
                ->chart([5, 8, 12, 15, 18, 22, 25]),

            Stat::make(__('translations.webp_format'), $stats['webp_images'])
                ->description($stats['webp_percentage'] . '% WebP ' . __('translations.optimized'))
                ->descriptionIcon('heroicon-m-bolt')
                ->color('warning')
                ->chart([20, 25, 30, 35, 40, 45, 50]),

            Stat::make(__('translations.products') . ' ' . __('translations.coverage'), $stats['products_with_images'])
                ->description($stats['coverage_percentage'] . '% ' . __('translations.have_images'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('info')
                ->chart([30, 35, 40, 45, 48, 50, 52]),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }

    public static function getSort(): int
    {
        return 2;
    }
}
