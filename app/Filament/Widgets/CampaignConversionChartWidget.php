<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\CampaignConversion;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
/**
 * CampaignConversionChartWidget
 * 
 * Filament v4 widget for CampaignConversionChartWidget dashboard display with real-time data and interactive features.
 * 
 * @property string|null $heading
 * @property int|null $sort
 */
class CampaignConversionChartWidget extends ChartWidget
{
    protected ?string $heading = 'campaign_conversions.widgets.conversion_trends';
    protected static ?int $sort = 2;
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $data = CampaignConversion::selectRaw('DATE(converted_at) as date, COUNT(*) as count, SUM(conversion_value) as value')->where('converted_at', '>=', now()->subDays(30))->groupBy('date')->orderBy('date')->get();
        return ['datasets' => [['label' => __('campaign_conversions.widgets.conversion_count'), 'data' => $data->pluck('count')->toArray(), 'borderColor' => 'rgb(59, 130, 246)', 'backgroundColor' => 'rgba(59, 130, 246, 0.1)', 'yAxisID' => 'y'], ['label' => __('campaign_conversions.widgets.conversion_value'), 'data' => $data->pluck('value')->toArray(), 'borderColor' => 'rgb(16, 185, 129)', 'backgroundColor' => 'rgba(16, 185, 129, 0.1)', 'yAxisID' => 'y1']], 'labels' => $data->pluck('date')->map(fn($date) => Carbon::parse($date)->format('M d'))->toArray()];
    }
    /**
     * Handle getType functionality with proper error handling.
     * @return string
     */
    protected function getType(): string
    {
        return 'line';
    }
    /**
     * Handle getOptions functionality with proper error handling.
     * @return array
     */
    protected function getOptions(): array
    {
        return ['scales' => ['y' => ['type' => 'linear', 'display' => true, 'position' => 'left', 'title' => ['display' => true, 'text' => __('campaign_conversions.widgets.conversion_count')]], 'y1' => ['type' => 'linear', 'display' => true, 'position' => 'right', 'title' => ['display' => true, 'text' => __('campaign_conversions.widgets.conversion_value')], 'grid' => ['drawOnChartArea' => false]]]];
    }
}