<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

final class ComprehensiveStatsWidget extends BaseStatsOverviewWidget
{
    protected static ?int $sort = 1;

    public function getStats(): array
    {
        $now = Carbon::now();

        $totalRevenue = (float) (Order::query()
            ->byStatus('completed')
            ->sum('total') ?? 0);

        $monthlyRevenue = (float) (Order::query()
            ->byStatus('completed')
            ->createdThisMonth()
            ->sum('total') ?? 0);

        $lastMonthRevenue = (float) (Order::query()
            ->byStatus('completed')
            ->createdLastMonth()
            ->sum('total') ?? 0);

        $revenueChange = $lastMonthRevenue > 0
            ? (($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
            : 0;

        $totalOrders = (int) Order::count();
        $monthlyOrders = (int) Order::query()->createdThisMonth()->count();
        $lastMonthOrders = (int) Order::query()->createdLastMonth()->count();

        $ordersChange = $lastMonthOrders > 0
            ? (($monthlyOrders - $lastMonthOrders) / $lastMonthOrders) * 100
            : 0;

        $totalProducts = (int) Product::count();
        $activeProducts = (int) Product::where('is_visible', true)->count();

        $totalCustomers = (int) User::whereHas('orders')->count();
        $newCustomers = (int) User::whereHas('orders')
            ->whereBetween('created_at', [$now->copy()->startOfMonth(), $now])
            ->count();

        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return [
            Stat::make(__('translations.total_revenue'), '€' . number_format($totalRevenue, 2))
                ->description($revenueChange >= 0 ? '+' . number_format($revenueChange, 1) . '%' : number_format($revenueChange, 1) . '%')
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart($this->getRevenueChart()),
            Stat::make(__('translations.monthly_revenue'), '€' . number_format($monthlyRevenue, 2))
                ->description(__('translations.from_last_month'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make(__('translations.total_orders'), number_format($totalOrders))
                ->description($ordersChange >= 0 ? '+' . number_format($ordersChange, 1) . '%' : number_format($ordersChange, 1) . '%')
                ->descriptionIcon($ordersChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ordersChange >= 0 ? 'success' : 'danger')
                ->chart($this->getOrdersChart()),
            Stat::make(__('messages.products'), number_format($totalProducts))
                ->description(number_format($activeProducts) . ' ' . __('translations.active_products'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),
            Stat::make(__('translations.total_customers'), number_format($totalCustomers))
                ->description(number_format($newCustomers) . ' ' . __('translations.new_customers_this_month'))
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
            Stat::make(__('translations.average_order_value'), '€' . number_format($averageOrderValue, 2))
                ->description(__('translations.per_order'))
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
            Stat::make(__('translations.conversions'), '2.4%')
                ->description('+0.3% ' . __('translations.from_last_month'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];
    }

    private function getRevenueChart(): array
    {
        $since = Carbon::now()->subDays(30);

        return Order::query()
            ->byStatus('completed')
            ->createdSince($since)
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue')
            ->toArray();
    }

    private function getOrdersChart(): array
    {
        $since = Carbon::now()->subDays(30);

        return Order::query()
            ->createdSince($since)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('orders')
            ->toArray();
    }
}
