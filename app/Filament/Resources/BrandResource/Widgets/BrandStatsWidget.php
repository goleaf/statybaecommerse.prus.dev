<?php

declare(strict_types=1);

namespace App\Filament\Resources\BrandResource\Widgets;

use App\Models\Brand;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class BrandStatsWidget extends ChartWidget
{
    protected ?string $heading = 'Brand Statistics';

    protected function getData(): array
    {
        $data = Brand::withCount(['products', 'categories'])
            ->orderBy('products_count', 'desc')
            ->limit(8)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('brands.charts.products_count'),
                    'data' => $data->pluck('products_count')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => '#3B82F6',
                    'borderWidth' => 1,
                ],
                [
                    'label' => __('brands.charts.categories_count'),
                    'data' => $data->pluck('categories_count')->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                    'borderColor' => '#10B981',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $data->pluck('name')->toArray(),
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
