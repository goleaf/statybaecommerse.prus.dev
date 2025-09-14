<?php

declare(strict_types=1);

namespace App\Filament\Resources\CountryResource\Widgets;

use App\Models\Country;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class CountryDetailsWidget extends BaseWidget
{
    public ?Country $record = null;

    protected function getStats(): array
    {
        if (!$this->record) {
            return [];
        }

        $addressesCount = $this->record->addresses()->count();
        $citiesCount = $this->record->cities()->count();
        $regionsCount = $this->record->regions()->count();
        $usersCount = $this->record->users()->count();

        return [
            Stat::make('Addresses', $addressesCount)
                ->description('Total addresses in this country')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('primary'),

            Stat::make('Cities', $citiesCount)
                ->description('Total cities in this country')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('success'),

            Stat::make('Regions', $regionsCount)
                ->description('Total regions in this country')
                ->descriptionIcon('heroicon-m-map')
                ->color('info'),

            Stat::make('Users', $usersCount)
                ->description('Users from this country')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
        ];
    }
}
