<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockResource\Widgets;

use App\Models\Stock;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class StockDetailsWidget extends ChartWidget
{
    protected ?string $heading = 'Stock Details by Location';

    protected function getData(): array
    {
        $data = Stock::select(
                'location',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(reserved_quantity) as total_reserved'),
                DB::raw('COUNT(*) as item_count')
            )
            ->groupBy('location')
            ->orderBy('total_quantity', 'desc')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('stocks.charts.total_quantity'),
                    'data' => $data->pluck('total_quantity')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => '#3B82F6',
                    'borderWidth' => 1,
                ],
                [
                    'label' => __('stocks.charts.reserved_quantity'),
                    'data' => $data->pluck('total_reserved')->toArray(),
                    'backgroundColor' => 'rgba(245, 158, 11, 0.8)',
                    'borderColor' => '#F59E0B',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $data->pluck('location')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
        ];
    }
}

