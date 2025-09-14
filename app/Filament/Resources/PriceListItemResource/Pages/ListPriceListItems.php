<?php

declare (strict_types=1);
namespace App\Filament\Resources\PriceListItemResource\Pages;

use App\Filament\Resources\PriceListItemResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
/**
 * ListPriceListItems
 * 
 * Filament v4 resource for ListPriceListItems management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ListPriceListItems extends ListRecords
{
    protected static string $resource = PriceListItemResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\Action::make('back_to_dashboard')->label(__('common.back_to_dashboard'))->icon('heroicon-o-arrow-left')->color('gray')->url('/admin')->tooltip(__('common.back_to_dashboard_tooltip')), Actions\CreateAction::make()->label(__('admin.price_list_items.actions.create'))->icon('heroicon-o-plus')];
    }
    /**
     * Handle getTabs functionality with proper error handling.
     * @return array
     */
    public function getTabs(): array
    {
        return ['all' => Tab::make(__('admin.price_list_items.tabs.all'))->icon('heroicon-o-list-bullet'), 'active' => Tab::make(__('admin.price_list_items.tabs.active'))->icon('heroicon-o-check-circle')->modifyQueryUsing(fn(Builder $query) => $query->where('is_active', true)), 'with_discount' => Tab::make(__('admin.price_list_items.tabs.with_discount'))->icon('heroicon-o-tag')->modifyQueryUsing(fn(Builder $query) => $query->whereNotNull('compare_amount')->whereColumn('compare_amount', '>', 'net_amount')), 'valid_now' => Tab::make(__('admin.price_list_items.tabs.valid_now'))->icon('heroicon-o-clock')->modifyQueryUsing(fn(Builder $query) => $query->where('is_active', true)->where(function ($q) {
            $q->whereNull('valid_from')->orWhere('valid_from', '<=', now());
        })->where(function ($q) {
            $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
        }))];
    }
}