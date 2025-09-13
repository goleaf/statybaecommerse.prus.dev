<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Collection;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class CollectionPerformanceWidget extends ChartWidget
{
    protected ?string $heading = 'admin.collections.charts.performance_heading';

    protected static ?int $sort = 2;

    protected ?string $pollingInterval = '60s';

    protected function getData(): array
    {
        $collections = Collection::withCount('products')
            ->orderBy('products_count', 'desc')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('admin.collections.charts.products_count'),
                    'data' => $collections->pluck('products_count')->toArray(),
                    'backgroundColor' => [
                        '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                        '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6B7280',
                    ],
                    'borderColor' => [
                        '#1E40AF', '#059669', '#D97706', '#DC2626', '#7C3AED',
                        '#0891B2', '#65A30D', '#EA580C', '#DB2777', '#4B5563',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $collections->pluck('name')->toArray(),
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
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return "' . __('admin.collections.charts.products_count') . ': " + context.parsed.y;
                        }',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}