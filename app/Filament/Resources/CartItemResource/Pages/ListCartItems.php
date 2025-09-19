<?php

declare(strict_types=1);

namespace App\Filament\Resources\CartItemResource\Pages;

use App\Filament\Resources\CartItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListCartItems extends ListRecords
{
    protected static string $resource = CartItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('cart_items.tabs.all')),
            
            'active' => Tab::make(__('cart_items.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
            
            'saved' => Tab::make(__('cart_items.tabs.saved'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_saved_for_later', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_saved_for_later', true)->count()),
            
            'low_stock' => Tab::make(__('cart_items.tabs.low_stock'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('product.inventories', function ($q) {
                    $q->where('quantity', '<=', 10);
                }))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereHas('product.inventories', function ($q) {
                    $q->where('quantity', '<=', 10);
                })->count()),
            
            'out_of_stock' => Tab::make(__('cart_items.tabs.out_of_stock'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('product.inventories', function ($q) {
                    $q->where('quantity', '=', 0);
                }))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereHas('product.inventories', function ($q) {
                    $q->where('quantity', '=', 0);
                })->count()),
            
            'recent' => Tab::make(__('cart_items.tabs.recent'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(7)))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('created_at', '>=', now()->subDays(7))->count()),
            
            'abandoned' => Tab::make(__('cart_items.tabs.abandoned'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('updated_at', '<', now()->subDays(3)))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('updated_at', '<', now()->subDays(3))->count()),
        ];
    }
}
