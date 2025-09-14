<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Location;
use App\Models\Inventory;
use App\Models\VariantInventory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final /**
 * LocationInventoryWidget
 * 
 * Filament widget for admin panel dashboard.
 */
class LocationInventoryWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $totalInventoryValue = Inventory::join('locations', 'inventories.location_id', '=', 'locations.id')
            ->selectRaw('SUM(inventories.quantity * COALESCE(products.price, 0)) as total_value')
            ->leftJoin('products', 'inventories.product_id', '=', 'products.id')
            ->value('total_value') ?? 0;

        $totalProducts = Inventory::distinct('product_id')->count();
        $lowStockProducts = Inventory::whereRaw('quantity <= threshold')->count();
        $outOfStockProducts = Inventory::where('quantity', '<=', 0)->count();

        $variantInventoryValue = VariantInventory::join('locations', 'variant_inventories.location_id', '=', 'locations.id')
            ->selectRaw('SUM(variant_inventories.stock * COALESCE(variant_inventories.cost_per_unit, 0)) as total_value')
            ->value('total_value') ?? 0;

        $totalVariantProducts = VariantInventory::distinct('variant_id')->count();
        $lowStockVariantProducts = VariantInventory::whereRaw('stock <= threshold')->count();
        $outOfStockVariantProducts = VariantInventory::where('stock', '<=', 0)->count();

        return [
            Stat::make(__('locations.total_inventory_value'), '€' . number_format($totalInventoryValue + $variantInventoryValue, 2))
                ->description(__('locations.total_inventory_value_description'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),

            Stat::make(__('locations.total_products'), $totalProducts + $totalVariantProducts)
                ->description(__('locations.total_products_description'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make(__('locations.low_stock_products'), $lowStockProducts + $lowStockVariantProducts)
                ->description(__('locations.low_stock_products_description'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make(__('locations.out_of_stock_products'), $outOfStockProducts + $outOfStockVariantProducts)
                ->description(__('locations.out_of_stock_products_description'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
