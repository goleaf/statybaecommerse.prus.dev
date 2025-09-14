<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\CampaignConversion;
use Filament\Widgets\ChartWidget;
/**
 * CampaignConversionDeviceWidget
 * 
 * Filament v4 widget for CampaignConversionDeviceWidget dashboard display with real-time data and interactive features.
 * 
 * @property string|null $heading
 * @property int|null $sort
 */
class CampaignConversionDeviceWidget extends ChartWidget
{
    protected ?string $heading = 'campaign_conversions.widgets.device_breakdown';
    protected static ?int $sort = 3;
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $data = CampaignConversion::selectRaw('device_type, COUNT(*) as count, SUM(conversion_value) as value')->whereNotNull('device_type')->groupBy('device_type')->get();
        return ['datasets' => [['data' => $data->pluck('count')->toArray(), 'backgroundColor' => [
            'rgb(59, 130, 246)',
            // Blue for mobile
            'rgb(16, 185, 129)',
            // Green for desktop
            'rgb(245, 158, 11)',
        ]]], 'labels' => $data->pluck('device_type')->map(fn($type) => __("campaign_conversions.device_types.{$type}"))->toArray()];
    }
    /**
     * Handle getType functionality with proper error handling.
     * @return string
     */
    protected function getType(): string
    {
        return 'doughnut';
    }
}