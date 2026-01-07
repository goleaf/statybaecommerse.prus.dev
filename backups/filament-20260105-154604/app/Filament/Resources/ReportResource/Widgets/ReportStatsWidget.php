<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReportResource\Widgets;

use App\Models\Report;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class ReportStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalReports = Report::count();
        $activeReports = Report::where('is_active', true)->count();
        $salesReports = Report::where('type', 'sales')->count();
        $inventoryReports = Report::where('type', 'inventory')->count();

        return [
            Stat::make(__('reports.stats.total_reports'), $totalReports)
                ->description(__('reports.stats.total_reports_description'))
                ->descriptionIcon('heroicon-m-document-report')
                ->color('primary'),

            Stat::make(__('reports.stats.active_reports'), $activeReports)
                ->description(__('reports.stats.active_reports_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('reports.stats.sales_reports'), $salesReports)
                ->description(__('reports.stats.sales_reports_description'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('info'),

            Stat::make(__('reports.stats.inventory_reports'), $inventoryReports)
                ->description(__('reports.stats.inventory_reports_description'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('warning'),
        ];
    }
}
