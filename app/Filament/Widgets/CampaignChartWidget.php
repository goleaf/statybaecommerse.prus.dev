<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Campaign;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class CampaignChartWidget extends ChartWidget
{
    protected ?string $heading = 'Campaign Performance';

    protected function getData(): array
    {
        $data = Campaign::selectRaw('DATE(created_at) as date, SUM(total_views) as views, SUM(total_clicks) as clicks, SUM(total_conversions) as conversions')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('campaigns.analytics.views'),
                    'data' => $data->pluck('views')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => __('campaigns.analytics.clicks'),
                    'data' => $data->pluck('clicks')->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => __('campaigns.analytics.conversions'),
                    'data' => $data->pluck('conversions')->toArray(),
                    'backgroundColor' => 'rgba(245, 158, 11, 0.5)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $data->pluck('date')->map(fn($date) => Carbon::parse($date)->format('M j'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
