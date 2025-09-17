<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class ProductReviewsWidget extends ChartWidget
{
    protected ?string $heading = 'Product Reviews Distribution';

    protected function getData(): array
    {
        $reviewRanges = [
            '5 stars' => Product::whereHas('reviews', function ($query) {
                $query->where('rating', 5);
            })->count(),
            '4 stars' => Product::whereHas('reviews', function ($query) {
                $query->where('rating', 4);
            })->count(),
            '3 stars' => Product::whereHas('reviews', function ($query) {
                $query->where('rating', 3);
            })->count(),
            '2 stars' => Product::whereHas('reviews', function ($query) {
                $query->where('rating', 2);
            })->count(),
            '1 star' => Product::whereHas('reviews', function ($query) {
                $query->where('rating', 1);
            })->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => __('products.charts.review_distribution'),
                    'data' => array_values($reviewRanges),
                    'backgroundColor' => [
                        '#10B981', // emerald (5 stars)
                        '#3B82F6', // blue (4 stars)
                        '#F59E0B', // amber (3 stars)
                        '#EF4444', // red (2 stars)
                        '#6B7280', // gray (1 star)
                    ],
                ],
            ],
            'labels' => array_keys($reviewRanges),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
