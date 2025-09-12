<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\CampaignConversion;
use Filament\Widgets\ChartWidget;

class CampaignConversionDeviceWidget extends ChartWidget
{
    protected ?string $heading = 'campaign_conversions.widgets.device_breakdown';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = CampaignConversion::selectRaw('device_type, COUNT(*) as count, SUM(conversion_value) as value')
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->get();

        return [
            'datasets' => [
                [
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => [
                        'rgb(59, 130, 246)',  // Blue for mobile
                        'rgb(16, 185, 129)',  // Green for desktop
                        'rgb(245, 158, 11)',  // Yellow for tablet
                    ],
                ],
            ],
            'labels' => $data->pluck('device_type')->map(fn($type) => __("campaign_conversions.device_types.{$type}"))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
