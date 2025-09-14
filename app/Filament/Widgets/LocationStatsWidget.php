<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Location;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final /**
 * LocationStatsWidget
 * 
 * Filament widget for admin panel dashboard.
 */
class LocationStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalLocations = Location::count();
        $enabledLocations = Location::enabled()->count();
        $disabledLocations = Location::where('is_enabled', false)->count();
        $warehouseCount = Location::byType('warehouse')->count();
        $storeCount = Location::byType('store')->count();
        $officeCount = Location::byType('office')->count();
        $pickupPointCount = Location::byType('pickup_point')->count();

        return [
            Stat::make(__('locations.total_locations'), $totalLocations)
                ->description(__('locations.total_locations_description'))
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('primary'),

            Stat::make(__('locations.enabled_locations'), $enabledLocations)
                ->description(__('locations.enabled_locations_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('locations.disabled_locations'), $disabledLocations)
                ->description(__('locations.disabled_locations_description'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make(__('locations.warehouse_count'), $warehouseCount)
                ->description(__('locations.warehouse_count_description'))
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('info'),

            Stat::make(__('locations.store_count'), $storeCount)
                ->description(__('locations.store_count_description'))
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('warning'),

            Stat::make(__('locations.office_count'), $officeCount)
                ->description(__('locations.office_count_description'))
                ->descriptionIcon('heroicon-m-building-office')
                ->color('gray'),

            Stat::make(__('locations.pickup_point_count'), $pickupPointCount)
                ->description(__('locations.pickup_point_count_description'))
                ->descriptionIcon('heroicon-m-truck')
                ->color('success'),
        ];
    }
}
