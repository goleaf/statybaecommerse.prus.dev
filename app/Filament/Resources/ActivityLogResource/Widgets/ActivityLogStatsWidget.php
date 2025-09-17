<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogResource\Widgets;

use App\Models\ActivityLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class ActivityLogStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalActivities = ActivityLog::count();
        $todayActivities = ActivityLog::whereDate('created_at', today())->count();
        $thisWeekActivities = ActivityLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $uniqueUsers = ActivityLog::distinct('user_id')->count('user_id');

        return [
            Stat::make(__('activity_logs.stats.total_activities'), $totalActivities)
                ->description(__('activity_logs.stats.total_activities_description'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),

            Stat::make(__('activity_logs.stats.today_activities'), $todayActivities)
                ->description(__('activity_logs.stats.today_activities_description'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),

            Stat::make(__('activity_logs.stats.week_activities'), $thisWeekActivities)
                ->description(__('activity_logs.stats.week_activities_description'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make(__('activity_logs.stats.unique_users'), $uniqueUsers)
                ->description(__('activity_logs.stats.unique_users_description'))
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
        ];
    }
}
