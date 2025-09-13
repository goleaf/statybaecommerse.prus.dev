<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Zone;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class ZoneStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalZones = Zone::count();
        $activeZones = Zone::where('is_active', true)->count();
        $enabledZones = Zone::where('is_enabled', true)->count();
        $defaultZones = Zone::where('is_default', true)->count();

        $shippingZones = Zone::where('type', 'shipping')->count();
        $taxZones = Zone::where('type', 'tax')->count();
        $paymentZones = Zone::where('type', 'payment')->count();
        $deliveryZones = Zone::where('type', 'delivery')->count();
        $generalZones = Zone::where('type', 'general')->count();

        $zonesWithCountries = Zone::has('countries')->count();
        $zonesWithFreeShipping = Zone::whereNotNull('free_shipping_threshold')->count();

        $averageTaxRate = Zone::where('tax_rate', '>', 0)->avg('tax_rate') ?? 0;
        $totalShippingCost = Zone::sum('shipping_rate') ?? 0;

        $thisMonthZones = Zone::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            Stat::make(__('zones.total_zones'), $totalZones)
                ->description(__('zones.all_zones'))
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('primary'),
            Stat::make(__('zones.active_zones'), $activeZones)
                ->description(__('zones.available_zones'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make(__('zones.enabled_zones'), $enabledZones)
                ->description(__('zones.enabled_zones_desc'))
                ->descriptionIcon('heroicon-m-power')
                ->color('info'),
            Stat::make(__('zones.default_zones'), $defaultZones)
                ->description(__('zones.default_zones_desc'))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
            Stat::make(__('zones.shipping_zones'), $shippingZones)
                ->description(__('zones.shipping_zone_count'))
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),
            Stat::make(__('zones.tax_zones'), $taxZones)
                ->description(__('zones.tax_zone_count'))
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning'),
            Stat::make(__('zones.payment_zones'), $paymentZones)
                ->description(__('zones.payment_zone_count'))
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('primary'),
            Stat::make(__('zones.delivery_zones'), $deliveryZones)
                ->description(__('zones.delivery_zone_count'))
                ->descriptionIcon('heroicon-m-home')
                ->color('success'),
            Stat::make(__('zones.general_zones'), $generalZones)
                ->description(__('zones.general_zone_count'))
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('gray'),
            Stat::make(__('zones.zones_with_countries'), $zonesWithCountries)
                ->description(__('zones.zones_with_countries_desc'))
                ->descriptionIcon('heroicon-m-flag')
                ->color('info'),
            Stat::make(__('zones.zones_with_free_shipping'), $zonesWithFreeShipping)
                ->description(__('zones.zones_with_free_shipping_desc'))
                ->descriptionIcon('heroicon-m-gift')
                ->color('success'),
            Stat::make(__('zones.average_tax_rate'), number_format($averageTaxRate, 2).'%')
                ->description(__('zones.average_tax_rate_desc'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),
            Stat::make(__('zones.total_shipping_cost'), '€'.number_format($totalShippingCost, 2))
                ->description(__('zones.total_shipping_cost_desc'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('primary'),
            Stat::make(__('zones.this_month_zones'), $thisMonthZones)
                ->description(__('zones.created_this_month'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('gray'),
        ];
    }
}
