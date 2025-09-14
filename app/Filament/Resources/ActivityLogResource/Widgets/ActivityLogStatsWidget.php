<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Activitylog\Models\Activity;

final /**
 * ActivityLogStatsWidget
 * 
 * Filament resource for admin panel management.
 */
class ActivityLogStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('admin.activity_logs.stats.total_activities'), Activity::count())
                ->description(__('admin.activity_logs.stats.all_time'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Stat::make(__('admin.activity_logs.stats.today_activities'), Activity::whereDate('created_at', today())->count())
                ->description(__('admin.activity_logs.stats.today'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),

            Stat::make(__('admin.activity_logs.stats.this_week_activities'), Activity::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])->count())
                ->description(__('admin.activity_logs.stats.this_week'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),

            Stat::make(__('admin.activity_logs.stats.this_month_activities'), Activity::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count())
                ->description(__('admin.activity_logs.stats.this_month'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
        ];
    }
}
