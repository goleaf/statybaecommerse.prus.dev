<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Address;
use App\Models\Country;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Order;
use App\Models\Product;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class DashboardOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    public function getStats(): array
    {
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();

        // Core Business Metrics
        $totalRevenue = Order::query()->where('status', '!=', 'cancelled')->sum('total_amount');
        $lastMonthRevenue = Order::query()
            ->where('status', '!=', 'cancelled')
            ->createdSince($lastMonth)
            ->sum('total_amount');
        $revenueGrowth = $lastMonthRevenue > 0 ? (($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0;

        $totalOrders = Order::count();
        $lastMonthOrders = Order::query()->createdSince($lastMonth)->count();
        $orderGrowth = $lastMonthOrders > 0 ? (($totalOrders - $lastMonthOrders) / $lastMonthOrders) * 100 : 0;

        $totalUsers = User::count();
        $newUsersThisMonth = User::where('created_at', '>=', $lastMonth)->count();
        $userGrowth = $newUsersThisMonth > 0 ? ($newUsersThisMonth / max($totalUsers - $newUsersThisMonth, 1)) * 100 : 0;

        $totalProducts = Product::count();
        $activeProducts = Product::where('is_visible', true)->count();
        $lowStockProducts = Inventory::where('stock_quantity', '<=', DB::raw('threshold'))->count();

        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Geographic & System Metrics
        $totalCountries = Country::count();
        $totalLocations = Location::count();
        $totalAddresses = Address::count();
        $totalSystemSettings = SystemSetting::count();

        return [
            // Primary Business Metrics
            Stat::make(__('translations.total_revenue'), \Illuminate\Support\Number::currency($totalRevenue, 'EUR'))
                ->description(__('translations.from_last_month') . ': ' . \Illuminate\Support\Number::currency($lastMonthRevenue, 'EUR'))
                ->descriptionIcon($revenueGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueGrowth >= 0 ? 'success' : 'danger')
                ->chart($this->getRevenueChart()),
            Stat::make(__('translations.total_orders'), \Illuminate\Support\Number::format($totalOrders))
                ->description(__('translations.from_last_month') . ': ' . \Illuminate\Support\Number::format($lastMonthOrders))
                ->descriptionIcon($orderGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($orderGrowth >= 0 ? 'success' : 'danger')
                ->chart($this->getOrdersChart()),
            Stat::make(__('translations.total_customers'), \Illuminate\Support\Number::format($totalUsers))
                ->description(__('translations.new_customers_today') . ': ' . \Illuminate\Support\Number::format($newUsersThisMonth))
                ->descriptionIcon($userGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($userGrowth >= 0 ? 'success' : 'danger'),
            Stat::make(__('translations.total_products'), \Illuminate\Support\Number::format($totalProducts))
                ->description(__('translations.active_products') . ': ' . \Illuminate\Support\Number::format($activeProducts))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),
            
            Stat::make(__('translations.average_order_value'), \Illuminate\Support\Number::currency($avgOrderValue, 'EUR'))
                ->description(__('translations.per_order'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
            Stat::make(__('translations.low_stock'), \Illuminate\Support\Number::format($lowStockProducts))
                ->description(__('translations.products_need_restocking'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockProducts > 0 ? 'warning' : 'success'),
            
            // System Metrics
            Stat::make(__('translations.countries'), \Illuminate\Support\Number::format($totalCountries))
                ->description(__('translations.supported_countries'))
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('info'),
            Stat::make(__('translations.locations'), \Illuminate\Support\Number::format($totalLocations))
                ->description(__('translations.warehouse_locations'))
                ->descriptionIcon('heroicon-m-building-office')
                ->color('warning'),
            Stat::make(__('translations.addresses'), \Illuminate\Support\Number::format($totalAddresses))
                ->description(__('translations.customer_addresses'))
                ->descriptionIcon('heroicon-m-home')
                ->color('info'),
            Stat::make(__('translations.system_settings'), \Illuminate\Support\Number::format($totalSystemSettings))
                ->description(__('translations.configuration_items'))
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('warning'),
        ];
    }

    private function getRevenueChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = Order::query()
                ->where('status', '!=', 'cancelled')
                ->createdOnDate($date)
                ->sum('total_amount');
            $data[] = $revenue;
        }

        return $data;
    }

    private function getOrdersChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $orders = Order::query()->createdOnDate($date)->count();
            $data[] = $orders;
        }

        return $data;
    }
}