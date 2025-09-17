<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductHistoryResource\Pages;

use App\Filament\Resources\ProductHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListProductHistories extends ListRecords
{
    protected static string $resource = ProductHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('product_histories.tabs.all')),
            
            'price_changes' => Tab::make(__('product_histories.tabs.price_changes'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('action', 'price_changed'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('action', 'price_changed')->count()),
            
            'stock_changes' => Tab::make(__('product_histories.tabs.stock_changes'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('action', 'stock_changed'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('action', 'stock_changed')->count()),
            
            'status_changes' => Tab::make(__('product_histories.tabs.status_changes'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('action', 'status_changed'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('action', 'status_changed')->count()),
            
            'created' => Tab::make(__('product_histories.tabs.created'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('action', 'created'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('action', 'created')->count()),
            
            'updated' => Tab::make(__('product_histories.tabs.updated'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('action', 'updated'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('action', 'updated')->count()),
            
            'deleted' => Tab::make(__('product_histories.tabs.deleted'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('action', 'deleted'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('action', 'deleted')->count()),
            
            'today' => Tab::make(__('product_histories.tabs.today'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today()))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereDate('created_at', today())->count()),
            
            'this_week' => Tab::make(__('product_histories.tabs.this_week'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()),
        ];
    }
}
