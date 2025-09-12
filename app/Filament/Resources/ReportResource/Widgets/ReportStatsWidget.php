<?php declare(strict_types=1);

namespace App\Filament\Resources\ReportResource\Widgets;

use App\Models\Report;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class ReportStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalReports = Report::count();
        $activeReports = Report::where('is_active', true)->count();
        $publicReports = Report::where('is_public', true)->count();
        $scheduledReports = Report::where('is_scheduled', true)->count();
        $totalViews = Report::sum('view_count');
        $totalDownloads = Report::sum('download_count');

        return [
            Stat::make(__('admin.reports.stats.total_reports'), $totalReports)
                ->description(__('admin.reports.stats.total_reports_description'))
                ->descriptionIcon('heroicon-m-document-chart-bar')
                ->color('primary'),

            Stat::make(__('admin.reports.stats.active_reports'), $activeReports)
                ->description(__('admin.reports.stats.active_reports_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('admin.reports.stats.public_reports'), $publicReports)
                ->description(__('admin.reports.stats.public_reports_description'))
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('info'),

            Stat::make(__('admin.reports.stats.scheduled_reports'), $scheduledReports)
                ->description(__('admin.reports.stats.scheduled_reports_description'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('admin.reports.stats.total_views'), number_format($totalViews))
                ->description(__('admin.reports.stats.total_views_description'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('secondary'),

            Stat::make(__('admin.reports.stats.total_downloads'), number_format($totalDownloads))
                ->description(__('admin.reports.stats.total_downloads_description'))
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('gray'),
        ];
    }
}
