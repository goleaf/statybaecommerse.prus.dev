<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

final class StatsWidget extends BaseStatsOverviewWidget
{
    protected static ?int $sort = 1;

    public function getStats(): array
    {
        $totalRevenue = (float) (Order::sum('total') ?? 0);
        $totalOrders = (int) Order::count();
        $totalProducts = (int) Product::where('is_visible', true)->count();
        $totalCustomers = (int) User::count();
        $visibleCategories = (int) Category::where('is_visible', true)->count();
        $enabledBrands = (int) Brand::where('is_enabled', true)->count();

        return [
            Stat::make(__('messages.total_revenue'), '€' . number_format($totalRevenue, 2))->color('success'),
            Stat::make(__('messages.total_orders'), $totalOrders)->color('primary'),
            Stat::make(__('messages.total_products'), $totalProducts)->color('primary'),
            Stat::make(__('messages.total_customers'), $totalCustomers)->color('primary'),
            Stat::make(__('messages.categories'), $visibleCategories)->color('info'),
            Stat::make(__('messages.brands'), $enabledBrands)->color('info'),
        ];
    }

    public function getRevenueChart(): array
    {
        $since = Carbon::now()->subDays(30);

        return Order::createdSince($since)
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue')
            ->toArray();
    }

    public function getOrdersChart(): array
    {
        $since = Carbon::now()->subDays(30);

        return Order::createdSince($since)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('orders')
            ->toArray();
    }
}