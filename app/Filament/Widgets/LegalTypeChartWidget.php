<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Legal;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class LegalTypeChartWidget extends ChartWidget
{
    protected ?string $heading = 'Legal Documents by Type';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Legal::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type')
            ->toArray();

        $types = Legal::getTypes();
        $labels = [];
        $values = [];

        foreach ($types as $key => $label) {
            $labels[] = $label;
            $values[] = $data[$key] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => __('admin.legal.documents_count'),
                    'data' => $values,
                    'backgroundColor' => [
                        '#ef4444', // red
                        '#f97316', // orange
                        '#eab308', // yellow
                        '#22c55e', // green
                        '#06b6d4', // cyan
                        '#3b82f6', // blue
                        '#8b5cf6', // violet
                        '#ec4899', // pink
                        '#6b7280', // gray
                    ],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            const label = context.label || "";
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return label + ": " + value + " (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
        ];
    }
}
