<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\DatabaseDateService;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB as Database;

class EnhancedEcommerceOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $todayOrders = Order::whereDate('created_at', today())->count();
        $todayRevenue = Order::whereDate('created_at', today())
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $monthlyOrders = Order::whereMonth('created_at', now()->month)->count();
        $monthlyRevenue = Order::whereMonth('created_at', now()->month)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $activeCartItems = CartItem::whereDate('created_at', '>=', now()->subDays(1))->count();
        $totalProducts = Product::where('status', 'active')->count();
        $totalUsers = User::count();

        // Analytics data
        $todayPageViews = AnalyticsEvent::ofType('page_view')
            ->whereDate('created_at', today())
            ->count();

        $todayProductViews = AnalyticsEvent::ofType('product_view')
            ->whereDate('created_at', today())
            ->count();

        $conversionRate = $todayPageViews > 0 ? round(($todayOrders / $todayPageViews) * 100, 2) : 0;

        return [
            Stat::make(__('translations.todays_orders'), $todayOrders)
                ->description(__('translations.orders_today'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success')
                ->chart($this->getOrdersChart()),
            Stat::make(__('translations.todays_revenue'), app_money_format($todayRevenue))
                ->description(__('translations.revenue_today'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),
            Stat::make(__('translations.monthly_orders'), $monthlyOrders)
                ->description(__('translations.orders_this_month'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
            Stat::make(__('translations.monthly_revenue'), app_money_format($monthlyRevenue))
                ->description(__('translations.revenue_this_month'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
            Stat::make(__('translations.active_carts'), $activeCartItems)
                ->description(__('translations.cart_items_last_24h'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning'),
            Stat::make(__('translations.total_products'), $totalProducts)
                ->description(__('translations.active_products'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),
            Stat::make(__('translations.total_users'), $totalUsers)
                ->description(__('translations.registered_users'))
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make(__('translations.page_views'), $todayPageViews)
                ->description(__('translations.page_views_today'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('gray'),
            Stat::make(__('translations.product_views'), $todayProductViews)
                ->description(__('translations.product_views_today'))
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color('gray'),
            Stat::make(__('translations.conversion_rate'), $conversionRate . '%')
                ->description(__('translations.orders_per_page_views'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($conversionRate > 2 ? 'success' : ($conversionRate > 1 ? 'warning' : 'danger')),
        ];
    }

    private function getOrdersChart(): array
    {
        return Order::select(
            Database::raw(DatabaseDateService::dateExpression('created_at') . ' as date'),
            Database::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();
    }

    protected function getColumns(): int
    {
        return 5;
    }
}
