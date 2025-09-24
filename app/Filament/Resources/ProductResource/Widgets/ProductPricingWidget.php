<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;

final class ProductPricingWidget extends ChartWidget
{
    protected ?string $heading = 'Product Price Distribution';

    protected function getData(): array
    {
        $priceRanges = [
            '0-10' => Product::whereHas('prices', function ($query) {
                $query->whereBetween('price', [0, 10]);
            })->count(),
            '10-50' => Product::whereHas('prices', function ($query) {
                $query->whereBetween('price', [10, 50]);
            })->count(),
            '50-100' => Product::whereHas('prices', function ($query) {
                $query->whereBetween('price', [50, 100]);
            })->count(),
            '100-500' => Product::whereHas('prices', function ($query) {
                $query->whereBetween('price', [100, 500]);
            })->count(),
            '500+' => Product::whereHas('prices', function ($query) {
                $query->where('price', '>', 500);
            })->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => __('products.charts.price_distribution'),
                    'data' => array_values($priceRanges),
                    'backgroundColor' => [
                        '#10B981', // emerald
                        '#3B82F6', // blue
                        '#F59E0B', // amber
                        '#EF4444', // red
                        '#8B5CF6', // violet
                    ],
                ],
            ],
            'labels' => array_keys($priceRanges),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
