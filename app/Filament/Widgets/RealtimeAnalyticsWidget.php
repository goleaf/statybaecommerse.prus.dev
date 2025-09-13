<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Campaign;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class RealtimeAnalyticsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();

        // Today's orders
        $todayOrders = Order::where('created_at', '>=', $today)->count();
        $yesterdayOrders = Order::whereBetween('created_at', [$yesterday, $today])->count();
        $ordersChange = $yesterdayOrders > 0 ? (($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100 : 0;

        // Today's revenue
        $todayRevenue = Order::where('created_at', '>=', $today)->sum('total');
        $yesterdayRevenue = Order::whereBetween('created_at', [$yesterday, $today])->sum('total');
        $revenueChange = $yesterdayRevenue > 0 ? (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100 : 0;

        // Active campaigns
        $activeCampaigns = Campaign::active()->count();

        // Total products
        $totalProducts = Product::count();

        return [
            Stat::make(__('analytics.today_orders'), $todayOrders)
                ->description($ordersChange >= 0 ? '+'.number_format($ordersChange, 1).'%' : number_format($ordersChange, 1).'%')
                ->descriptionIcon($ordersChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ordersChange >= 0 ? 'success' : 'danger'),

            Stat::make(__('analytics.today_revenue'), '€'.number_format($todayRevenue, 2))
                ->description($revenueChange >= 0 ? '+'.number_format($revenueChange, 1).'%' : number_format($revenueChange, 1).'%')
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger'),

            Stat::make(__('analytics.active_campaigns'), $activeCampaigns)
                ->description(__('analytics.campaigns_running'))
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('info'),

            Stat::make(__('analytics.total_products'), $totalProducts)
                ->description(__('analytics.products_catalog'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('warning'),
        ];
    }
}
