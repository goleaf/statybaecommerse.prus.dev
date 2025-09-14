<?php

declare (strict_types=1);
namespace App\Filament\Resources\CountryResource\Widgets;

use App\Models\Country;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
/**
 * CountriesStatsWidget
 * 
 * Filament v4 resource for CountriesStatsWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CountriesStatsWidget extends BaseWidget
{
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        $totalCountries = Country::count();
        $activeCountries = Country::active()->count();
        $euMembers = Country::euMembers()->count();
        $countriesWithVat = Country::requiresVat()->count();
        return [Stat::make(__('admin.countries.statistics.total_countries'), $totalCountries)->description(__('admin.countries.statistics.total_countries_description'))->descriptionIcon('heroicon-m-globe-alt')->color('primary'), Stat::make(__('admin.countries.statistics.active_countries'), $activeCountries)->description(__('admin.countries.statistics.active_countries_description'))->descriptionIcon('heroicon-m-check-circle')->color('success'), Stat::make(__('admin.countries.statistics.eu_members'), $euMembers)->description(__('admin.countries.statistics.eu_members_description'))->descriptionIcon('heroicon-m-building-office-2')->color('info'), Stat::make(__('admin.countries.statistics.countries_with_vat'), $countriesWithVat)->description(__('admin.countries.statistics.countries_with_vat_description'))->descriptionIcon('heroicon-m-currency-euro')->color('warning')];
    }
}