<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Address;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * AddressStatsWidget
 *
 * Widget displaying address statistics for the admin dashboard
 */
final class AddressStatsWidget extends BaseWidget
{
    /**
     * Get stats
     */
    protected function getStats(): array
    {
        $totalAddresses = Address::count();
        $activeAddresses = Address::where('is_active', true)->count();
        $defaultAddresses = Address::where('is_default', true)->count();
        $billingAddresses = Address::where('is_billing', true)->count();
        $shippingAddresses = Address::where('is_shipping', true)->count();
        $companyAddresses = Address::whereNotNull('company_name')->count();

        return [
            Stat::make(__('translations.total_addresses'), $totalAddresses)
                ->description(__('translations.total_addresses_description'))
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('primary'),
            Stat::make(__('translations.active_addresses'), $activeAddresses)
                ->description(__('translations.active_addresses_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make(__('translations.default_addresses'), $defaultAddresses)
                ->description(__('translations.default_addresses_description'))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
            Stat::make(__('translations.billing_addresses'), $billingAddresses)
                ->description(__('translations.billing_addresses_description'))
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('info'),
            Stat::make(__('translations.shipping_addresses'), $shippingAddresses)
                ->description(__('translations.shipping_addresses_description'))
                ->descriptionIcon('heroicon-m-truck')
                ->color('success'),
            Stat::make(__('translations.company_addresses'), $companyAddresses)
                ->description(__('translations.company_addresses_description'))
                ->descriptionIcon('heroicon-m-building-office')
                ->color('gray'),
        ];
    }
}
