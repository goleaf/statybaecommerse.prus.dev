<?php

declare(strict_types=1);

namespace App\Filament\Resources\AddressResource\Pages;

use App\Enums\AddressType;
use App\Filament\Resources\AddressResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable as SpatieTranslatableListRecords;

final class ListAddresses extends BaseListRecords
{
    use HasWidgetTabs;
    use SpatieTranslatableListRecords; // Track the active locale for listing translated records.

    protected static string $resource = AddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(), // Provide a quick language toggle for the grid view.
            Actions\CreateAction::make(),
        ];
    }

    public function getWidgetTabs(): array
    {
        return [
            'all'      => Tab::make(__('translations.all_addresses')),
            'shipping' => Tab::make(__('translations.shipping_addresses'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', AddressType::SHIPPING->value))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', AddressType::SHIPPING->value)->count()),
            'billing' => Tab::make(__('translations.billing_addresses'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', AddressType::BILLING->value))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', AddressType::BILLING->value)->count()),
            'home' => Tab::make(__('translations.home_addresses'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', AddressType::HOME->value))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', AddressType::HOME->value)->count()),
            'work' => Tab::make(__('translations.work_addresses'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', AddressType::WORK->value))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', AddressType::WORK->value)->count()),
            'default' => Tab::make(__('translations.default_addresses'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_default', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_default', true)->count()),
            'active' => WidgetTab::make(__('translations.active_addresses'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
            'recent' => WidgetTab::make(__('translations.recent_addresses'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(7)))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('created_at', '>=', now()->subDays(7))->count()),
        ];
    }
}
