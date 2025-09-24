<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SimplifiedStatsWidget extends BaseWidget
{
    protected static ?int $sort = -1;

    protected int|string|array $columnSpan = 'full';

    public function getStats(): array
    {
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();

        // === CORE BUSINESS METRICS ===
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total');
        $lastMonthRevenue = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $lastMonth)
            ->sum('total');
        $revenueGrowth = $lastMonthRevenue > 0 ? (($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0;

        $totalOrders = Order::count();
        $lastMonthOrders = Order::where('created_at', '>=', $lastMonth)->count();
        $orderGrowth = $lastMonthOrders > 0 ? (($totalOrders - $lastMonthOrders) / $lastMonthOrders) * 100 : 0;

        $totalUsers = User::count();
        $newUsersThisMonth = User::where('created_at', '>=', $lastMonth)->count();
        $userGrowth = $newUsersThisMonth > 0 ? ($newUsersThisMonth / max($totalUsers - $newUsersThisMonth, 1)) * 100 : 0;

        // === PRODUCT ECOSYSTEM ===
        $totalProducts = Product::count();
        $activeProducts = Product::where('is_visible', true)->count();

        // === CATEGORIES & BRANDS ===
        $totalCategories = Category::count();
        $totalBrands = Brand::count();

        // === REVIEWS & RATINGS ===
        $totalReviews = Review::count();
        $approvedReviews = Review::where('is_approved', true)->count();
        $avgRating = Review::where('is_approved', true)->avg('rating') ?? 0;

        // === CALCULATED METRICS ===
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return [
            // === PRIMARY BUSINESS METRICS ===
            Stat::make(__('translations.total_revenue'), \Illuminate\Support\Number::currency($totalRevenue, 'EUR'))
                ->description(__('translations.from_last_month').': '.\Illuminate\Support\Number::currency($lastMonthRevenue, 'EUR'))
                ->descriptionIcon($revenueGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueGrowth >= 0 ? 'success' : 'danger')
                ->chart($this->getRevenueChart()),

            Stat::make(__('translations.total_orders'), \Illuminate\Support\Number::format($totalOrders))
                ->description(__('translations.from_last_month').': '.\Illuminate\Support\Number::format($lastMonthOrders))
                ->descriptionIcon($orderGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($orderGrowth >= 0 ? 'success' : 'danger')
                ->chart($this->getOrdersChart()),

            Stat::make(__('translations.total_customers'), \Illuminate\Support\Number::format($totalUsers))
                ->description(__('translations.new_customers_this_month').': '.\Illuminate\Support\Number::format($newUsersThisMonth))
                ->descriptionIcon($userGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($userGrowth >= 0 ? 'success' : 'danger'),

            Stat::make(__('translations.average_order_value'), \Illuminate\Support\Number::currency($avgOrderValue, 'EUR'))
                ->description(__('translations.per_order'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),

            // === PRODUCT ECOSYSTEM ===
            Stat::make(__('translations.total_products'), \Illuminate\Support\Number::format($totalProducts)) // Viso produktų
                ->description(__('translations.active_products').': '.\Illuminate\Support\Number::format($activeProducts))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make(__('translations.categories'), \Illuminate\Support\Number::format($totalCategories))
                ->description(__('translations.total_categories'))
                ->descriptionIcon('heroicon-m-tag')
                ->color('info'),

            Stat::make(__('translations.brands'), \Illuminate\Support\Number::format($totalBrands))
                ->description(__('translations.total_brands'))
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary'),

            // === REVIEWS & RATINGS ===
            Stat::make(__('translations.total_reviews'), \Illuminate\Support\Number::format($totalReviews))
                ->description(__('translations.approved_reviews').': '.\Illuminate\Support\Number::format($approvedReviews))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make(__('translations.average_rating'), number_format((float) $avgRating, 1).'/5')
                ->description(__('translations.customer_satisfaction'))
                ->descriptionIcon('heroicon-m-star')
                ->color($avgRating >= 4 ? 'success' : ($avgRating >= 3 ? 'warning' : 'danger')),
        ];
    }

    public function getRevenueChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = Order::where('status', '!=', 'cancelled')
                ->whereDate('created_at', $date)
                ->sum('total');
            $data[] = $revenue;
        }

        return $data;
    }

    public function getOrdersChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $orders = Order::whereDate('created_at', $date)->count();
            $data[] = $orders;
        }

        return $data;
    }
}
