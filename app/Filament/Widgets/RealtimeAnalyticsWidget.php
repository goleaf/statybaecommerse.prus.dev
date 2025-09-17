<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\User;
use App\Models\WishlistItem;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class RealtimeAnalyticsWidget extends BaseWidget
{
    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $now = Carbon::now();
        $lastHour = $now->copy()->subHour();
        $last30Minutes = $now->copy()->subMinutes(30);
        $last15Minutes = $now->copy()->subMinutes(15);
        $last5Minutes = $now->copy()->subMinutes(5);

        // Real-time metrics
        $ordersLastHour = Order::where('created_at', '>=', $lastHour)->count();
        $ordersLast30Min = Order::where('created_at', '>=', $last30Minutes)->count();
        $ordersLast15Min = Order::where('created_at', '>=', $last15Minutes)->count();
        $ordersLast5Min = Order::where('created_at', '>=', $last5Minutes)->count();

        $usersLastHour = User::where('created_at', '>=', $lastHour)->count();
        $usersLast30Min = User::where('created_at', '>=', $last30Minutes)->count();
        $usersLast15Min = User::where('created_at', '>=', $last15Minutes)->count();
        $usersLast5Min = User::where('created_at', '>=', $last5Minutes)->count();

        $pageViewsLastHour = AnalyticsEvent::where('event_type', 'page_view')
            ->where('created_at', '>=', $lastHour)
            ->count();
        $pageViewsLast30Min = AnalyticsEvent::where('event_type', 'page_view')
            ->where('created_at', '>=', $last30Minutes)
            ->count();
        $pageViewsLast15Min = AnalyticsEvent::where('event_type', 'page_view')
            ->where('created_at', '>=', $last15Minutes)
            ->count();
        $pageViewsLast5Min = AnalyticsEvent::where('event_type', 'page_view')
            ->where('created_at', '>=', $last5Minutes)
            ->count();

        $cartAddsLastHour = AnalyticsEvent::where('event_type', 'add_to_cart')
            ->where('created_at', '>=', $lastHour)
            ->count();
        $cartAddsLast30Min = AnalyticsEvent::where('event_type', 'add_to_cart')
            ->where('created_at', '>=', $last30Minutes)
            ->count();
        $cartAddsLast15Min = AnalyticsEvent::where('event_type', 'add_to_cart')
            ->where('created_at', '>=', $last15Minutes)
            ->count();
        $cartAddsLast5Min = AnalyticsEvent::where('event_type', 'add_to_cart')
            ->where('created_at', '>=', $last5Minutes)
            ->count();

        $wishlistAddsLastHour = WishlistItem::where('created_at', '>=', $lastHour)->count();
        $wishlistAddsLast30Min = WishlistItem::where('created_at', '>=', $last30Minutes)->count();
        $wishlistAddsLast15Min = WishlistItem::where('created_at', '>=', $last15Minutes)->count();
        $wishlistAddsLast5Min = WishlistItem::where('created_at', '>=', $last5Minutes)->count();

        $searchesLastHour = AnalyticsEvent::where('event_type', 'search')
            ->where('created_at', '>=', $lastHour)
            ->count();
        $searchesLast30Min = AnalyticsEvent::where('event_type', 'search')
            ->where('created_at', '>=', $last30Minutes)
            ->count();
        $searchesLast15Min = AnalyticsEvent::where('event_type', 'search')
            ->where('created_at', '>=', $last15Minutes)
            ->count();
        $searchesLast5Min = AnalyticsEvent::where('event_type', 'search')
            ->where('created_at', '>=', $last5Minutes)
            ->count();

        return [
            // Orders Real-time
            Stat::make(__('translations.orders_last_hour'), \Illuminate\Support\Number::format($ordersLastHour))
                ->description(__('translations.last_30min') . ': ' . \Illuminate\Support\Number::format($ordersLast30Min))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),
            Stat::make(__('translations.orders_last_15min'), \Illuminate\Support\Number::format($ordersLast15Min))
                ->description(__('translations.last_5min') . ': ' . \Illuminate\Support\Number::format($ordersLast5Min))
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),
            // Users Real-time
            Stat::make(__('translations.users_last_hour'), \Illuminate\Support\Number::format($usersLastHour))
                ->description(__('translations.last_30min') . ': ' . \Illuminate\Support\Number::format($usersLast30Min))
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make(__('translations.users_last_15min'), \Illuminate\Support\Number::format($usersLast15Min))
                ->description(__('translations.last_5min') . ': ' . \Illuminate\Support\Number::format($usersLast5Min))
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('warning'),
            // Page Views Real-time
            Stat::make(__('translations.page_views_last_hour'), \Illuminate\Support\Number::format($pageViewsLastHour))
                ->description(__('translations.last_30min') . ': ' . \Illuminate\Support\Number::format($pageViewsLast30Min))
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),
            Stat::make(__('translations.page_views_last_15min'), \Illuminate\Support\Number::format($pageViewsLast15Min))
                ->description(__('translations.last_5min') . ': ' . \Illuminate\Support\Number::format($pageViewsLast5Min))
                ->descriptionIcon('heroicon-m-eye')
                ->color('primary'),
            // Cart Adds Real-time
            Stat::make(__('translations.cart_adds_last_hour'), \Illuminate\Support\Number::format($cartAddsLastHour))
                ->description(__('translations.last_30min') . ': ' . \Illuminate\Support\Number::format($cartAddsLast30Min))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),
            Stat::make(__('translations.cart_adds_last_15min'), \Illuminate\Support\Number::format($cartAddsLast15Min))
                ->description(__('translations.last_5min') . ': ' . \Illuminate\Support\Number::format($cartAddsLast5Min))
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
            // Wishlist Adds Real-time
            Stat::make(__('translations.wishlist_adds_last_hour'), \Illuminate\Support\Number::format($wishlistAddsLastHour))
                ->description(__('translations.last_30min') . ': ' . \Illuminate\Support\Number::format($wishlistAddsLast30Min))
                ->descriptionIcon('heroicon-m-heart')
                ->color('danger'),
            Stat::make(__('translations.wishlist_adds_last_15min'), \Illuminate\Support\Number::format($wishlistAddsLast15Min))
                ->description(__('translations.last_5min') . ': ' . \Illuminate\Support\Number::format($wishlistAddsLast5Min))
                ->descriptionIcon('heroicon-m-heart')
                ->color('info'),
            // Searches Real-time
            Stat::make(__('translations.searches_last_hour'), \Illuminate\Support\Number::format($searchesLastHour))
                ->description(__('translations.last_30min') . ': ' . \Illuminate\Support\Number::format($searchesLast30Min))
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color('primary'),
            Stat::make(__('translations.searches_last_15min'), \Illuminate\Support\Number::format($searchesLast15Min))
                ->description(__('translations.last_5min') . ': ' . \Illuminate\Support\Number::format($searchesLast5Min))
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color('success'),
        ];
    }
}
