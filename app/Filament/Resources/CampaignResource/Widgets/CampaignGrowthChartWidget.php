<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignResource\Widgets;

use App\Models\Campaign;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

final class CampaignGrowthChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Campaign Growth Over Time';

    protected function getData(): array
    {
        $startDate = Carbon::now()->subMonths(12);
        $endDate = Carbon::now();

        $campaigns = Campaign::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];

        // Fill in missing dates with 0
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateString = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format('M d');
            
            $campaign = $campaigns->firstWhere('date', $dateString);
            $data[] = $campaign ? $campaign->count : 0;
            
            $currentDate->addDay();
        }

        return [
            'datasets' => [
                [
                    'label' => __('campaigns.charts.campaigns_created'),
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => '#3B82F6',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): string
    {
        return __('campaigns.charts.campaign_growth_heading');
    }
}
