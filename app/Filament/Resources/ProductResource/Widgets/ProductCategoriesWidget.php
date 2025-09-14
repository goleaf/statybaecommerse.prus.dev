<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Models\Product;
use App\Models\Category;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final /**
 * ProductCategoriesWidget
 * 
 * Filament resource for admin panel management.
 */
class ProductCategoriesWidget extends ChartWidget
{
    protected static ?string $heading = 'Products by Category';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $categoryData = Product::join('product_categories', 'products.id', '=', 'product_categories.product_id')
            ->join('categories', 'product_categories.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('COUNT(products.id) as count'))
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        $labels = $categoryData->pluck('name')->toArray();
        $data = $categoryData->pluck('count')->toArray();

        // Generate colors for each category
        $colors = [];
        for ($i = 0; $i < count($labels); $i++) {
            $colors[] = 'hsl(' . ($i * 360 / count($labels)) . ', 70%, 50%)';
        }

        return [
            'datasets' => [
                [
                    'label' => __('translations.products_count'),
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.label + ": " + context.parsed + " " + "' . __('translations.products') . '";
                        }',
                    ],
                ],
            ],
        ];
    }
}
