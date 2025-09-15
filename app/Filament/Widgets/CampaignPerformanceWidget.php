<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Campaign;
use App\Models\CampaignClick;
use App\Models\CampaignConversion;
use App\Models\CampaignView;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CampaignPerformanceWidget extends ChartWidget
{
    protected ?string $heading = 'Campaign Performance Analytics';
    protected static ?int $sort = 6;
    protected int|string|array $columnSpan = 2;
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Get top 10 campaigns by views
        $topCampaigns = CampaignView::select('campaign_id', DB::raw('SUM(views_count) as total_views'))
            ->with('campaign:id,name')
            ->groupBy('campaign_id')
            ->orderBy('total_views', 'desc')
            ->limit(10)
            ->get();

        $campaignNames = [];
        $viewsData = [];
        $clicksData = [];
        $conversionsData = [];
        $ctrData = [];
        $conversionRateData = [];

        foreach ($topCampaigns as $item) {
            if ($item->campaign) {
                $campaignNames[] = \Str::limit($item->campaign->name, 20);
                $viewsData[] = $item->total_views;

                // Get clicks for this campaign
                $clicks = CampaignClick::where('campaign_id', $item->campaign_id)
                    ->sum('clicks_count');
                $clicksData[] = $clicks;

                // Get conversions for this campaign
                $conversions = CampaignConversion::where('campaign_id', $item->campaign_id)
                    ->sum('conversions_count');
                $conversionsData[] = $conversions;

                // Calculate CTR (Click Through Rate)
                $ctr = $item->total_views > 0 ? ($clicks / $item->total_views) * 100 : 0;
                $ctrData[] = $ctr;

                // Calculate Conversion Rate
                $conversionRate = $clicks > 0 ? ($conversions / $clicks) * 100 : 0;
                $conversionRateData[] = $conversionRate;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Views',
                    'data' => $viewsData,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Clicks',
                    'data' => $clicksData,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Conversions',
                    'data' => $conversionsData,
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'CTR (%)',
                    'data' => $ctrData,
                    'borderColor' => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Conversion Rate (%)',
                    'data' => $conversionRateData,
                    'borderColor' => 'rgb(168, 85, 247)',
                    'backgroundColor' => 'rgba(168, 85, 247, 0.2)',
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $campaignNames,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Top 10 Campaigns Performance',
                ],
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Count',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'beginAtZero' => true,
                    'max' => 100,
                    'title' => [
                        'display' => true,
                        'text' => 'Rate (%)',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}
