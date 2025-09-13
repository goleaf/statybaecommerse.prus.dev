<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignResource\Widgets;

use App\Models\Campaign;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class CampaignAnalyticsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalViews = Campaign::sum('total_views');
        $totalClicks = Campaign::sum('total_clicks');
        $totalConversions = Campaign::sum('total_conversions');
        $totalRevenue = Campaign::sum('total_revenue');

        $averageConversionRate = Campaign::where('total_views', '>', 0)
            ->avg('conversion_rate') ?? 0;

        $averageClickThroughRate = Campaign::where('total_views', '>', 0)
            ->avg(\DB::raw('(total_clicks / total_views) * 100')) ?? 0;

        $averageROI = Campaign::where('budget', '>', 0)
            ->avg(\DB::raw('((total_revenue - budget) / budget) * 100')) ?? 0;

        return [
            Stat::make(__('campaigns.stats.total_views'), number_format($totalViews))
                ->description(__('campaigns.stats.total_views_description'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),

            Stat::make(__('campaigns.stats.total_clicks'), number_format($totalClicks))
                ->description(__('campaigns.stats.total_clicks_description'))
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->color('warning'),

            Stat::make(__('campaigns.stats.total_conversions'), number_format($totalConversions))
                ->description(__('campaigns.stats.total_conversions_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('campaigns.stats.total_revenue'), '€' . number_format($totalRevenue, 2))
                ->description(__('campaigns.stats.total_revenue_description'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),

            Stat::make(__('campaigns.stats.avg_conversion_rate'), number_format($averageConversionRate, 2) . '%')
                ->description(__('campaigns.stats.avg_conversion_rate_description'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            Stat::make(__('campaigns.stats.avg_ctr'), number_format($averageClickThroughRate, 2) . '%')
                ->description(__('campaigns.stats.avg_ctr_description'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),

            Stat::make(__('campaigns.stats.avg_roi'), number_format($averageROI, 2) . '%')
                ->description(__('campaigns.stats.avg_roi_description'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
