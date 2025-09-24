<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * ProductChartWidget
 *
 * Chart widget showing product performance over time
 */
final class ProductChartWidget extends ChartWidget
{
    protected ?string $heading = 'Product Performance';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = collect();

        // Get data for the last 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $productsCreated = Product::whereDate('created_at', $date)->count();
            $data->push([
                'date' => $date->format('M d'),
                'products_created' => $productsCreated,
            ]);
        }

        return [
            'datasets' => [
                [
                    'label' => __('products.widgets.products_created'),
                    'data' => $data->pluck('products_created'),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $data->pluck('date'),
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
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
