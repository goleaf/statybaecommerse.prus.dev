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

final class AdvancedStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        // Get time ranges for comparisons
        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();
        $currentWeek = now()->startOfWeek();
        $previousWeek = now()->subWeek()->startOfWeek();

        // Revenue Analytics
        $totalRevenue = Order::sum('total') ?? 0;
        $monthlyRevenue = Order::where('created_at', '>=', $currentMonth)->sum('total') ?? 0;
        $weeklyRevenue = Order::where('created_at', '>=', $currentWeek)->sum('total') ?? 0;

        // Order Analytics
        $totalOrders = Order::count();
        $monthlyOrders = Order::where('created_at', '>=', $currentMonth)->count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();

        // Product Analytics
        $totalProducts = Product::where('is_visible', true)->count();
        $featuredProducts = Product::where('is_visible', true)
            ->where('is_featured', true)
            ->count();
        $lowStockProducts = Product::where('is_visible', true)
            ->where('manage_stock', true)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->count();

        // Customer Analytics
        $totalCustomers = User::count();
        $activeCustomers = User::whereHas('orders')->count();
        $newCustomers = User::where('created_at', '>=', $currentMonth)->count();

        // Content Analytics
        $totalCategories = Category::where('is_visible', true)->count();
        $totalBrands = Brand::where('is_visible', true)->count();
        $totalReviews = Review::where('is_approved', true)->count();
        $avgRating = Review::where('is_approved', true)->avg('rating') ?? 0;

        // Performance Metrics
        $conversionRate = $totalCustomers > 0 ? round(($activeCustomers / $totalCustomers) * 100, 1) : 0;
        $avgOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;
        $completionRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0;

        return [
            // Revenue Section
            Stat::make(__('Total Revenue'), '€' . number_format($totalRevenue, 2))
                ->description(__('All time revenue'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($this->getRevenueChart()),
            Stat::make(__('Monthly Revenue'), '€' . number_format($monthlyRevenue, 2))
                ->description('€' . number_format($weeklyRevenue, 2) . ' ' . __('this week'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
            // Order Section
            Stat::make(__('Total Orders'), number_format($totalOrders))
                ->description($pendingOrders . ' ' . __('pending') . ' | ' . $completedOrders . ' ' . __('completed'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info')
                ->chart($this->getOrdersChart()),
            Stat::make(__('Monthly Orders'), number_format($monthlyOrders))
                ->description($completionRate . '% ' . __('completion rate'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($completionRate >= 80 ? 'success' : ($completionRate >= 60 ? 'warning' : 'danger')),
            // Product Section
            Stat::make(__('Products'), number_format($totalProducts))
                ->description($featuredProducts . ' ' . __('featured'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),
            Stat::make(__('Stock Status'), $lowStockProducts > 0 ? $lowStockProducts . ' ' . __('low stock') : __('All in stock'))
                ->description($totalProducts . ' ' . __('total products'))
                ->descriptionIcon($lowStockProducts > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($lowStockProducts > 0 ? 'warning' : 'success'),
            // Customer Section
            Stat::make(__('Customers'), number_format($totalCustomers))
                ->description($newCustomers . ' ' . __('new this month'))
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make(__('Conversion Rate'), $conversionRate . '%')
                ->description($activeCustomers . ' ' . __('active customers'))
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($conversionRate >= 30 ? 'success' : ($conversionRate >= 15 ? 'warning' : 'danger')),
            // Content Section
            Stat::make(__('Content'), $totalCategories . ' ' . __('categories'))
                ->description($totalBrands . ' ' . __('brands'))
                ->descriptionIcon('heroicon-m-folder')
                ->color('primary'),
            Stat::make(__('Reviews'), number_format($totalReviews))
                ->description(number_format($avgRating, 1) . '/5 ' . __('avg rating'))
                ->descriptionIcon('heroicon-m-star')
                ->color($avgRating >= 4.0 ? 'success' : ($avgRating >= 3.0 ? 'warning' : 'danger')),
            // Performance Section
            Stat::make(__('Avg Order Value'), '€' . number_format($avgOrderValue, 2))
                ->description(__('Per order average'))
                ->descriptionIcon('heroicon-m-calculator')
                ->color('success'),
            Stat::make(__('System Health'), __('Optimal'))
                ->description(__('All systems operational'))
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
