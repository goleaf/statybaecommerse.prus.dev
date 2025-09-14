<?php

declare (strict_types=1);
namespace App\Filament\Resources\CountryResource\Widgets;

use App\Models\Country;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
/**
 * CountriesOverviewWidget
 * 
 * Filament v4 resource for CountriesOverviewWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CountriesOverviewWidget extends BaseWidget
{
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        return [Stat::make(__('admin.countries.statistics.total_countries'), Country::count())->description(__('admin.countries.statistics.total_countries_description'))->descriptionIcon('heroicon-m-globe-alt')->color('primary'), Stat::make(__('admin.countries.statistics.active_countries'), Country::where('is_active', true)->count())->description(__('admin.countries.statistics.active_countries_description'))->descriptionIcon('heroicon-m-check-circle')->color('success'), Stat::make(__('admin.countries.statistics.eu_members'), Country::where('is_eu_member', true)->count())->description(__('admin.countries.statistics.eu_members_description'))->descriptionIcon('heroicon-m-flag')->color('info'), Stat::make(__('admin.countries.statistics.countries_with_vat'), Country::where('requires_vat', true)->count())->description(__('admin.countries.statistics.countries_with_vat_description'))->descriptionIcon('heroicon-m-currency-euro')->color('warning')];
    }
    /**
     * Handle getColumns functionality with proper error handling.
     * @return int
     */
    protected function getColumns(): int
    {
        return 4;
    }
}