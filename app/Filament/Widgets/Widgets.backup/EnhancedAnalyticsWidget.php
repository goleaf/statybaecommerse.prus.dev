<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\DatabaseDateService;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB as Database;

final class EnhancedAnalyticsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        // Get current month data
        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();

        // Orders analytics
        $currentMonthOrders = Order::where('created_at', '>=', $currentMonth)->count();
        $previousMonthOrders = Order::whereBetween('created_at', [$previousMonth, $currentMonth])->count();
        $ordersChange = $previousMonthOrders > 0
            ? round((($currentMonthOrders - $previousMonthOrders) / $previousMonthOrders) * 100, 1)
            : 0;

        // Revenue analytics
        $currentMonthRevenue = Order::where('created_at', '>=', $currentMonth)->sum('total');
        $previousMonthRevenue = Order::whereBetween('created_at', [$previousMonth, $currentMonth])->sum('total');
        $revenueChange = $previousMonthRevenue > 0
            ? round((($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100, 1)
            : 0;

        // Customer analytics
        $currentMonthCustomers = User::where('created_at', '>=', $currentMonth)->count();
        $previousMonthCustomers = User::whereBetween('created_at', [$previousMonth, $currentMonth])->count();
        $customersChange = $previousMonthCustomers > 0
            ? round((($currentMonthCustomers - $previousMonthCustomers) / $previousMonthCustomers) * 100, 1)
            : 0;

        // Product analytics
        $totalProducts = Product::where('is_visible', true)->count();
        $lowStockProducts = Product::where('is_visible', true)
            ->where('manage_stock', true)
            ->where('stock_quantity', '<=', Database::raw('low_stock_threshold'))
            ->count();

        return [
            Stat::make(__('Total Orders This Month'), $currentMonthOrders)
                ->description($ordersChange >= 0 ? "+{$ordersChange}% from last month" : "{$ordersChange}% from last month")
                ->descriptionIcon($ordersChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ordersChange >= 0 ? 'success' : 'danger')
                ->chart($this->getOrdersChart()),
            Stat::make(__('Revenue This Month'), '€' . number_format($currentMonthRevenue, 2))
                ->description($revenueChange >= 0 ? "+{$revenueChange}% from last month" : "{$revenueChange}% from last month")
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart($this->getRevenueChart()),
            Stat::make(__('New Customers This Month'), $currentMonthCustomers)
                ->description($customersChange >= 0 ? "+{$customersChange}% from last month" : "{$customersChange}% from last month")
                ->descriptionIcon($customersChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($customersChange >= 0 ? 'success' : 'danger'),
            Stat::make(__('Products'), $totalProducts)
                ->description($lowStockProducts > 0 ? "{$lowStockProducts} low stock items" : __('All items in stock'))
                ->descriptionIcon($lowStockProducts > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($lowStockProducts > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.products.index')),
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

    private function getRevenueChart(): array
    {
        return Order::select(
            Database::raw(DatabaseDateService::dateExpression('created_at') . ' as date'),
            Database::raw('SUM(total) as total')
        )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total')
            ->toArray();
    }
}
