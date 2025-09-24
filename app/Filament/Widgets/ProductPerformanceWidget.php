<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;

final class ProductPerformanceWidget extends ChartWidget
{
    protected ?string $heading = 'Product Performance';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $products = Product::select('name', 'views_count', 'created_at')
            ->orderBy('views_count', 'desc')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('dashboard.product_views'),
                    'data' => $products->pluck('views_count')->toArray(),
                    'backgroundColor' => [
                        '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                        '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6366F1',
                    ],
                    'borderColor' => [
                        '#1E40AF', '#059669', '#D97706', '#DC2626', '#7C3AED',
                        '#0891B2', '#65A30D', '#EA580C', '#DB2777', '#4F46E5',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $products->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
