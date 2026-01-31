<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\InventoryService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryStats extends BaseWidget
{
    protected function getStats(): array
    {
        $summary = app(InventoryService::class)->getInventorySummary();

        return [
            Stat::make(__('admin.inventory_management.total_products'), number_format($summary['total_products']))
                ->color('gray'),
            Stat::make(__('admin.inventory_management.in_stock'), number_format($summary['in_stock']))
                ->color('success'),
            Stat::make(__('admin.inventory_management.low_stock'), number_format($summary['low_stock']))
                ->url(route('filament.admin.resources.products.index', ['tableFilters' => ['stock_status' => ['value' => 'low_stock']]]))
                ->color('warning'),
            Stat::make(__('admin.inventory_management.out_of_stock'), number_format($summary['out_of_stock']))
                ->url(route('filament.admin.resources.products.index', ['tableFilters' => ['stock_status' => ['value' => 'out_of_stock']]]))
                ->color('danger'),
            Stat::make(__('admin.inventory_management.tracked_products'), number_format($summary['tracked_products']))
                ->url(route('filament.admin.resources.products.index', ['tableFilters' => ['manage_stock' => ['value' => '1']]]))
                ->color('info'),
        ];
    }
}
