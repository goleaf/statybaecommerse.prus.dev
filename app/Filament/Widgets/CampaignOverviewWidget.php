<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Campaign;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

final class CampaignOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalCampaigns = Campaign::count();
        $activeCampaigns = Campaign::active()->count();
        $scheduledCampaigns = Campaign::scheduled()->count();
        $featuredCampaigns = Campaign::featured()->count();

        $totalViews = Campaign::sum('total_views');
        $totalClicks = Campaign::sum('total_clicks');
        $totalConversions = Campaign::sum('total_conversions');
        $totalRevenue = Campaign::sum('total_revenue');

        $avgConversionRate = Campaign::where('total_clicks', '>', 0)
            ->avg(DB::raw('(total_conversions / total_clicks) * 100'));

        $avgClickThroughRate = Campaign::where('total_views', '>', 0)
            ->avg(DB::raw('(total_clicks / total_views) * 100'));

        return [
            Stat::make(__('campaigns.stats.total_campaigns'), $totalCampaigns)
                ->description(__('campaigns.stats.total_campaigns_description'))
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('primary'),
            Stat::make(__('campaigns.stats.active_campaigns'), $activeCampaigns)
                ->description(__('campaigns.stats.active_campaigns_description'))
                ->descriptionIcon('heroicon-m-play')
                ->color('success'),
            Stat::make(__('campaigns.stats.scheduled_campaigns'), $scheduledCampaigns)
                ->description(__('campaigns.stats.scheduled_campaigns_description'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make(__('campaigns.stats.featured_campaigns'), $featuredCampaigns)
                ->description(__('campaigns.stats.featured_campaigns_description'))
                ->descriptionIcon('heroicon-m-star')
                ->color('info'),
            Stat::make(__('campaigns.stats.total_views'), number_format($totalViews))
                ->description(__('campaigns.stats.total_views_description'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('gray'),
            Stat::make(__('campaigns.stats.total_clicks'), number_format($totalClicks))
                ->description(__('campaigns.stats.total_clicks_description'))
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->color('gray'),
            Stat::make(__('campaigns.stats.total_conversions'), number_format($totalConversions))
                ->description(__('campaigns.stats.total_conversions_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make(__('campaigns.stats.total_revenue'), '€' . number_format($totalRevenue, 2))
                ->description(__('campaigns.stats.total_revenue_description'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),
            Stat::make(__('campaigns.stats.avg_conversion_rate'), number_format($avgConversionRate, 2) . '%')
                ->description(__('campaigns.stats.avg_conversion_rate_description'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
            Stat::make(__('campaigns.stats.avg_ctr'), number_format($avgClickThroughRate, 2) . '%')
                ->description(__('campaigns.stats.avg_ctr_description'))
                ->descriptionIcon('heroicon-m-chart-bar-square')
                ->color('info'),
        ];
    }
}

