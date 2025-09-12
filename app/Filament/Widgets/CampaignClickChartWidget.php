<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\CampaignClick;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CampaignClickChartWidget extends ChartWidget
{
    protected ?string $heading = 'Campaign Clicks Over Time';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $clicks = CampaignClick::select(
            DB::raw('DATE(clicked_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('clicked_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('campaign_clicks.clicks'),
                    'data' => $clicks->pluck('count'),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
            ],
            'labels' => $clicks->pluck('date'),
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
