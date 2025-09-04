<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

final class AdvancedAnalyticsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $totalRevenue = Order::where('status', 'completed')->sum('total');
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalCustomers = User::where('is_admin', false)->count();
        
        $lastMonthRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [now()->subMonth(), now()])
            ->sum('total');
        
        $previousMonthRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [now()->subMonths(2), now()->subMonth()])
            ->sum('total');
            
        $revenueChange = $previousMonthRevenue > 0 
            ? (($lastMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100 
            : 0;

        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        
        $newCustomersThisMonth = User::where('is_admin', false)
            ->whereBetween('created_at', [now()->startOfMonth(), now()])
            ->count();

        return [
            Stat::make(__('admin.stats.total_revenue'), Number::currency($totalRevenue, 'EUR'))
                ->description($revenueChange >= 0 ? '+' . number_format($revenueChange, 1) . '% from last month' : number_format($revenueChange, 1) . '% from last month')
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make(__('admin.stats.total_orders'), number_format($totalOrders))
                ->description(__('admin.stats.avg_order_value') . ': ' . Number::currency($avgOrderValue, 'EUR'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info')
                ->chart([15, 4, 10, 2, 12, 4, 12]),

            Stat::make(__('admin.stats.total_products'), number_format($totalProducts))
                ->description(__('admin.stats.active_products'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('warning'),

            Stat::make(__('admin.stats.total_customers'), number_format($totalCustomers))
                ->description($newCustomersThisMonth . ' ' . __('admin.stats.new_this_month'))
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
        ];
    }
}
