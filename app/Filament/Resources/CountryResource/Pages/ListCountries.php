<?php

declare (strict_types=1);
namespace App\Filament\Resources\CountryResource\Pages;

use App\Filament\Resources\CountryResource;
use App\Filament\Resources\CountryResource\Widgets\CountriesByRegionWidget;
use App\Filament\Resources\CountryResource\Widgets\CountriesStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
/**
 * ListCountries
 * 
 * Filament v4 resource for ListCountries management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ListCountries extends ListRecords
{
    protected static string $resource = CountryResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
    /**
     * Handle getHeaderWidgets functionality with proper error handling.
     * @return array
     */
    protected function getHeaderWidgets(): array
    {
        return [CountriesStatsWidget::class];
    }
    /**
     * Handle getFooterWidgets functionality with proper error handling.
     * @return array
     */
    protected function getFooterWidgets(): array
    {
        return [CountriesByRegionWidget::class];
    }
    /**
     * Handle getTabs functionality with proper error handling.
     * @return array
     */
    public function getTabs(): array
    {
        return ['all' => Tab::make('All Countries')->icon('heroicon-o-globe-alt'), 'active' => Tab::make('Active Countries')->icon('heroicon-o-check-circle')->modifyQueryUsing(fn(Builder $query) => $query->where('is_active', true)), 'eu_members' => Tab::make('EU Members')->icon('heroicon-o-building-office-2')->modifyQueryUsing(fn(Builder $query) => $query->where('is_eu_member', true)), 'with_vat' => Tab::make('With VAT')->icon('heroicon-o-currency-euro')->modifyQueryUsing(fn(Builder $query) => $query->where('requires_vat', true)), 'europe' => Tab::make('Europe')->icon('heroicon-o-map')->modifyQueryUsing(fn(Builder $query) => $query->where('region', 'Europe')), 'asia' => Tab::make('Asia')->icon('heroicon-o-map')->modifyQueryUsing(fn(Builder $query) => $query->where('region', 'Asia')), 'africa' => Tab::make('Africa')->icon('heroicon-o-map')->modifyQueryUsing(fn(Builder $query) => $query->where('region', 'Africa')), 'north_america' => Tab::make('North America')->icon('heroicon-o-map')->modifyQueryUsing(fn(Builder $query) => $query->where('region', 'North America')), 'south_america' => Tab::make('South America')->icon('heroicon-o-map')->modifyQueryUsing(fn(Builder $query) => $query->where('region', 'South America')), 'oceania' => Tab::make('Oceania')->icon('heroicon-o-map')->modifyQueryUsing(fn(Builder $query) => $query->where('region', 'Oceania')), 'trashed' => Tab::make('Trashed')->icon('heroicon-o-trash')->modifyQueryUsing(fn(Builder $query) => $query->onlyTrashed())];
    }
}