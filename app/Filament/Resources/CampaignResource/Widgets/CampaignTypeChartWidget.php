<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignResource\Widgets;

use App\Models\Campaign;
use Filament\Widgets\ChartWidget;

final class CampaignTypeChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Campaign Types Distribution';

    protected function getData(): array
    {
        $campaignTypes = Campaign::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $labels = array_keys($campaignTypes);
        $data = array_values($campaignTypes);

        return [
            'datasets' => [
                [
                    'label' => __('campaigns.charts.campaign_types'),
                    'data' => $data,
                    'backgroundColor' => [
                        '#3B82F6', // Blue
                        '#10B981', // Green
                        '#F59E0B', // Yellow
                        '#8B5CF6', // Purple
                        '#EC4899', // Pink
                        '#EF4444', // Red
                    ],
                    'borderColor' => [
                        '#1E40AF',
                        '#059669',
                        '#D97706',
                        '#7C3AED',
                        '#DB2777',
                        '#DC2626',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => array_map(fn($type) => __('campaigns.types.' . $type), $labels),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    public function getHeading(): string
    {
        return __('campaigns.charts.campaign_types_heading');
    }
}
