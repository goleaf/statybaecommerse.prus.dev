<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountConditionResource\Widgets;

use App\Models\DiscountCondition;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final /**
 * DiscountConditionChartWidget
 * 
 * Filament resource for admin panel management.
 */
class DiscountConditionChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Discount Conditions by Type';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = DiscountCondition::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type')
            ->toArray();

        $labels = [];
        $values = [];

        foreach ($data as $type => $count) {
            $labels[] = DiscountCondition::getTypes()[$type] ?? $type;
            $values[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => __('discount_conditions.charts.conditions_by_type'),
                    'data' => $values,
                    'backgroundColor' => [
                        '#3B82F6', // blue
                        '#10B981', // emerald
                        '#F59E0B', // amber
                        '#EF4444', // red
                        '#8B5CF6', // violet
                        '#06B6D4', // cyan
                        '#84CC16', // lime
                        '#F97316', // orange
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
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ": " + value + " (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
        ];
    }
}
