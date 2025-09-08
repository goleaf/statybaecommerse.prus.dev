<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\DatabaseDateService;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB as Database;

final class EnhancedDashboardWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        // Get time-based data for better insights
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        // Revenue Analytics
        $todayRevenue = Order::where('created_at', '>=', $today)->sum('total');
        $thisMonthRevenue = Order::where('created_at', '>=', $thisMonth)->sum('total');
        $lastMonthRevenue = Order::whereBetween('created_at', [$lastMonth, $thisMonth])->sum('total');
        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        // Order Analytics
        $todayOrders = Order::where('created_at', '>=', $today)->count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $thisWeekOrders = Order::where('created_at', '>=', $thisWeek)->count();
        $lastWeekOrders = Order::whereBetween('created_at', [
            $thisWeek->copy()->subWeek(),
            $thisWeek
        ])->count();
        $orderGrowth = $lastWeekOrders > 0
            ? round((($thisWeekOrders - $lastWeekOrders) / $lastWeekOrders) * 100, 1)
            : 0;

        // Customer Analytics
        $newCustomersToday = User::where('created_at', '>=', $today)->count();
        $totalActiveCustomers = User::whereHas('orders')->count();
        $customerRetentionRate = User::count() > 0
            ? round(($totalActiveCustomers / User::count()) * 100, 1)
            : 0;

        // Product Analytics
        $totalProducts = Product::where('is_visible', true)->count();
        $lowStockProducts = Product::where('is_visible', true)
            ->where('manage_stock', true)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->count();
        $outOfStockProducts = Product::where('is_visible', true)
            ->where('manage_stock', true)
            ->where('stock_quantity', 0)
            ->count();

        // Get recent revenue trend for charts
        $revenueChart = Order::select(
            Database::raw(DatabaseDateService::dateExpression('created_at') . ' as date'),
            Database::raw('SUM(total) as revenue')
        )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue')
            ->toArray();

        return [
            Stat::make(__('todays_revenue'), '€' . number_format($todayRevenue, 2))
                ->description($revenueGrowth >= 0
                    ? __('revenue_growth_positive', ['percent' => $revenueGrowth])
                    : __('revenue_growth_negative', ['percent' => abs($revenueGrowth)]))
                ->descriptionIcon($revenueGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueGrowth >= 0 ? 'success' : 'danger')
                ->chart($revenueChart),
            Stat::make(__('todays_orders'), (string) $todayOrders)
                ->description($orderGrowth >= 0
                    ? __('order_growth_positive', ['percent' => $orderGrowth])
                    : __('order_growth_negative', ['percent' => abs($orderGrowth)]))
                ->descriptionIcon($orderGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($orderGrowth >= 0 ? 'success' : 'danger'),
            Stat::make(__('new_customers_today'), (string) $newCustomersToday)
                ->description(__('customer_retention_rate', ['rate' => $customerRetentionRate]))
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make(__('inventory_alerts'), (string) $lowStockProducts)
                ->description($outOfStockProducts > 0
                    ? __('out_of_stock_products', ['count' => $outOfStockProducts])
                    : __('inventory_healthy'))
                ->descriptionIcon($lowStockProducts > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($lowStockProducts > 0 ? 'warning' : 'success'),
            Stat::make(__('pending_orders'), (string) $pendingOrders)
                ->description(__('orders_awaiting_processing'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingOrders > 5 ? 'warning' : 'primary'),
            Stat::make(__('total_products'), (string) $totalProducts)
                ->description(__('visible_products_count'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('secondary'),
        ];
    }
}
