<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignResource\Widgets;

use App\Models\Campaign;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final /**
 * CampaignPerformanceWidget
 * 
 * Filament resource for admin panel management.
 */
class CampaignPerformanceWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalCampaigns = Campaign::count();
        $activeCampaigns = Campaign::active()->count();
        $highPerformingCampaigns = Campaign::where('conversion_rate', '>', 5)->count();
        $underperformingCampaigns = Campaign::where('conversion_rate', '<', 2)->count();

        return [
            Stat::make(__('campaigns.stats.total_campaigns'), $totalCampaigns)
                ->description(__('campaigns.stats.total_campaigns_description'))
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('primary'),

            Stat::make(__('campaigns.stats.active_campaigns'), $activeCampaigns)
                ->description(__('campaigns.stats.active_campaigns_description'))
                ->descriptionIcon('heroicon-m-play')
                ->color('success'),

            Stat::make(__('campaigns.stats.high_performing'), $highPerformingCampaigns)
                ->description(__('campaigns.stats.high_performing_description'))
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success'),

            Stat::make(__('campaigns.stats.underperforming'), $underperformingCampaigns)
                ->description(__('campaigns.stats.underperforming_description'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
