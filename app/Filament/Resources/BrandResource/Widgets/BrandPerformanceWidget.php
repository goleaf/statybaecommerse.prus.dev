<?php

declare(strict_types=1);

namespace App\Filament\Resources\BrandResource\Widgets;

use App\Models\Brand;
use Filament\Widgets\ChartWidget;

final /**
 * BrandPerformanceWidget
 * 
 * Filament resource for admin panel management.
 */
class BrandPerformanceWidget extends ChartWidget
{
    protected static ?string $heading = 'admin.brands.widgets.performance_heading';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $brands = Brand::withCount(['products', 'translations'])
            ->orderBy('products_count', 'desc')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('admin.brands.widgets.products_count'),
                    'data' => $brands->pluck('products_count')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => __('admin.brands.widgets.translations_count'),
                    'data' => $brands->pluck('translations_count')->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $brands->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
