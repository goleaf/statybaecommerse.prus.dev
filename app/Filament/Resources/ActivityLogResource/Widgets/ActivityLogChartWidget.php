<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogResource\Widgets;

use Filament\Widgets\ChartWidget;
use Spatie\Activitylog\Models\Activity;

final /**
 * ActivityLogChartWidget
 * 
 * Filament resource for admin panel management.
 */
class ActivityLogChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Activity Logs Over Time';

    protected function getData(): array
    {
        $data = Activity::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('admin.activity_logs.chart.activities'),
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
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
