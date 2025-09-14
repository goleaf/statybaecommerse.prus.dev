<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * OrderStatsWidget
 * 
 * Filament widget for admin panel dashboard.
 */
class OrderStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $processingOrders = Order::where('status', 'processing')->count();
        $shippedOrders = Order::where('status', 'shipped')->count();
        $deliveredOrders = Order::where('status', 'delivered')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();

        $totalRevenue = Order::whereIn('status', ['delivered', 'completed'])
            ->sum('total');

        $averageOrderValue = Order::whereIn('status', ['delivered', 'completed'])
            ->avg('total');

        $todayOrders = Order::whereDate('created_at', today())->count();
        $thisWeekOrders = Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $thisMonthOrders = Order::whereMonth('created_at', now()->month)->count();

        return [
            Stat::make('orders.stats.total_orders', $totalOrders)
                ->description('orders.stats.all_time')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('orders.stats.pending_orders', $pendingOrders)
                ->description('orders.stats.need_attention')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('orders.stats.processing_orders', $processingOrders)
                ->description('orders.stats.in_progress')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('info'),

            Stat::make('orders.stats.shipped_orders', $shippedOrders)
                ->description('orders.stats.in_transit')
                ->descriptionIcon('heroicon-m-truck')
                ->color('success'),

            Stat::make('orders.stats.delivered_orders', $deliveredOrders)
                ->description('orders.stats.completed_deliveries')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('orders.stats.completed_orders', $completedOrders)
                ->description('orders.stats.fully_completed')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('orders.stats.cancelled_orders', $cancelledOrders)
                ->description('orders.stats.cancelled')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('orders.stats.total_revenue', '€'.number_format($totalRevenue, 2))
                ->description('orders.stats.lifetime_revenue')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),

            Stat::make('orders.stats.average_order_value', '€'.number_format($averageOrderValue, 2))
                ->description('orders.stats.per_order')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make('orders.stats.today_orders', $todayOrders)
                ->description('orders.stats.today')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('orders.stats.this_week_orders', $thisWeekOrders)
                ->description('orders.stats.this_week')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('orders.stats.this_month_orders', $thisMonthOrders)
                ->description('orders.stats.this_month')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success'),
        ];
    }
}
