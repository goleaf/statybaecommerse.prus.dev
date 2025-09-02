<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

final class EnhancedStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Total Revenue (this month)
        $thisMonthRevenue = Order::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $lastMonthRevenue = Order::query()
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $revenueChange = $lastMonthRevenue > 0 
            ? (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 
            : 0;

        // Total Orders (this month)
        $thisMonthOrders = Order::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonthOrders = Order::query()
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $ordersChange = $lastMonthOrders > 0 
            ? (($thisMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100 
            : 0;

        // Active Products
        $activeProducts = Product::query()
            ->where('is_visible', true)
            ->where('status', 'published')
            ->count();

        $totalProducts = Product::query()->count();
        $activePercentage = $totalProducts > 0 ? ($activeProducts / $totalProducts) * 100 : 0;

        // New Customers (this month)
        $newCustomers = User::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'customer');
            })
            ->count();

        $lastMonthCustomers = User::query()
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'customer');
            })
            ->count();

        $customersChange = $lastMonthCustomers > 0 
            ? (($newCustomers - $lastMonthCustomers) / $lastMonthCustomers) * 100 
            : 0;

        return [
            Stat::make(__('translations.total_revenue'), '€' . Number::format($thisMonthRevenue, 2))
                ->description($revenueChange >= 0 ? '+' . number_format($revenueChange, 1) . '% ' . __('translations.from_last_month') : number_format($revenueChange, 1) . '% ' . __('translations.from_last_month'))
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart([
                    $lastMonthRevenue,
                    $thisMonthRevenue,
                ]),

            Stat::make(__('translations.total_orders'), Number::format($thisMonthOrders))
                ->description($ordersChange >= 0 ? '+' . number_format($ordersChange, 1) . '% ' . __('translations.from_last_month') : number_format($ordersChange, 1) . '% ' . __('translations.from_last_month'))
                ->descriptionIcon($ordersChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ordersChange >= 0 ? 'success' : 'danger'),

            Stat::make(__('translations.active_products'), Number::format($activeProducts))
                ->description(number_format($activePercentage, 1) . '% ' . __('translations.of_total_products'))
                ->descriptionIcon('heroicon-m-cube')
                ->color($activePercentage >= 80 ? 'success' : ($activePercentage >= 60 ? 'warning' : 'danger')),

            Stat::make(__('translations.new_customers'), Number::format($newCustomers))
                ->description($customersChange >= 0 ? '+' . number_format($customersChange, 1) . '% ' . __('translations.from_last_month') : number_format($customersChange, 1) . '% ' . __('translations.from_last_month'))
                ->descriptionIcon($customersChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($customersChange >= 0 ? 'success' : 'danger'),
        ];
    }
}
