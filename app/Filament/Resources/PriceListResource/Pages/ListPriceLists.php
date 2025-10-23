<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceListResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\PriceListResource;
use App\Models\PriceList;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListPriceLists extends BaseListRecords
{
    use HasWidgetTabs;

    protected static string $resource = PriceListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getWidgetTabs(): array
    {
        return [
            'all' => WidgetTab::make(__('price_lists.tabs.all'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->count()),
            'active' => WidgetTab::make(__('price_lists.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->active())
                ->value(fn () => $this->getResource()::getEloquentQuery()->active()->count()),

            'active' => Tab::make(__('price_lists.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->active())
                ->badge(fn () => PriceList::query()->active()->count()),

            'enabled' => Tab::make(__('price_lists.tabs.enabled'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_enabled', true))
                ->badge(fn () => PriceList::query()->where('is_enabled', true)->count()),

            'default' => Tab::make(__('price_lists.tabs.default'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_default', true))
                ->badge(fn () => PriceList::query()->where('is_default', true)->count()),

            'auto_apply' => Tab::make(__('price_lists.tabs.auto_apply'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('auto_apply', true))
                ->badge(fn () => PriceList::query()->where('auto_apply', true)->count()),
        ];
    }
}
