<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
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
        $activeCustomers = (int) User::whereHas('orders')->count();
        $visibleCategories = (int) Category::where('is_visible', true)->count();
        $enabledBrands = (int) Brand::where('is_enabled', true)->count();
        $approvedReviews = (int) Review::where('is_approved', true)->count();
        $avgRating = (float) (Review::where('is_approved', true)->avg('rating') ?? 0);
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0.0;
        $monthOrders = (int) Order::whereMonth('created_at', Carbon::now()->month)->count();

        return [
            Stat::make(__('analytics.total_revenue'), '€'.number_format($totalRevenue, 2))->color('success'),
            Stat::make(__('analytics.total_orders'), $totalOrders)->color('primary'),
            Stat::make(__('analytics.products'), $totalProducts)->color('primary'),
            Stat::make(__('analytics.customers'), $totalCustomers)->color('primary'),
            Stat::make(__('analytics.active_customers'), $activeCustomers)->color('success'),
            Stat::make(__('analytics.categories'), $visibleCategories)->color('info'),
            Stat::make(__('analytics.brands'), $enabledBrands)->color('info'),
            Stat::make(__('analytics.content'), $visibleCategories + $enabledBrands)->color('info'),
            Stat::make(__('analytics.reviews'), $approvedReviews)->color('warning'),
            Stat::make(__('analytics.average_rating'), number_format($avgRating, 1).'/5')->color('warning'),
            Stat::make(__('analytics.average_order_value'), '€'.number_format($avgOrderValue, 2))->color('info'),
            Stat::make(__('analytics.month_orders'), $monthOrders)->color('primary'),
        ];
    }

    public function getRevenueChart(): array
    {
        return Order::whereDate('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue')
            ->toArray();
    }

    public function getOrdersChart(): array
    {
        return Order::whereDate('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('orders')
            ->toArray();
    }
}
