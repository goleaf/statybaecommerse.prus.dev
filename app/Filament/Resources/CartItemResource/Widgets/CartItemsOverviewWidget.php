<?php

declare (strict_types=1);
namespace App\Filament\Resources\CartItemResource\Widgets;

use App\Models\CartItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
/**
 * CartItemsOverviewWidget
 * 
 * Filament v4 resource for CartItemsOverviewWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CartItemsOverviewWidget extends BaseWidget
{
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        $totalCartItems = CartItem::count();
        $activeCartItems = CartItem::whereHas('user')->count();
        $guestCartItems = CartItem::whereNull('user_id')->count();
        $totalValue = CartItem::sum('total_price');
        $needsRestocking = CartItem::whereRaw('quantity < minimum_quantity')->count();
        return [Stat::make(__('admin.cart_items.stats.total_items'), $totalCartItems)->description(__('admin.cart_items.stats.total_items_description'))->descriptionIcon('heroicon-m-shopping-bag')->color('primary'), Stat::make(__('admin.cart_items.stats.active_carts'), $activeCartItems)->description(__('admin.cart_items.stats.active_carts_description'))->descriptionIcon('heroicon-m-users')->color('success'), Stat::make(__('admin.cart_items.stats.guest_carts'), $guestCartItems)->description(__('admin.cart_items.stats.guest_carts_description'))->descriptionIcon('heroicon-m-user')->color('warning'), Stat::make(__('admin.cart_items.stats.total_value'), app_money_format($totalValue))->description(__('admin.cart_items.stats.total_value_description'))->descriptionIcon('heroicon-m-currency-euro')->color('info'), Stat::make(__('admin.cart_items.stats.needs_restocking'), $needsRestocking)->description(__('admin.cart_items.stats.needs_restocking_description'))->descriptionIcon('heroicon-m-exclamation-triangle')->color($needsRestocking > 0 ? 'danger' : 'success')];
    }
}