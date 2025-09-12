<?php declare(strict_types=1);

namespace App\Filament\Resources\PriceListResource\Pages;

use App\Filament\Resources\PriceListResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListPriceLists extends ListRecords
{
    protected static string $resource = PriceListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('admin.price_lists.create_price_list')),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('admin.common.all'))
                ->badge(PriceListResource::getEloquentQuery()->count()),

            'active' => Tab::make(__('admin.price_lists.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_enabled', true))
                ->badge(PriceListResource::getEloquentQuery()->where('is_enabled', true)->count()),

            'default' => Tab::make(__('admin.price_lists.tabs.default'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_default', true))
                ->badge(PriceListResource::getEloquentQuery()->where('is_default', true)->count()),

            'auto_apply' => Tab::make(__('admin.price_lists.tabs.auto_apply'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('auto_apply', true))
                ->badge(PriceListResource::getEloquentQuery()->where('auto_apply', true)->count()),

            'expired' => Tab::make(__('admin.price_lists.tabs.expired'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('ends_at', '<', now()))
                ->badge(PriceListResource::getEloquentQuery()->where('ends_at', '<', now())->count()),

            'upcoming' => Tab::make(__('admin.price_lists.tabs.upcoming'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('starts_at', '>', now()))
                ->badge(PriceListResource::getEloquentQuery()->where('starts_at', '>', now())->count()),
        ];
    }
}
