<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB as Database;
use Illuminate\Support\Number;

final class EcommerceStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static bool $isLazy = false;
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $todayOrders = Order::whereDate('created_at', today())->count();
        $todayRevenue = Order::whereDate('created_at', today())->sum('total');
        $monthlyRevenue = Order::whereMonth('created_at', now()->month)->sum('total');
        $totalCustomers = User::count();
        $averageOrderValue = Order::avg('total') ?? 0;
        $totalProducts = Product::where('is_visible', true)->count();
        $lowStockProducts = Product::where('is_visible', true)
            ->where('manage_stock', true)
            ->where('stock_quantity', '<=', Database::raw('low_stock_threshold'))
            ->count();
        $averageRating = Review::where('is_approved', true)->avg('rating') ?? 0;

        return [
            Stat::make(__('admin.stats.todays_orders'), $todayOrders)
                ->description(__('admin.stats.orders_today'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->chart([7, 12, 15, 18, 22, 14, $todayOrders])
                ->color($todayOrders > 10 ? 'success' : ($todayOrders > 5 ? 'warning' : 'gray')),
            Stat::make(__('admin.stats.todays_revenue'), '€' . Number::format($todayRevenue, precision: 2))
                ->description(__('admin.stats.revenue_today'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->chart([100, 150, 200, 180, 250, 220, $todayRevenue])
                ->color($todayRevenue > 1000 ? 'success' : ($todayRevenue > 500 ? 'warning' : 'gray')),
            Stat::make(__('admin.stats.monthly_revenue'), '€' . Number::format($monthlyRevenue, precision: 2))
                ->description(__('admin.stats.current_month'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->chart([1000, 1200, 1500, 1300, 1800, 1600, $monthlyRevenue])
                ->color('success'),
            Stat::make(__('admin.stats.total_customers'), Number::format($totalCustomers))
                ->description(__('admin.stats.registered_users'))
                ->descriptionIcon('heroicon-m-users')
                ->chart([50, 75, 100, 120, 150, 180, $totalCustomers])
                ->color('primary'),
            Stat::make(__('admin.stats.avg_order_value'), '€' . Number::format($averageOrderValue, precision: 2))
                ->description(__('admin.stats.average_per_order'))
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
            Stat::make(__('admin.stats.total_products'), Number::format($totalProducts))
                ->description(__('admin.stats.active_products'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),
            Stat::make(__('admin.stats.low_stock_products'), Number::format($lowStockProducts))
                ->description(__('admin.stats.need_restock'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockProducts > 10 ? 'danger' : ($lowStockProducts > 5 ? 'warning' : 'success')),
            Stat::make(__('admin.stats.avg_rating'), Number::format($averageRating, precision: 1) . '/5')
                ->description(__('admin.stats.customer_satisfaction'))
                ->descriptionIcon('heroicon-m-star')
                ->color($averageRating >= 4 ? 'success' : ($averageRating >= 3 ? 'warning' : 'danger')),
        ];
    }
}
