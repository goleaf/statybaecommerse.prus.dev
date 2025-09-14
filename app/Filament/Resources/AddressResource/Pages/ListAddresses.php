<?php

declare (strict_types=1);
namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
/**
 * ListAddresses
 * 
 * Filament v4 resource for ListAddresses management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ListAddresses extends ListRecords
{
    protected static string $resource = AddressResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
    /**
     * Handle getTabs functionality with proper error handling.
     * @return array
     */
    public function getTabs(): array
    {
        return ['all' => Tab::make(__('translations.all'))->icon('heroicon-o-list-bullet'), 'default' => Tab::make(__('translations.default_addresses'))->icon('heroicon-o-star')->modifyQueryUsing(fn(Builder $query) => $query->where('is_default', true))->badge(fn() => $this->getModel()::where('is_default', true)->count()), 'billing' => Tab::make(__('translations.billing_addresses'))->icon('heroicon-o-credit-card')->modifyQueryUsing(fn(Builder $query) => $query->where('is_billing', true))->badge(fn() => $this->getModel()::where('is_billing', true)->count()), 'shipping' => Tab::make(__('translations.shipping_addresses'))->icon('heroicon-o-truck')->modifyQueryUsing(fn(Builder $query) => $query->where('is_shipping', true))->badge(fn() => $this->getModel()::where('is_shipping', true)->count()), 'active' => Tab::make(__('translations.active'))->icon('heroicon-o-check-circle')->modifyQueryUsing(fn(Builder $query) => $query->where('is_active', true))->badge(fn() => $this->getModel()::where('is_active', true)->count()), 'inactive' => Tab::make(__('translations.inactive'))->icon('heroicon-o-x-circle')->modifyQueryUsing(fn(Builder $query) => $query->where('is_active', false))->badge(fn() => $this->getModel()::where('is_active', false)->count())];
    }
}