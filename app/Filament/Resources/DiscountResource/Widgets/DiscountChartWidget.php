<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountResource\Widgets;

use Filament\Widgets\ChartWidget;

final class DiscountChartWidget extends ChartWidget
{
    protected ?string $heading = 'Discount Usage Over Time';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // Example dataset; you may replace with real data aggregation
        return [
            'datasets' => [
                [
                    'label' => __('discounts.usage'),
                    'data' => [5, 10, 7, 14, 9, 12, 8],
                ],
            ],
            'labels' => [
                __('common.mon'),
                __('common.tue'),
                __('common.wed'),
                __('common.thu'),
                __('common.fri'),
                __('common.sat'),
                __('common.sun'),
            ],
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
