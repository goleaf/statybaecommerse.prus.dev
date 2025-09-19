<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductHistoryResource\Pages;

use App\Filament\Resources\ProductHistoryResource;
use Filament\Actions;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

final class ListProductHistories extends ListRecords
{
    protected static string $resource = ProductHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('product_history.tabs.all')),
            'price_changes' => Tab::make(__('product_history.tabs.price_changes'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('action', 'price_changed'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('action', 'price_changed')->count()),
            'stock_changes' => Tab::make(__('product_history.tabs.stock_changes'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('action', 'stock_updated'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('action', 'stock_updated')->count()),
            'status_changes' => Tab::make(__('product_history.tabs.status_changes'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('action', 'status_changed'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('action', 'status_changed')->count()),
            'created' => Tab::make(__('product_history.tabs.created'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('action', 'created'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('action', 'created')->count()),
            'updated' => Tab::make(__('product_history.tabs.updated'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('action', 'updated'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('action', 'updated')->count()),
            'deleted' => Tab::make(__('product_history.tabs.deleted'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('action', 'deleted'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('action', 'deleted')->count()),
            'today' => Tab::make(__('product_history.tabs.today'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereDate('created_at', Carbon::today()))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereDate('created_at', Carbon::today())->count()),
            'this_week' => Tab::make(__('product_history.tabs.this_week'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count()),
        ];
    }
}
