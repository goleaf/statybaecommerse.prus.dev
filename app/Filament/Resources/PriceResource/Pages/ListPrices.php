<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceResource\Pages;

use App\Filament\Resources\PriceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListPrices extends ListRecords
{
    protected static string $resource = PriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('prices.tabs.all')),
            
            'active' => Tab::make(__('prices.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
            
            'regular' => Tab::make(__('prices.tabs.regular'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'regular'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'regular')->count()),
            
            'sale' => Tab::make(__('prices.tabs.sale'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'sale'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'sale')->count()),
            
            'wholesale' => Tab::make(__('prices.tabs.wholesale'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'wholesale'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'wholesale')->count()),
            
            'bulk' => Tab::make(__('prices.tabs.bulk'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'bulk'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'bulk')->count()),
            
            'current' => Tab::make(__('prices.tabs.current'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('valid_from', '<=', now())->where(function ($q) {
                    $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
                }))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('valid_from', '<=', now())->where(function ($q) {
                    $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
                })->count()),
            
            'expired' => Tab::make(__('prices.tabs.expired'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('valid_until', '<', now()))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('valid_until', '<', now())->count()),
        ];
    }
}

