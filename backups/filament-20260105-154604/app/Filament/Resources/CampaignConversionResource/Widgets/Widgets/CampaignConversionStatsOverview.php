<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignConversionResource\Widgets;

use App\Models\CampaignConversion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

final class CampaignConversionStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalConversions = CampaignConversion::count();
        $totalValue = (float) CampaignConversion::sum('conversion_value');
        $averageValue = $totalConversions > 0 ? $totalValue / $totalConversions : 0.0;
        $recentConversions = CampaignConversion::where('converted_at', '>=', Carbon::now()->subDays(7))->count();

        return [
            Stat::make(__('campaign_conversions.widgets.total_conversions'), number_format($totalConversions))
                ->description(__('campaign_conversions.widgets.total_conversions_description'))
                ->color('primary'),
            Stat::make(__('campaign_conversions.widgets.total_value'), '€' . number_format($totalValue, 2))
                ->description(__('campaign_conversions.widgets.total_value_description'))
                ->color('success'),
            Stat::make(__('campaign_conversions.widgets.average_value'), '€' . number_format($averageValue, 2))
                ->description(__('campaign_conversions.widgets.average_value_description'))
                ->color('info'),
            Stat::make(__('campaign_conversions.widgets.recent_conversions'), number_format($recentConversions))
                ->description(__('campaign_conversions.widgets.recent_conversions_description'))
                ->color('warning'),
        ];
    }
}
