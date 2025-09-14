<?php

declare(strict_types=1);

namespace App\Filament\Resources\CityResource\Pages;

use App\Filament\Resources\CityResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

/**
 * ListCities
 * 
 * Filament resource for admin panel management.
 */
class ListCities extends ListRecords
{
    protected static string $resource = CityResource::class;

    public function getTitle(): string
    {
        return __('cities.title');
    }

    public function getSubheading(): ?string
    {
        return __('cities.subtitle');
    }

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
                ->label(__('cities.create'))
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('cities.filter_all'))
                ->icon('heroicon-o-building-office'),

            'enabled' => Tab::make(__('cities.filter_enabled'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_enabled', true))
                ->badge(fn () => $this->getModel()::where('is_enabled', true)->count()),

            'capitals' => Tab::make(__('cities.filter_capital'))
                ->icon('heroicon-o-crown')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_capital', true))
                ->badge(fn () => $this->getModel()::where('is_capital', true)->count()),

            'default' => Tab::make(__('cities.filter_default'))
                ->icon('heroicon-o-star')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_default', true))
                ->badge(fn () => $this->getModel()::where('is_default', true)->count()),

            'with_coordinates' => Tab::make(__('cities.with_coordinates'))
                ->icon('heroicon-o-map-pin')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('latitude')->whereNotNull('longitude'))
                ->badge(fn () => $this->getModel()::whereNotNull('latitude')->whereNotNull('longitude')->count()),

            'trashed' => Tab::make(__('cities.trashed'))
                ->icon('heroicon-o-trash')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badge(fn () => $this->getModel()::onlyTrashed()->count()),
        ];
    }
}
