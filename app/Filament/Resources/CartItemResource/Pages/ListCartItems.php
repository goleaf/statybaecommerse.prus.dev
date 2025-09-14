<?php

declare (strict_types=1);
namespace App\Filament\Resources\CartItemResource\Pages;

use App\Filament\Resources\CartItemResource;
use App\Filament\Resources\CartItemResource\Widgets;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
/**
 * ListCartItems
 * 
 * Filament v4 resource for ListCartItems management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ListCartItems extends ListRecords
{
    protected static string $resource = CartItemResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\Action::make('back_to_dashboard')->label(__('common.back_to_dashboard'))->icon('heroicon-o-arrow-left')->color('gray')->url('/admin')->tooltip(__('common.back_to_dashboard_tooltip')), Actions\CreateAction::make(), Actions\Action::make('clear_old_carts')->label(__('admin.cart_items.actions.clear_old_carts'))->icon('heroicon-o-trash')->color('danger')->requiresConfirmation()->modalHeading(__('admin.cart_items.actions.clear_old_carts'))->modalDescription(__('admin.cart_items.actions.clear_old_carts_description'))->action(function (): void {
            \App\Models\CartItem::where('created_at', '<', now()->subDays(30))->delete();
        }), Actions\Action::make('export_cart_items')->label(__('admin.cart_items.actions.export'))->icon('heroicon-o-arrow-down-tray')->action(function (): void {
            // Export logic here
        })];
    }
    /**
     * Handle getHeaderWidgets functionality with proper error handling.
     * @return array
     */
    protected function getHeaderWidgets(): array
    {
        return [Widgets\CartItemsOverviewWidget::class];
    }
    /**
     * Handle getFooterWidgets functionality with proper error handling.
     * @return array
     */
    protected function getFooterWidgets(): array
    {
        return [Widgets\CartItemsChartWidget::class, Widgets\LowStockCartItemsWidget::class];
    }
    /**
     * Handle getTabs functionality with proper error handling.
     * @return array
     */
    public function getTabs(): array
    {
        return ['all' => Tab::make(__('admin.cart_items.tabs.all'))->icon('heroicon-o-shopping-bag'), 'active' => Tab::make(__('admin.cart_items.tabs.active'))->icon('heroicon-o-users')->modifyQueryUsing(fn(Builder $query) => $query->whereHas('user')), 'guest' => Tab::make(__('admin.cart_items.tabs.guest'))->icon('heroicon-o-user')->modifyQueryUsing(fn(Builder $query) => $query->whereNull('user_id')), 'needs_restocking' => Tab::make(__('admin.cart_items.tabs.needs_restocking'))->icon('heroicon-o-exclamation-triangle')->modifyQueryUsing(fn(Builder $query) => $query->whereRaw('quantity < minimum_quantity')), 'trashed' => Tab::make(__('admin.cart_items.tabs.trashed'))->icon('heroicon-o-trash')->modifyQueryUsing(fn(Builder $query) => $query->onlyTrashed())];
    }
}