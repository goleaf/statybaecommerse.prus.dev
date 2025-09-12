<?php declare(strict_types=1);

namespace App\Filament\Resources\LocationResource\Pages;

use App\Filament\Resources\LocationResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

final class ListLocations extends ListRecords
{
    protected static string $resource = LocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_dashboard')
                ->label(__('common.back_to_dashboard'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url('/admin')
                ->tooltip(__('common.back_to_dashboard_tooltip')),
            Actions\CreateAction::make()
                ->label(__('locations.create_location')),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('locations.all_locations'))
                ->icon('heroicon-o-map-pin'),
            'warehouses' => Tab::make(__('locations.warehouses'))
                ->icon('heroicon-o-building-office-2')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'warehouse')),
            'stores' => Tab::make(__('locations.stores'))
                ->icon('heroicon-o-building-storefront')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'store')),
            'offices' => Tab::make(__('locations.offices'))
                ->icon('heroicon-o-building-office')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'office')),
            'pickup_points' => Tab::make(__('locations.pickup_points'))
                ->icon('heroicon-o-map-pin')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'pickup_point')),
            'enabled' => Tab::make(__('locations.enabled_locations'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_enabled', true)),
            'disabled' => Tab::make(__('locations.disabled_locations'))
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_enabled', false)),
        ];
    }
}
