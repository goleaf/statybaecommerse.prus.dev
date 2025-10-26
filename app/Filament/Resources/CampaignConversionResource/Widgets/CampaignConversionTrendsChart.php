<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignConversionResource\Widgets;

use App\Models\CampaignConversion;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class CampaignConversionTrendsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): ?string
    {
        return __('campaign_conversions.widgets.conversion_trends');
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getChartId(): string
    {
        return 'conversion_trends';
    }

    protected function getData(): array
    {
        $startDate = Carbon::now()->subDays(14)->startOfDay();

        $days = Collection::times(15, fn (int $i): Carbon => (clone $startDate)->addDays($i));

        $counts = [];
        $values = [];
        $labels = [];

        $grouped = CampaignConversion::where('converted_at', '>=', $startDate)
            ->selectRaw('DATE(converted_at) as date, COUNT(*) as total, SUM(conversion_value) as value')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $valueGrouped = CampaignConversion::where('converted_at', '>=', $startDate)
            ->selectRaw('DATE(converted_at) as date, SUM(conversion_value) as value')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('value', 'date');

        foreach ($days as $day) {
            $key = $day->toDateString();
            $labels[] = $day->format('M d');
            $counts[] = (int) ($grouped[$key] ?? 0);
            $values[] = (float) ($valueGrouped[$key] ?? 0.0);
        }

        return [
            'datasets' => [
                [
                    'label'           => __('campaign_conversions.widgets.conversion_count'),
                    'data'            => $counts,
                    'borderColor'     => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'yAxisID'         => 'y',
                ],
                [
                    'label'           => __('campaign_conversions.widgets.conversion_value'),
                    'data'            => $values,
                    'borderColor'     => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'yAxisID'         => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'scales'              => [
                'y' => [
                    'beginAtZero' => true,
                ],
                'y1' => [
                    'beginAtZero' => true,
                    'position'    => 'right',
                ],
            ],
        ];
    }
}
