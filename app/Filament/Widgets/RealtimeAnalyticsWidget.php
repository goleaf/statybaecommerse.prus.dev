<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Campaign;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

final class RealtimeAnalyticsWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): ?string
    {
        return __('admin.widgets.realtime_analytics');
    }

    public function mount(): void
    {
        abort_unless(auth()->check() && auth()->user()->hasRole('admin'), 403);
    }

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function getStatsProperty(): array
    {
        $today = Carbon::today();
        $yesterday = (clone $today)->subDay();

        $ordersToday = Order::whereDate('created_at', $today)->count();
        $ordersYesterday = Order::whereDate('created_at', $yesterday)->count();
        $ordersChange = $ordersYesterday > 0
            ? (($ordersToday - $ordersYesterday) / $ordersYesterday) * 100
            : ($ordersToday > 0 ? 100 : 0);

        $revenueToday = (float) (Order::where('status', '!=', 'cancelled')
            ->whereDate('created_at', $today)
            ->sum('total') ?? 0);
        $revenueYesterday = (float) (Order::where('status', '!=', 'cancelled')
            ->whereDate('created_at', $yesterday)
            ->sum('total') ?? 0);
        $revenueChange = $revenueYesterday > 0
            ? (($revenueToday - $revenueYesterday) / $revenueYesterday) * 100
            : ($revenueToday > 0 ? 100 : 0);

        $activeCampaigns = Campaign::where('is_active', true)->count();
        $totalProducts = Product::count();

        return [
            Stat::make(__('admin.widgets.today_orders'), $ordersToday)
                ->description(sprintf('%+0.1f%%', $ordersChange))
                ->descriptionIcon($ordersChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ordersChange >= 0 ? 'success' : 'danger'),
            Stat::make(__('admin.widgets.today_revenue'), '€'.number_format($revenueToday, 2))
                ->description(sprintf('%+0.1f%%', $revenueChange))
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger'),
            Stat::make(__('admin.widgets.active_campaigns'), $activeCampaigns)
                ->color('primary'),
            Stat::make(__('admin.widgets.total_products'), $totalProducts)
                ->color('info'),
        ];
    }

    protected function getData(): array
    {
        $labels = [];
        $orders = [];
        for ($i = 23; $i >= 0; $i--) {
            $hour = Carbon::now()->subHours($i);
            $labels[] = $hour->format('H:00');
            $orders[] = Order::whereBetween('created_at', [$hour->copy()->startOfHour(), $hour->copy()->endOfHour()])->count();
        }

        return [
            'datasets' => [
                [
                    'label' => __('admin.widgets.orders_last_24h'),
                    'data' => $orders,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
