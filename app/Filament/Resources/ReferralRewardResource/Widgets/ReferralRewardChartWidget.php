<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralRewardResource\Widgets;

use App\Models\ReferralReward;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class ReferralRewardChartWidget extends ChartWidget
{
    protected ?string $heading = 'Referral Rewards Over Time';

    protected function getData(): array
    {
        $data = ReferralReward::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('referral_rewards.charts.rewards_over_time'),
                    'data' => $data->pluck('count')->toArray(),
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data->pluck('date')->map(fn ($date) => \Carbon\Carbon::parse($date)->format('M d'))->toArray(),
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
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
        ];
    }
}
