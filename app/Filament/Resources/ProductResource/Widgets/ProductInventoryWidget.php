<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;

final class ProductInventoryWidget extends ChartWidget
{
    protected ?string $heading = 'Product Inventory Status';

    protected function getData(): array
    {
        $inStock = Product::whereHas('inventories', function ($query) {
            $query->where('quantity', '>', 10);
        })->count();

        $lowStock = Product::whereHas('inventories', function ($query) {
            $query->where('quantity', '<=', 10)->where('quantity', '>', 0);
        })->count();

        $outOfStock = Product::whereHas('inventories', function ($query) {
            $query->where('quantity', '=', 0);
        })->count();

        $noInventory = Product::whereDoesntHave('inventories')->count();

        return [
            'datasets' => [
                [
                    'label' => __('products.charts.inventory_status'),
                    'data' => [$inStock, $lowStock, $outOfStock, $noInventory],
                    'backgroundColor' => [
                        '#10B981', // emerald (in stock)
                        '#F59E0B', // amber (low stock)
                        '#EF4444', // red (out of stock)
                        '#6B7280', // gray (no inventory)
                    ],
                ],
            ],
            'labels' => [
                __('products.charts.in_stock'),
                __('products.charts.low_stock'),
                __('products.charts.out_of_stock'),
                __('products.charts.no_inventory'),
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
