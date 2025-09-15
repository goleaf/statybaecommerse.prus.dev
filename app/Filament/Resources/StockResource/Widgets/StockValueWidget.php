<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockResource\Widgets;

use App\Models\Stock;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class StockValueWidget extends ChartWidget
{
    protected ?string $heading = 'Stock Value by Product';

    protected function getData(): array
    {
        $data = Stock::join('products', 'stocks.product_id', '=', 'products.id')
            ->join('prices', 'products.id', '=', 'prices.product_id')
            ->select(
                'products.name',
                DB::raw('SUM(stocks.quantity * prices.price) as total_value')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_value', 'desc')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('stocks.charts.stock_value'),
                    'data' => $data->pluck('total_value')->toArray(),
                    'backgroundColor' => [
                        '#3B82F6', // blue
                        '#10B981', // emerald
                        '#F59E0B', // amber
                        '#EF4444', // red
                        '#8B5CF6', // violet
                        '#06B6D4', // cyan
                        '#84CC16', // lime
                        '#F97316', // orange
                        '#EC4899', // pink
                        '#6B7280', // gray
                    ],
                ],
            ],
            'labels' => $data->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
