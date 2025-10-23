<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignConversionResource\Widgets;

use App\Models\CampaignConversion;
use Filament\Widgets\ChartWidget;

final class CampaignConversionDeviceBreakdownChart extends ChartWidget
{
    protected static ?int $sort = 3;

    public function getHeading(): ?string
    {
        return __('campaign_conversions.widgets.device_breakdown');
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    public function getChartId(): string
    {
        return 'device_breakdown';
    }

    protected function getData(): array
    {
        $counts = CampaignConversion::selectRaw('device_type, COUNT(*) as total')
            ->groupBy('device_type')
            ->pluck('total', 'device_type');

        $labels = [
            __('campaign_conversions.device_types.mobile'),
            __('campaign_conversions.device_types.tablet'),
            __('campaign_conversions.device_types.desktop'),
            __('campaign_conversions.device_types.unknown'),
        ];

        $data = [
            (int) ($counts['mobile'] ?? 0),
            (int) ($counts['tablet'] ?? 0),
            (int) ($counts['desktop'] ?? 0),
            (int) ($counts[''] ?? 0) + (int) ($counts['unknown'] ?? 0),
        ];

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(234, 179, 8, 0.7)',
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(148, 163, 184, 0.7)',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }
}
