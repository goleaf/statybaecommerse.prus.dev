<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockResource\Widgets;

use App\Models\Stock;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class StockMovementsWidget extends ChartWidget
{
    protected ?string $heading = 'Stock Movements Over Time';

    protected function getData(): array
    {
        $data = Stock::select(
                DB::raw('DATE(updated_at) as date'),
                DB::raw('COUNT(*) as movements')
            )
            ->where('updated_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('stocks.charts.stock_movements'),
                    'data' => $data->pluck('movements')->toArray(),
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data->pluck('date')->map(fn ($date) => \Carbon\Carbon::parse($date)->format('M d'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
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

