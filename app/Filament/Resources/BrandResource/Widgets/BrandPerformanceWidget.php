<?php

declare(strict_types=1);

namespace App\Filament\Resources\BrandResource\Widgets;

use App\Models\Brand;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class BrandPerformanceWidget extends ChartWidget
{
    protected ?string $heading = 'Brand Performance by Product Count';

    protected function getData(): array
    {
        $data = Brand::withCount('products')
            ->orderBy('products_count', 'desc')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('brands.charts.products_per_brand'),
                    'data' => $data->pluck('products_count')->toArray(),
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
