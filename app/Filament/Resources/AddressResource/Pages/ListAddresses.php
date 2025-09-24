<?php

declare(strict_types=1);

namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListAddresses extends ListRecords
{
    protected static string $resource = AddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('translations.all_addresses')),
            'shipping' => Tab::make(__('translations.shipping_addresses'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', \App\Enums\AddressType::SHIPPING))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', \App\Enums\AddressType::SHIPPING)->count()),
            'billing' => Tab::make(__('translations.billing_addresses'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', \App\Enums\AddressType::BILLING))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', \App\Enums\AddressType::BILLING)->count()),
            'home' => Tab::make(__('translations.home_addresses'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', \App\Enums\AddressType::HOME))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', \App\Enums\AddressType::HOME)->count()),
            'work' => Tab::make(__('translations.work_addresses'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', \App\Enums\AddressType::WORK))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', \App\Enums\AddressType::WORK)->count()),
            'default' => Tab::make(__('translations.default_addresses'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_default', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_default', true)->count()),
            'active' => Tab::make(__('translations.active_addresses'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
            'recent' => Tab::make(__('translations.recent_addresses'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(7)))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('created_at', '>=', now()->subDays(7))->count()),
        ];
    }
}
