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
                ->modifyQueryUsing(fn (Builder $query) => $query->active())
                ->badge(fn () => $this->getResource()::getEloquentQuery()->active()->count()),

            'default' => Tab::make(__('price_lists.tabs.default'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_default', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_default', true)->count()),

            'auto_apply' => Tab::make(__('price_lists.tabs.auto_apply'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('auto_apply', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('auto_apply', true)->count()),
        ];
    }
}
