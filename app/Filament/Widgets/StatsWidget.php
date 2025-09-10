<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\DatabaseDateService;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB as Database;

final class StatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();
        $currentWeek = now()->startOfWeek();

        $totalRevenue = Order::sum('total') ?? 0;
        $monthlyRevenue = Order::where('created_at', '>=', $currentMonth)->sum('total') ?? 0;
        $weeklyRevenue = Order::where('created_at', '>=', $currentWeek)->sum('total') ?? 0;

        $totalOrders = Order::count();
        $monthlyOrders = Order::where('created_at', '>=', $currentMonth)->count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();

        $totalProducts = Product::where('is_visible', true)->count();
        $featuredProducts = Product::where('is_visible', true)
            ->where('is_featured', true)
            ->count();
        $lowStockProducts = Product::where('is_visible', true)
            ->where('manage_stock', true)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->count();

        $totalCustomers = User::count();
        $activeCustomers = User::whereHas('orders')->count();
        $newCustomers = User::where('created_at', '>=', $currentMonth)->count();

        $totalCategories = Category::where('is_visible', true)->count();
        $totalBrands = Brand::where('is_visible', true)->count();
        $totalReviews = Review::where('is_approved', true)->count();
        $avgRating = Review::where('is_approved', true)->avg('rating') ?? 0;

        $conversionRate = $totalCustomers > 0 ? round(($activeCustomers / $totalCustomers) * 100, 1) : 0;
        $avgOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;
        $completionRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0;

        return [
            Stat::make(__('analytics.total_revenue'), '€' . number_format($totalRevenue, 2))
                ->description(__('analytics.all_time_revenue'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($this->getRevenueChart()),
            Stat::make(__('analytics.monthly_revenue'), '€' . number_format($monthlyRevenue, 2))
                ->description('€' . number_format($weeklyRevenue, 2) . ' ' . __('analytics.this_week'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
            Stat::make(__('analytics.total_orders'), number_format($totalOrders))
                ->description($pendingOrders . ' ' . __('analytics.pending') . ' | ' . $completedOrders . ' ' . __('analytics.completed'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info')
                ->chart($this->getOrdersChart()),
            Stat::make(__('analytics.monthly_orders'), number_format($monthlyOrders))
                ->description($completionRate . '% ' . __('analytics.completion_rate'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($completionRate >= 80 ? 'success' : ($completionRate >= 60 ? 'warning' : 'danger')),
            Stat::make(__('analytics.products'), number_format($totalProducts))
                ->description($featuredProducts . ' ' . __('analytics.featured'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),
            Stat::make(__('analytics.stock_status'), $lowStockProducts > 0 ? $lowStockProducts . ' ' . __('analytics.low_stock') : __('analytics.all_in_stock'))
                ->description($totalProducts . ' ' . __('analytics.total_products'))
                ->descriptionIcon($lowStockProducts > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($lowStockProducts > 0 ? 'warning' : 'success'),
            Stat::make(__('analytics.customers'), number_format($totalCustomers))
                ->description($newCustomers . ' ' . __('analytics.new_this_month'))
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make(__('analytics.conversion_rate'), $conversionRate . '%')
                ->description($activeCustomers . ' ' . __('analytics.active_customers'))
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($conversionRate >= 30 ? 'success' : ($conversionRate >= 15 ? 'warning' : 'danger')),
            Stat::make(__('analytics.content'), $totalCategories . ' ' . __('analytics.categories'))
                ->description($totalBrands . ' ' . __('analytics.brands'))
                ->descriptionIcon('heroicon-m-folder')
                ->color('primary'),
            Stat::make(__('analytics.reviews'), number_format($totalReviews))
                ->description(number_format($avgRating, 1) . '/5 ' . __('analytics.avg_rating'))
                ->descriptionIcon('heroicon-m-star')
                ->color($avgRating >= 4.0 ? 'success' : ($avgRating >= 3.0 ? 'warning' : 'danger')),
            Stat::make(__('analytics.avg_order_value'), '€' . number_format($avgOrderValue, 2))
                ->description(__('analytics.per_order_average'))
                ->descriptionIcon('heroicon-m-calculator')
                ->color('success'),
            Stat::make(__('analytics.system_health'), __('analytics.optimal'))
                ->description(__('analytics.all_systems_operational'))
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('success'),
        ];
    }

    private function getRevenueChart(): array
    {
        return Order::select(
            Database::raw(DatabaseDateService::dateExpression('created_at') . ' as date'),
            Database::raw('SUM(total) as revenue')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue')
            ->map(fn($value) => (float) $value)
            ->toArray();
    }

    private function getOrdersChart(): array
    {
        return Order::select(
            Database::raw(DatabaseDateService::dateExpression('created_at') . ' as date'),
            Database::raw('COUNT(*) as orders')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('orders')
            ->map(fn($value) => (int) $value)
            ->toArray();
    }

    public static function canView(): bool
    {
        return auth()->user()?->can('view_dashboard_stats') ?? false;
    }
}

