<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignResource\Widgets;

use App\Models\Campaign;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class CampaignAnalyticsWidget extends ChartWidget
{
    protected ?string $heading = 'Campaign Analytics';

    protected function getData(): array
    {
        $data = Campaign::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('campaigns.charts.campaigns_by_type'),
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => [
                        '#3B82F6', // blue
                        '#10B981', // emerald
                        '#F59E0B', // amber
                        '#EF4444', // red
                        '#8B5CF6', // violet
                        '#06B6D4', // cyan
                        '#84CC16', // lime
                        '#F97316', // orange
                        '#EC4899', // pink
                        '#6B7280', // gray
                    ],
                ],
            ],
            'labels' => $data->pluck('type')->map(fn ($type) => __("campaigns.types.{$type}"))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
