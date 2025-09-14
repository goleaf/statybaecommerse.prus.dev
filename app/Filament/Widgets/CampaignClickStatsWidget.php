<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\CampaignClick;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
/**
 * CampaignClickStatsWidget
 * 
 * Filament v4 widget for CampaignClickStatsWidget dashboard display with real-time data and interactive features.
 * 
 */
class CampaignClickStatsWidget extends BaseWidget
{
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        $totalClicks = CampaignClick::count();
        $convertedClicks = CampaignClick::where('is_converted', true)->count();
        $conversionRate = $totalClicks > 0 ? round($convertedClicks / $totalClicks * 100, 2) : 0;
        $totalConversionValue = CampaignClick::where('is_converted', true)->sum('conversion_value');
        $todayClicks = CampaignClick::whereDate('clicked_at', today())->count();
        $thisWeekClicks = CampaignClick::whereBetween('clicked_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        return [Stat::make(__('campaign_clicks.total_clicks'), $totalClicks)->description(__('campaign_clicks.all_time'))->descriptionIcon('heroicon-m-cursor-arrow-rays')->color('primary'), Stat::make(__('campaign_clicks.converted_clicks'), $convertedClicks)->description(__('campaign_clicks.conversion_rate') . ': ' . $conversionRate . '%')->descriptionIcon('heroicon-m-check-circle')->color('success'), Stat::make(__('campaign_clicks.conversion_value'), '€' . number_format($totalConversionValue, 2))->description(__('campaign_clicks.total_revenue'))->descriptionIcon('heroicon-m-currency-euro')->color('success'), Stat::make(__('campaign_clicks.today_clicks'), $todayClicks)->description(__('campaign_clicks.today'))->descriptionIcon('heroicon-m-calendar-days')->color('info'), Stat::make(__('campaign_clicks.this_week_clicks'), $thisWeekClicks)->description(__('campaign_clicks.this_week'))->descriptionIcon('heroicon-m-calendar')->color('warning')];
    }
}