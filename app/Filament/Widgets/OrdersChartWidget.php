<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

final class OrdersChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): ?string
    {
        return __('analytics.orders_overview');
    }

    public function getDescription(): ?string
    {
        return __('analytics.orders_and_revenue_trends');
    }

    public function getData(): array
    {
        $data = collect(range(0, 11))->map(function ($month) {
            $date = now()->subMonths($month);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            return [
                'month'  => $date->format('M Y'),
                'orders' => Order::createdInMonth($date)
                    ->count(),
                'revenue' => Order::createdInMonth($date)
                    ->where('status', '!=', 'cancelled')
                    ->createdBetween($startOfMonth, $endOfMonth)
                    ->sum('total'),
            ];
        })->reverse()->values();

        return [
            'datasets' => [
                [
                    'label'           => __('admin.charts.orders'),
                    'data'            => $data->pluck('orders')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor'     => 'rgb(59, 130, 246)',
                    'yAxisID'         => 'y',
                ],
                [
                    'label'           => __('admin.charts.revenue'),
                    'data'            => $data->pluck('revenue')->toArray(),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor'     => 'rgb(34, 197, 94)',
                    'yAxisID'         => 'y1',
                ],
            ],
            'labels' => $data->pluck('month')->toArray(),
        ];
    }

    public function getType(): string
    {
        return 'line';
    }

    public function getOptions(): array
    {
        return [
            'responsive'  => true,
            'interaction' => [
                'mode'      => 'index',
                'intersect' => false,
            ],
            'scales' => [
                'y' => [
                    'type'     => 'linear',
                    'display'  => true,
                    'position' => 'left',
                    'title'    => [
                        'display' => true,
                        'text'    => __('admin.charts.orders'),
                    ],
                ],
                'y1' => [
                    'type'     => 'linear',
                    'display'  => true,
                    'position' => 'right',
                    'title'    => [
                        'display' => true,
                        'text'    => __('admin.charts.revenue_eur'),
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}
