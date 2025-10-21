<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Filament\Support\InteractsWithDateFilter;
use App\Support\Stats\OrderMetrics;
use EightyNine\FilamentAdvancedWidget\AdvancedChartWidget;

final class OrderRevenueTrend extends AdvancedChartWidget
{
    use InteractsWithDateFilter;

    protected static ?string $heading = 'Revenue trend';

    protected static string $color = 'primary';

    protected static ?string $icon = 'heroicon-o-chart-bar-square';

    protected static ?string $badgeColor = 'primary';

    public ?string $filter = 'quarter';

    protected function getFilters(): ?array
    {
        return [
            'month'   => __('Last month'),
            'quarter' => __('This quarter'),
            'year'    => __('This year'),
            'ytd'     => __('Year to date'),
        ];
    }

    protected function getData(): array
    {
        [$from, $to] = $this->getDateRange($this->filter);
        $trend = OrderMetrics::ordersTrend($from, $to);

        return [
            'datasets' => [
                [
                    'label'           => __('Orders'),
                    'data'            => $trend['orders'],
                    'type'            => 'bar',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.25)',
                    'borderColor'     => 'rgba(59, 130, 246, 0.6)',
                    'borderWidth'     => 1,
                    'yAxisID'         => 'y-orders',
                ],
                [
                    'label'           => __('Revenue'),
                    'data'            => $trend['revenue'],
                    'type'            => 'line',
                    'borderColor'     => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.1)',
                    'borderWidth'     => 3,
                    'tension'         => 0.35,
                    'yAxisID'         => 'y-revenue',
                ],
            ],
            'labels'  => $trend['labels'],
            'options' => [
                'responsive'          => true,
                'maintainAspectRatio' => false,
                'scales'              => [
                    'y-orders' => [
                        'type'        => 'linear',
                        'position'    => 'left',
                        'beginAtZero' => true,
                    ],
                    'y-revenue' => [
                        'type'        => 'linear',
                        'position'    => 'right',
                        'beginAtZero' => true,
                        'grid'        => [
                            'drawOnChartArea' => false,
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
