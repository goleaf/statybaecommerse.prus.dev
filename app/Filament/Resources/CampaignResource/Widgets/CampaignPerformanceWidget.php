<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignResource\Widgets;

use App\Models\Campaign;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class CampaignPerformanceWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalCampaigns = Campaign::count();
        $activeCampaigns = Campaign::where('is_active', true)->count();
        $scheduledCampaigns = Campaign::where('is_scheduled', true)->count();
        $runningCampaigns = Campaign::where('status', 'running')->count();

        return [
            Stat::make(__('campaigns.stats.total_campaigns'), $totalCampaigns)
                ->description(__('campaigns.stats.total_campaigns_description'))
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('primary'),

            Stat::make(__('campaigns.stats.active_campaigns'), $activeCampaigns)
                ->description(__('campaigns.stats.active_campaigns_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('campaigns.stats.scheduled_campaigns'), $scheduledCampaigns)
                ->description(__('campaigns.stats.scheduled_campaigns_description'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('campaigns.stats.running_campaigns'), $runningCampaigns)
                ->description(__('campaigns.stats.running_campaigns_description'))
                ->descriptionIcon('heroicon-m-play')
                ->color('info'),
        ];
    }
}
