<?php

declare(strict_types=1);

namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListAddresses extends ListRecords
{
    protected static string $resource = AddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('addresses.tabs.all')),
            
            'billing' => Tab::make(__('addresses.tabs.billing'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'billing'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'billing')->count()),
            
            'shipping' => Tab::make(__('addresses.tabs.shipping'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'shipping'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'shipping')->count()),
            
            'both' => Tab::make(__('addresses.tabs.both'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'both'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'both')->count()),
            
            'default' => Tab::make(__('addresses.tabs.default'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_default', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_default', true)->count()),
            
            'active' => Tab::make(__('addresses.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
            
            'recent' => Tab::make(__('addresses.tabs.recent'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(7)))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('created_at', '>=', now()->subDays(7))->count()),
        ];
    }
}
