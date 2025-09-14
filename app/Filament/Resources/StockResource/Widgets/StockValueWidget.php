<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockResource\Widgets;

use App\Models\VariantInventory;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

/**
 * StockValueWidget
 * 
 * Filament resource for admin panel management.
 */
class StockValueWidget extends ChartWidget
{
    protected static ?string $heading = 'inventory.stock_value_by_location';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return __('inventory.stock_value_by_location');
    }

    protected function getData(): array
    {
        $stockValues = VariantInventory::query()
            ->join('locations', 'variant_inventories.location_id', '=', 'locations.id')
            ->select(
                'locations.name as location_name',
                DB::raw('SUM(variant_inventories.stock * COALESCE(variant_inventories.cost_per_unit, 0)) as total_value'),
                DB::raw('SUM(variant_inventories.reserved * COALESCE(variant_inventories.cost_per_unit, 0)) as reserved_value')
            )
            ->groupBy('locations.id', 'locations.name')
            ->orderBy('total_value', 'desc')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('inventory.available_stock_value'),
                    'data' => $stockValues->map(fn ($item) => $item->total_value - $item->reserved_value)->toArray(),
                    'backgroundColor' => [
                        '#10B981', '#3B82F6', '#8B5CF6', '#F59E0B', '#EF4444',
                        '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6366F1',
                    ],
                    'borderColor' => [
                        '#059669', '#2563EB', '#7C3AED', '#D97706', '#DC2626',
                        '#0891B2', '#65A30D', '#EA580C', '#DB2777', '#4F46E5',
                    ],
                    'borderWidth' => 2,
                ],
                [
                    'label' => __('inventory.reserved_stock_value'),
                    'data' => $stockValues->map(fn ($item) => $item->reserved_value)->toArray(),
                    'backgroundColor' => [
                        '#FCD34D', '#93C5FD', '#C4B5FD', '#FDE68A', '#FCA5A5',
                        '#67E8F9', '#BEF264', '#FDBA74', '#F9A8D4', '#A5B4FC',
                    ],
                    'borderColor' => [
                        '#F59E0B', '#3B82F6', '#8B5CF6', '#F59E0B', '#EF4444',
                        '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6366F1',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $stockValues->pluck('location_name')->toArray(),
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
                    'text' => __('inventory.stock_value_distribution'),
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "€" + value.toLocaleString(); }',
                    ],
                ],
            ],
        ];
    }
}
