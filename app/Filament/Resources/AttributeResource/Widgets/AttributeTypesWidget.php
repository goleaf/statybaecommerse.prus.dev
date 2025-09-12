<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeResource\Widgets;

use App\Models\Attribute;
use Filament\Widgets\ChartWidget;

final class AttributeTypesWidget extends ChartWidget
{
    protected static ?string $heading = 'Attribute Types Distribution';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $types = Attribute::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $labels = [];
        $data = [];
        $colors = [];

        $typeColors = [
            'text' => '#3B82F6',
            'number' => '#10B981',
            'boolean' => '#F59E0B',
            'select' => '#8B5CF6',
            'multiselect' => '#EF4444',
            'color' => '#EC4899',
            'date' => '#06B6D4',
            'textarea' => '#84CC16',
            'file' => '#F97316',
            'image' => '#6366F1',
        ];

        foreach ($types as $type => $count) {
            $labels[] = __('attributes.'.$type);
            $data[] = $count;
            $colors[] = $typeColors[$type] ?? '#6B7280';
        }

        return [
            'datasets' => [
                [
                    'label' => __('attributes.attributes'),
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                    'borderWidth' => 1,
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
