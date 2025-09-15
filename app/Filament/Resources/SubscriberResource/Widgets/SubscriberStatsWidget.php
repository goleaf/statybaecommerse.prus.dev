<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubscriberResource\Widgets;

use App\Models\Subscriber;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class SubscriberStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalSubscribers = Subscriber::count();
        $activeSubscribers = Subscriber::where('is_active', true)->count();
        $verifiedSubscribers = Subscriber::where('is_verified', true)->count();
        $todaySubscribers = Subscriber::whereDate('created_at', today())->count();

        return [
            Stat::make(__('subscribers.stats.total_subscribers'), $totalSubscribers)
                ->description(__('subscribers.stats.total_subscribers_description'))
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make(__('subscribers.stats.active_subscribers'), $activeSubscribers)
                ->description(__('subscribers.stats.active_subscribers_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('subscribers.stats.verified_subscribers'), $verifiedSubscribers)
                ->description(__('subscribers.stats.verified_subscribers_description'))
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('info'),

            Stat::make(__('subscribers.stats.today_subscribers'), $todaySubscribers)
                ->description(__('subscribers.stats.today_subscribers_description'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),
        ];
    }
}
