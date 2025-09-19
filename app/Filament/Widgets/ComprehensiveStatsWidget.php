<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Illuminate\Support\Carbon;

final class ComprehensiveStatsWidget extends BaseStatsOverviewWidget
{
    protected static ?int $sort = 1;

    public function getStats(): array
    {
        $totalRevenue = Order::where('status', 'completed')
            ->sum('total');

        $monthlyRevenue = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->sum('total');

        $lastMonthRevenue = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('total');

        $revenueChange = $lastMonthRevenue > 0
            ? (($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
            : 0;

        $totalOrders = Order::count();
        $monthlyOrders = Order::whereMonth('created_at', now()->month)->count();
        $lastMonthOrders = Order::whereMonth('created_at', now()->subMonth()->month)->count();

        $ordersChange = $lastMonthOrders > 0
            ? (($monthlyOrders - $lastMonthOrders) / $lastMonthOrders) * 100
            : 0;

        $totalProducts = Product::count();
        $activeProducts = Product::where('is_visible', true)->count();

        $totalCustomers = User::whereHas('orders')->count();
        $newCustomers = User::whereHas('orders')
            ->whereMonth('created_at', now()->month)
            ->count();

        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $totalReviews = Review::count();
        $averageRating = Review::avg('rating') ?? 0;

        return [
            Stat::make(__('Total Revenue'), '€' . number_format($totalRevenue, 2))
                ->description($revenueChange >= 0 ? '+' . number_format($revenueChange, 1) . '%' : number_format($revenueChange, 1) . '%')
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart($this->getRevenueChart()),
            Stat::make(__('Monthly Revenue'), '€' . number_format($monthlyRevenue, 2))
                ->description(__('This month'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make(__('Total Orders'), number_format($totalOrders))
                ->description($ordersChange >= 0 ? '+' . number_format($ordersChange, 1) . '%' : number_format($ordersChange, 1) . '%')
                ->descriptionIcon($ordersChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ordersChange >= 0 ? 'success' : 'danger')
                ->chart($this->getOrdersChart()),
            Stat::make(__('Products'), number_format($totalProducts))
                ->description(number_format($activeProducts) . ' ' . __('active'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),
            Stat::make(__('Customers'), number_format($totalCustomers))
                ->description(number_format($newCustomers) . ' ' . __('new this month'))
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
            Stat::make(__('Avg Order Value'), '€' . number_format($averageOrderValue, 2))
                ->description(__('Per order'))
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
            Stat::make(__('Reviews'), number_format($totalReviews))
                ->description(number_format($averageRating, 1) . ' ' . __('avg rating'))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
            Stat::make(__('Conversion Rate'), '2.4%')
                ->description('+0.3% ' . __('from last month'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];
    }

    private function getRevenueChart(): array
    {
        return Order::where('status', 'completed')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue')
            ->toArray();
    }

    private function getOrdersChart(): array
    {
        return Order::whereDate('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('orders')
            ->toArray();
    }
}
