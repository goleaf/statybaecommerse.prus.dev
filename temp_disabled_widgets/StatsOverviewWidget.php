<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalCustomers = User::whereHas('roles', function ($query) {
            $query->where('name', 'customer');
        })->count();
        $totalRevenue = Order::where('status', 'completed')->sum('total');
        
        $previousMonthRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [
                now()->subMonth(2)->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ])
            ->sum('total');
            
        $currentMonthRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->sum('total');
            
        $revenueChange = $previousMonthRevenue > 0 
            ? (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100 
            : 0;

        return [
            Stat::make(__('Total Products'), number_format($totalProducts))
                ->description(__('Active products in catalog'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),
                
            Stat::make(__('Total Orders'), number_format($totalOrders))
                ->description(__('All time orders'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),
                
            Stat::make(__('Customers'), number_format($totalCustomers))
                ->description(__('Registered customers'))
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
                
            Stat::make(__('Revenue'), format_money($totalRevenue))
                ->description(sprintf(
                    '%s%% %s',
                    number_format(abs($revenueChange), 1),
                    $revenueChange >= 0 ? __('increase') : __('decrease')
                ))
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger'),
        ];
    }
}