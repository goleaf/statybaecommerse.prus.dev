<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceListResource\Pages;

use App\Filament\Resources\PriceListResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListPriceLists extends ListRecords
{
    protected static string $resource = PriceListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('price_lists.tabs.all')),

            'active' => Tab::make(__('price_lists.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),

            'public' => Tab::make(__('price_lists.tabs.public'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_public', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_public', true)->count()),

            'default' => Tab::make(__('price_lists.tabs.default'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_default', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_default', true)->count()),

            'wholesale' => Tab::make(__('price_lists.tabs.wholesale'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'wholesale'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'wholesale')->count()),

            'retail' => Tab::make(__('price_lists.tabs.retail'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'retail'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'retail')->count()),

            'promotional' => Tab::make(__('price_lists.tabs.promotional'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'promotional'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'promotional')->count()),
        ];
    }
}
