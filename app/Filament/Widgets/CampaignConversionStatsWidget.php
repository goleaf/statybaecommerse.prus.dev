<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\CampaignConversion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;
/**
 * CampaignConversionStatsWidget
 * 
 * Filament v4 widget for CampaignConversionStatsWidget dashboard display with real-time data and interactive features.
 * 
 */
class CampaignConversionStatsWidget extends BaseWidget
{
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        $totalConversions = CampaignConversion::count();
        $totalValue = CampaignConversion::sum('conversion_value');
        $averageValue = $totalConversions > 0 ? $totalValue / $totalConversions : 0;
        $recentConversions = CampaignConversion::where('converted_at', '>=', now()->subDays(7))->count();
        return [Stat::make(__('campaign_conversions.widgets.total_conversions'), $totalConversions)->description(__('campaign_conversions.widgets.total_conversions_description'))->descriptionIcon('heroicon-m-arrow-trending-up')->color('success'), Stat::make(__('campaign_conversions.widgets.total_value'), '€' . Number::format($totalValue, 2))->description(__('campaign_conversions.widgets.total_value_description'))->descriptionIcon('heroicon-m-currency-euro')->color('info'), Stat::make(__('campaign_conversions.widgets.average_value'), '€' . Number::format($averageValue, 2))->description(__('campaign_conversions.widgets.average_value_description'))->descriptionIcon('heroicon-m-calculator')->color('warning'), Stat::make(__('campaign_conversions.widgets.recent_conversions'), $recentConversions)->description(__('campaign_conversions.widgets.recent_conversions_description'))->descriptionIcon('heroicon-m-clock')->color('primary')];
    }
}