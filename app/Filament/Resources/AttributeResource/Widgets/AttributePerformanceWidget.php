<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeResource\Widgets;

use App\Models\Attribute;
use Filament\Widgets\ChartWidget;

final class AttributePerformanceWidget extends ChartWidget
{
    protected ?string $heading = 'Attribute Performance by Filterability';

    protected function getData(): array
    {
        $filterable = Attribute::where('is_filterable', true)->count();
        $nonFilterable = Attribute::where('is_filterable', false)->count();
        $required = Attribute::where('is_required', true)->count();
        $optional = Attribute::where('is_required', false)->count();

        return [
            'datasets' => [
                [
                    'label' => __('attributes.charts.attribute_performance'),
                    'data' => [$filterable, $nonFilterable, $required, $optional],
                    'backgroundColor' => [
                        '#10B981', // emerald (filterable)
                        '#6B7280', // gray (non-filterable)
                        '#F59E0B', // amber (required)
                        '#3B82F6', // blue (optional)
                    ],
                ],
            ],
            'labels' => [
                __('attributes.charts.filterable'),
                __('attributes.charts.non_filterable'),
                __('attributes.charts.required'),
                __('attributes.charts.optional'),
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
