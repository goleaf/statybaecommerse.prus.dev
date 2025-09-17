<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class InventoryAnalyticsWidget extends ChartWidget
{
    protected string $view = 'filament.widgets.inventory-analytics-widget';
    
    protected ?string $heading = 'Inventory Analytics';
    protected static ?int $sort = 7;
    protected int|string|array $columnSpan = 2;
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Get inventory data by location
        $locations = Location::with('inventories')->get();

        $locationNames = [];
        $stockValueData = [];
        $stockQuantityData = [];
        $lowStockData = [];
        $outOfStockData = [];

        foreach ($locations as $location) {
            $locationNames[] = \Str::limit($location->name, 15);

            // Calculate total stock value
            $stockValue = $location->inventories->sum(function ($inventory) {
                return $inventory->stock_quantity * $inventory->cost_price;
            });
            $stockValueData[] = $stockValue;

            // Calculate total stock quantity
            $stockQuantity = $location->inventories->sum('stock_quantity');
            $stockQuantityData[] = $stockQuantity;

            // Count low stock items
            $lowStock = $location
                ->inventories
                ->where('stock_quantity', '<=', DB::raw('low_stock_threshold'))
                ->where('stock_quantity', '>', 0)
                ->count();
            $lowStockData[] = $lowStock;

            // Count out of stock items
            $outOfStock = $location->inventories->where('stock_quantity', '<=', 0)->count();
            $outOfStockData[] = $outOfStock;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Stock Value (€)',
                    'data' => $stockValueData,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Stock Quantity',
                    'data' => $stockQuantityData,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Low Stock Items',
                    'data' => $lowStockData,
                    'borderColor' => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Out of Stock Items',
                    'data' => $outOfStockData,
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $locationNames,
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
                    'position' => 'top',
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Inventory by Location',
                ],
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Stock Value (€)',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Quantity',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}
