<?php declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;

final class ActivityLogStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        return [
            Stat::make(__('admin.activity_logs.stats.total_activities'), Activity::count())
                ->description(__('admin.activity_logs.stats.all_time'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Stat::make(__('admin.activity_logs.stats.today_activities'), Activity::whereDate('created_at', $today)->count())
                ->description(__('admin.activity_logs.stats.today'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),

            Stat::make(__('admin.activity_logs.stats.this_week_activities'), Activity::where('created_at', '>=', $thisWeek)->count())
                ->description(__('admin.activity_logs.stats.this_week'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make(__('admin.activity_logs.stats.this_month_activities'), Activity::where('created_at', '>=', $thisMonth)->count())
                ->description(__('admin.activity_logs.stats.this_month'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),

            Stat::make(__('admin.activity_logs.stats.most_active_user'), $this->getMostActiveUser())
                ->description(__('admin.activity_logs.stats.most_active_user_desc'))
                ->descriptionIcon('heroicon-m-user')
                ->color('secondary'),

            Stat::make(__('admin.activity_logs.stats.most_common_event'), $this->getMostCommonEvent())
                ->description(__('admin.activity_logs.stats.most_common_event_desc'))
                ->descriptionIcon('heroicon-m-bolt')
                ->color('danger'),
        ];
    }

    private function getMostActiveUser(): string
    {
        $user = Activity::select('causer_id', DB::raw('count(*) as activity_count'))
            ->whereNotNull('causer_id')
            ->with('causer:id,name')
            ->groupBy('causer_id')
            ->orderBy('activity_count', 'desc')
            ->first();

        if (!$user || !$user->causer) {
            return __('admin.activity_logs.stats.no_user_activity');
        }

        return $user->causer->name . ' (' . $user->activity_count . ')';
    }

    private function getMostCommonEvent(): string
    {
        $event = Activity::select('event', DB::raw('count(*) as event_count'))
            ->whereNotNull('event')
            ->groupBy('event')
            ->orderBy('event_count', 'desc')
            ->first();

        if (!$event) {
            return __('admin.activity_logs.stats.no_events');
        }

        return __('admin.activity_logs.events.' . $event->event, [], $event->event) . ' (' . $event->event_count . ')';
    }
}
