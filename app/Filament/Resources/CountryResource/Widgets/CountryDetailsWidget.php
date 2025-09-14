<?php

declare (strict_types=1);
namespace App\Filament\Resources\CountryResource\Widgets;

use App\Models\Country;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
/**
 * CountryDetailsWidget
 * 
 * Filament v4 resource for CountryDetailsWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property Country|null $record
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CountryDetailsWidget extends BaseWidget
{
    public ?Country $record = null;
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        if (!$this->record) {
            return [];
        }
        $addressesCount = $this->record->addresses()->count();
        $citiesCount = $this->record->cities()->count();
        $regionsCount = $this->record->regions()->count();
        $usersCount = $this->record->users()->count();
        return [Stat::make('Addresses', $addressesCount)->description('Total addresses in this country')->descriptionIcon('heroicon-m-map-pin')->color('primary'), Stat::make('Cities', $citiesCount)->description('Total cities in this country')->descriptionIcon('heroicon-m-building-office')->color('success'), Stat::make('Regions', $regionsCount)->description('Total regions in this country')->descriptionIcon('heroicon-m-map')->color('info'), Stat::make('Users', $usersCount)->description('Users from this country')->descriptionIcon('heroicon-m-users')->color('warning')];
    }
}