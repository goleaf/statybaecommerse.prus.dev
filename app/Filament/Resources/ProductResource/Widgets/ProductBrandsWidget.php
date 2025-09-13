<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Models\Product;
use App\Models\Brand;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class ProductBrandsWidget extends ChartWidget
{
    protected static ?string $heading = 'Products by Brand';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $brandData = Product::join('brands', 'products.brand_id', '=', 'brands.id')
            ->select('brands.name', DB::raw('COUNT(products.id) as count'))
            ->groupBy('brands.id', 'brands.name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        $labels = $brandData->pluck('name')->toArray();
        $data = $brandData->pluck('count')->toArray();

        // Generate colors for each brand
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
        return 'bar';
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
                            return context.label + ": " + context.parsed.y + " " + "' . __('translations.products') . '";
                        }',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}