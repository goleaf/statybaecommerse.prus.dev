<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\Address;
use App\Enums\AddressType;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
/**
 * AddressStatsWidget
 * 
 * Filament v4 widget for AddressStatsWidget dashboard display with real-time data and interactive features.
 * 
 */
final class AddressStatsWidget extends BaseWidget
{
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        $totalAddresses = Address::count();
        $activeAddresses = Address::where('is_active', true)->count();
        $defaultAddresses = Address::where('is_default', true)->count();
        $billingAddresses = Address::where('is_billing', true)->count();
        $shippingAddresses = Address::where('is_shipping', true)->count();
        $addressesThisMonth = Address::whereMonth('created_at', now()->month)->count();
        $addressesLastMonth = Address::whereMonth('created_at', now()->subMonth()->month)->count();
        $monthlyGrowth = $addressesLastMonth > 0 ? round(($addressesThisMonth - $addressesLastMonth) / $addressesLastMonth * 100, 1) : 0;
        $topCountries = Address::select('country_code', DB::raw('count(*) as count'))->groupBy('country_code')->orderBy('count', 'desc')->limit(1)->first();
        return [Stat::make(__('translations.total_addresses'), $totalAddresses)->description(__('translations.all_addresses_in_system'))->descriptionIcon('heroicon-m-map-pin')->color('primary'), Stat::make(__('translations.active_addresses'), $activeAddresses)->description(__('translations.active_addresses_description'))->descriptionIcon('heroicon-m-check-circle')->color('success'), Stat::make(__('translations.default_addresses'), $defaultAddresses)->description(__('translations.default_addresses_description'))->descriptionIcon('heroicon-m-star')->color('warning'), Stat::make(__('translations.billing_addresses'), $billingAddresses)->description(__('translations.billing_addresses_description'))->descriptionIcon('heroicon-m-credit-card')->color('info'), Stat::make(__('translations.shipping_addresses'), $shippingAddresses)->description(__('translations.shipping_addresses_description'))->descriptionIcon('heroicon-m-truck')->color('success'), Stat::make(__('translations.new_addresses_this_month'), $addressesThisMonth)->description($monthlyGrowth >= 0 ? __('translations.growth_positive', ['percent' => abs($monthlyGrowth)]) : __('translations.growth_negative', ['percent' => abs($monthlyGrowth)]))->descriptionIcon($monthlyGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')->color($monthlyGrowth >= 0 ? 'success' : 'danger'), Stat::make(__('translations.top_country'), $topCountries?->country_code ?? 'N/A')->description($topCountries ? __('translations.addresses_count', ['count' => $topCountries->count]) : __('translations.no_data'))->descriptionIcon('heroicon-m-globe-alt')->color('secondary')];
    }
}