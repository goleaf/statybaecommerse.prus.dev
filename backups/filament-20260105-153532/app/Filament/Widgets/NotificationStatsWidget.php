<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Notification;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class NotificationStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('notifications.stats.total_notifications'), Notification::count())
                ->description(__('notifications.stats.all_time'))
                ->descriptionIcon('heroicon-m-bell')
                ->color('primary'),
            Stat::make(__('notifications.stats.unread_notifications'), Notification::unread()->count())
                ->description(__('notifications.stats.requires_attention'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
            Stat::make(__('notifications.stats.urgent_notifications'), Notification::urgent()->count())
                ->description(__('notifications.stats.high_priority'))
                ->descriptionIcon('heroicon-m-fire')
                ->color('danger'),
            Stat::make(__('notifications.stats.today_notifications'), Notification::whereDate('created_at', today())->count())
                ->description(__('notifications.stats.created_today'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),
        ];
    }
}
