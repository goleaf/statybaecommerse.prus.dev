<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListStocks extends ListRecords
{
    protected static string $resource = StockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('stocks.tabs.all')),
            
            'in_stock' => Tab::make(__('stocks.tabs.in_stock'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('quantity', '>', 10))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('quantity', '>', 10)->count()),
            
            'low_stock' => Tab::make(__('stocks.tabs.low_stock'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('quantity', '<=', 10)->where('quantity', '>', 0))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('quantity', '<=', 10)->where('quantity', '>', 0)->count()),
            
            'out_of_stock' => Tab::make(__('stocks.tabs.out_of_stock'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('quantity', '=', 0))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('quantity', '=', 0)->count()),
            
            'reserved' => Tab::make(__('stocks.tabs.reserved'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('reserved_quantity', '>', 0))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('reserved_quantity', '>', 0)->count()),
            
            'available' => Tab::make(__('stocks.tabs.available'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereRaw('quantity - reserved_quantity > 0'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereRaw('quantity - reserved_quantity > 0')->count()),
            
            'active' => Tab::make(__('stocks.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
        ];
    }
}
