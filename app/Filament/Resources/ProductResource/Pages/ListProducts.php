<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('products.tabs.all'))
                ->icon('heroicon-o-cube'),

            'published' => Tab::make(__('products.tabs.published'))
                ->icon('heroicon-o-eye')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_visible', true)),

            'draft' => Tab::make(__('products.tabs.draft'))
                ->icon('heroicon-o-document')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_visible', false)),

            'featured' => Tab::make(__('products.tabs.featured'))
                ->icon('heroicon-o-star')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_featured', true)),

            'low_stock' => Tab::make(__('products.tabs.low_stock'))
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereRaw('stock_quantity <= low_stock_threshold')),

            'out_of_stock' => Tab::make(__('products.tabs.out_of_stock'))
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('stock_quantity', '<=', 0)),
        ];
    }
}
