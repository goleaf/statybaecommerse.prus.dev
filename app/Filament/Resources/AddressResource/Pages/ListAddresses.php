<?php

declare(strict_types=1);

namespace App\Filament\Resources\AddressResource\Pages;

use App\Enums\AddressType;
use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\AddressResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable as SpatieTranslatableListRecords;

final class ListAddresses extends BaseListRecords
{
    use HasResizableColumns;
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
            'all' => WidgetTab::make(__('translations.all_addresses'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->count()),
            'shipping' => WidgetTab::make(__('translations.shipping_addresses'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', AddressType::SHIPPING))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', AddressType::SHIPPING)->count()),
            'billing' => WidgetTab::make(__('translations.billing_addresses'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', AddressType::BILLING))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', AddressType::BILLING)->count()),
            'home' => WidgetTab::make(__('translations.home_addresses'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', AddressType::HOME))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', AddressType::HOME)->count()),
            'work' => WidgetTab::make(__('translations.work_addresses'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', AddressType::WORK))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', AddressType::WORK)->count()),
            'default' => WidgetTab::make(__('translations.default_addresses'))
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
