<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

final class EcommerceStatsWidget extends BaseWidget
{
    public function getStats(): array
    {
        return [
            Stat::make(__('admin.dashboard.stats.total_orders'), Order::count())
                ->description(__('admin.dashboard.stats.total_orders_desc'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart($this->getOrdersChart()),
            Stat::make(__('admin.dashboard.stats.monthly_revenue'), $this->getMonthlyRevenue())
                ->description(__('admin.dashboard.stats.monthly_revenue_desc'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('primary')
                ->chart($this->getRevenueChart()),
            Stat::make(__('admin.dashboard.stats.total_products'), Product::count())
                ->description(__('admin.dashboard.stats.total_products_desc'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),
            Stat::make(__('admin.dashboard.stats.total_customers'), User::where('email_verified_at', '!=', null)->count())
                ->description(__('admin.dashboard.stats.total_customers_desc'))
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
            Stat::make(__('admin.dashboard.stats.pending_orders'), Order::where('status', 'pending')->count())
                ->description(__('admin.dashboard.stats.pending_orders_desc'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
            Stat::make(__('admin.dashboard.stats.average_rating'), $this->getAverageRating())
                ->description(__('admin.dashboard.stats.average_rating_desc'))
                ->descriptionIcon('heroicon-m-star')
                ->color('success'),
        ];
    }

    private function getMonthlyRevenue(): string
    {
        $now = Carbon::now();

        $revenue = (float) (Order::where('status', '!=', 'cancelled')
            ->createdBetween($now->copy()->startOfMonth(), $now)
            ->sum('total') ?? 0);

        return '€' . number_format($revenue, 2);
    }

    private function getAverageRating(): string
    {
        $average = (float) (Review::where('is_approved', true)->avg('rating') ?? 0);

        return number_format($average, 1) . '/5';
    }

    private function getOrdersChart(): array
    {
        $since = Carbon::now()->subDays(7);

        return Order::createdSince($since)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();
    }

    private function getRevenueChart(): array
    {
        $since = Carbon::now()->subDays(7);

        return Order::where('status', '!=', 'cancelled')
            ->createdSince($since)
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total')
            ->toArray();
    }
}
