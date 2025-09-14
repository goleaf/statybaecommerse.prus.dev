<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\CampaignConversion;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * CampaignConversionChartWidget
 * 
 * Filament widget for admin panel dashboard.
 */
class CampaignConversionChartWidget extends ChartWidget
{
    protected ?string $heading = 'campaign_conversions.widgets.conversion_trends';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = CampaignConversion::selectRaw('DATE(converted_at) as date, COUNT(*) as count, SUM(conversion_value) as value')
            ->where('converted_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('campaign_conversions.widgets.conversion_count'),
                    'data' => $data->pluck('count')->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => __('campaign_conversions.widgets.conversion_value'),
                    'data' => $data->pluck('value')->toArray(),
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $data->pluck('date')->map(fn ($date) => Carbon::parse($date)->format('M d'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => __('campaign_conversions.widgets.conversion_count'),
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => __('campaign_conversions.widgets.conversion_value'),
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}
