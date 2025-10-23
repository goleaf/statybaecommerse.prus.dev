<?php

declare(strict_types=1);

namespace App\Filament\Resources\CartItemResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\CartItemResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

final class ListCartItems extends BaseListRecords
{
    use HasResizableColumns;
    use HasWidgetTabs;

    protected static string $resource = CartItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getWidgetTabs(): array
    {
        return [
            'all' => WidgetTab::make(__('cart_items.tabs.all'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->count()),
            'active' => WidgetTab::make(__('cart_items.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),

            'saved' => WidgetTab::make(__('cart_items.tabs.saved'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_saved_for_later', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_saved_for_later', true)->count()),

            'low_stock' => WidgetTab::make(__('cart_items.tabs.low_stock'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('product.inventories', function ($q) {
                    $q->where('quantity', '<=', 10);
                }))
                ->value(fn () => $this->getResource()::getEloquentQuery()->whereHas('product.inventories', function ($q) {
                    $q->where('quantity', '<=', 10);
                })->count()),

            'out_of_stock' => WidgetTab::make(__('cart_items.tabs.out_of_stock'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('product.inventories', function ($q) {
                    $q->where('quantity', '=', 0);
                }))
                ->value(fn () => $this->getResource()::getEloquentQuery()->whereHas('product.inventories', function ($q) {
                    $q->where('quantity', '=', 0);
                })->count()),

            'recent' => WidgetTab::make(__('cart_items.tabs.recent'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(7)))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('created_at', '>=', now()->subDays(7))->count()),

            'abandoned' => WidgetTab::make(__('cart_items.tabs.abandoned'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('updated_at', '<', now()->subDays(3)))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('updated_at', '<', now()->subDays(3))->count()),
        ];
    }
}
