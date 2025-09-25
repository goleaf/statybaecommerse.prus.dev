<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Campaign;
use App\Models\Order;
use App\Models\Product;
use App\Models\Scopes\ActiveScope;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

final class EcommerceOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    public function mount(): void
    {
        abort_unless(auth()->check() && auth()->user()->hasRole('admin'), 403);
    }

    public function getStatsProperty(): array
    {
        $now = Carbon::now();
        $thisMonthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        $revenueThisMonth = (float) ($this
            ->orders()
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$thisMonthStart, $now])
            ->sum('total') ?? 0);
        $revenueLastMonth = (float) ($this
            ->orders()
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('total') ?? 0);
        $revenueChange = $revenueLastMonth > 0
            ? (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100
            : ($revenueThisMonth > 0 ? 100 : 0);

        $ordersThisMonth = (int) $this
            ->orders()
            ->whereBetween('created_at', [$thisMonthStart, $now])
            ->count();
        $ordersLastMonth = (int) $this
            ->orders()
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->count();
        $ordersChange = $ordersLastMonth > 0
            ? (($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100
            : ($ordersThisMonth > 0 ? 100 : 0);

        $totalCustomers = (int) User::count();
        $totalProducts = (int) Product::query()->withoutGlobalScopes()->count();
        $activeCampaigns = (int) Campaign::query()->withoutGlobalScopes()->where('is_active', true)->count();

        $avgOrderValue = $ordersThisMonth > 0 ? $revenueThisMonth / $ordersThisMonth : 0.0;

        return [
            Stat::make(__('admin.dashboard.stats.total_revenue'), '€'.number_format($revenueThisMonth, 2))
                ->description(sprintf('%+0.1f%%', $revenueChange))
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger'),
            Stat::make(__('admin.dashboard.stats.total_orders'), $ordersThisMonth)
                ->description(sprintf('%+0.1f%%', $ordersChange))
                ->descriptionIcon($ordersChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ordersChange >= 0 ? 'success' : 'danger'),
            Stat::make(__('admin.dashboard.stats.total_customers'), $totalCustomers)
                ->color('primary'),
            Stat::make(__('admin.dashboard.stats.average_order_value'), '€'.number_format($avgOrderValue, 2))
                ->color('info'),
            Stat::make(__('admin.dashboard.stats.total_products'), $totalProducts)
                ->color('primary'),
            Stat::make(__('admin.dashboard.stats.active_campaigns'), $activeCampaigns)
                ->color('warning'),
        ];
    }

    protected function getStats(): array
    {
        return $this->getStatsProperty();
    }

    private function orders(): \Illuminate\Database\Eloquent\Builder
    {
        return Order::query()->withoutGlobalScope(ActiveScope::class);
    }
}
