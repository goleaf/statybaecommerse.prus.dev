<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Review;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class ComprehensiveStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        // Get current and previous month data for comparisons
        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();
        
        // Total Revenue
        $totalRevenue = Order::sum('total');
        $currentMonthRevenue = Order::where('created_at', '>=', $currentMonth)->sum('total');
        $previousMonthRevenue = Order::whereBetween('created_at', [$previousMonth, $currentMonth])->sum('total');
        $revenueChange = $previousMonthRevenue > 0 ? 
            round((($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100, 1) : 0;
        
        // Total Orders
        $totalOrders = Order::count();
        $currentMonthOrders = Order::where('created_at', '>=', $currentMonth)->count();
        $previousMonthOrders = Order::whereBetween('created_at', [$previousMonth, $currentMonth])->count();
        $ordersChange = $previousMonthOrders > 0 ? 
            round((($currentMonthOrders - $previousMonthOrders) / $previousMonthOrders) * 100, 1) : 0;
        
        // Active Customers
        $totalCustomers = User::count();
        $activeCustomers = User::whereHas('orders')->count();
        $customerRate = $totalCustomers > 0 ? round(($activeCustomers / $totalCustomers) * 100, 1) : 0;
        
        // Product Performance
        $totalProducts = Product::where('is_visible', true)->count();
        $lowStockProducts = Product::where('is_visible', true)
            ->where('manage_stock', true)
            ->where('stock_quantity', '<=', DB::raw('low_stock_threshold'))
            ->count();
        
        // Average Order Value
        $avgOrderValue = Order::avg('total') ?? 0;
        
        // Customer Satisfaction (from reviews)
        $avgRating = Review::where('is_approved', true)->avg('rating') ?? 0;
        $totalReviews = Review::where('is_approved', true)->count();

        return [
            Stat::make(__('Total Revenue'), '€' . number_format($totalRevenue, 2))
                ->description($revenueChange >= 0 ? "+{$revenueChange}% from last month" : "{$revenueChange}% from last month")
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart([
                    $previousMonthRevenue,
                    $currentMonthRevenue,
                ]),
                
            Stat::make(__('Total Orders'), number_format($totalOrders))
                ->description($ordersChange >= 0 ? "+{$ordersChange}% from last month" : "{$ordersChange}% from last month")
                ->descriptionIcon($ordersChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ordersChange >= 0 ? 'success' : 'danger')
                ->chart([
                    $previousMonthOrders,
                    $currentMonthOrders,
                ]),
                
            Stat::make(__('Active Customers'), number_format($activeCustomers))
                ->description("{$customerRate}% of total customers")
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
                
            Stat::make(__('Products'), number_format($totalProducts))
                ->description($lowStockProducts > 0 ? "{$lowStockProducts} low stock items" : "All items in stock")
                ->descriptionIcon($lowStockProducts > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($lowStockProducts > 0 ? 'warning' : 'success'),
                
            Stat::make(__('Avg Order Value'), '€' . number_format($avgOrderValue, 2))
                ->description(__('Per order average'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
                
            Stat::make(__('Customer Rating'), number_format($avgRating, 1) . '/5')
                ->description("{$totalReviews} reviews")
                ->descriptionIcon('heroicon-m-star')
                ->color($avgRating >= 4.0 ? 'success' : ($avgRating >= 3.0 ? 'warning' : 'danger')),
        ];
    }
    
    public static function canView(): bool
    {
        return auth()->user()->can('view_dashboard_stats');
    }
}

