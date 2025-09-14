<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Campaign;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * CampaignStatsWidget
 * 
 * Filament widget for admin panel dashboard.
 */
class CampaignStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('campaigns.analytics.views'), Campaign::sum('total_views'))
                ->description(__('campaigns.analytics.total_views'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('primary'),
            Stat::make(__('campaigns.analytics.clicks'), Campaign::sum('total_clicks'))
                ->description(__('campaigns.analytics.total_clicks'))
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->color('success'),
            Stat::make(__('campaigns.analytics.conversions'), Campaign::sum('total_conversions'))
                ->description(__('campaigns.analytics.total_conversions'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),
            Stat::make(__('campaigns.analytics.revenue'), '€'.number_format(Campaign::sum('total_revenue'), 2))
                ->description(__('campaigns.analytics.total_revenue'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make(__('campaigns.status.active'), Campaign::where('status', 'active')->count())
                ->description(__('campaigns.analytics.active_campaigns'))
                ->descriptionIcon('heroicon-m-play')
                ->color('success'),
            Stat::make(__('campaigns.status.scheduled'), Campaign::where('status', 'scheduled')->count())
                ->description(__('campaigns.analytics.scheduled_campaigns'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
