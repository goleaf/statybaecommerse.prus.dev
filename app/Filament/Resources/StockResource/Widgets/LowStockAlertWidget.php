<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockResource\Widgets;

use App\Models\Stock;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class LowStockAlertWidget extends ChartWidget
{
    protected ?string $heading = 'Low Stock Alerts';

    protected function getData(): array
    {
        $lowStock = Stock::where('quantity', '<=', 10)->where('quantity', '>', 0)->count();
        $outOfStock = Stock::where('quantity', '=', 0)->count();
        $criticalStock = Stock::where('quantity', '<=', 5)->where('quantity', '>', 0)->count();
        $normalStock = Stock::where('quantity', '>', 10)->count();

        return [
            'datasets' => [
                [
                    'label' => __('stocks.charts.stock_status'),
                    'data' => [$normalStock, $lowStock, $criticalStock, $outOfStock],
                    'backgroundColor' => [
                        '#10B981', // emerald (normal)
                        '#F59E0B', // amber (low)
                        '#EF4444', // red (critical)
                        '#6B7280', // gray (out of stock)
                    ],
                ],
            ],
            'labels' => [
                __('stocks.charts.normal_stock'),
                __('stocks.charts.low_stock'),
                __('stocks.charts.critical_stock'),
                __('stocks.charts.out_of_stock'),
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}

