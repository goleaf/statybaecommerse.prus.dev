<?php

declare(strict_types=1);

namespace App\Filament\Resources\CartItemResource\Widgets;

use App\Models\CartItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class CartItemsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalCartItems = CartItem::count();
        $activeCartItems = CartItem::where('is_active', true)->count();
        $savedForLater = CartItem::where('is_saved_for_later', true)->count();
        $totalValue = CartItem::join('products', 'cart_items.product_id', '=', 'products.id')
            ->join('prices', 'products.id', '=', 'prices.product_id')
            ->selectRaw('SUM(cart_items.quantity * prices.price) as total_value')
            ->value('total_value') ?? 0;

        return [
            Stat::make(__('cart_items.stats.total_cart_items'), $totalCartItems)
                ->description(__('cart_items.stats.total_cart_items_description'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),

            Stat::make(__('cart_items.stats.active_cart_items'), $activeCartItems)
                ->description(__('cart_items.stats.active_cart_items_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('cart_items.stats.saved_for_later'), $savedForLater)
                ->description(__('cart_items.stats.saved_for_later_description'))
                ->descriptionIcon('heroicon-m-bookmark')
                ->color('info'),

            Stat::make(__('cart_items.stats.total_value'), '€' . number_format($totalValue, 2))
                ->description(__('cart_items.stats.total_value_description'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('warning'),
        ];
    }
}
