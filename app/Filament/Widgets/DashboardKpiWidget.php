<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\Dashboard\DashboardMetricsRepository;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Number;

final class DashboardKpiWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function __construct(private readonly DashboardMetricsRepository $metricsRepository)
    {
        parent::__construct();
    }

    public static function canView(): bool
    {
        return Gate::allows(config('dashboard.permissions.view_kpis'));
    }

    protected function getStats(): array
    {
        $locale = app()->getLocale();

        $ordersToday = $this->metricsRepository->ordersToday();
        $revenueLastSevenDays = $this->metricsRepository->revenueLastSevenDays();
        $newUsersToday = $this->metricsRepository->newUsersToday();
        $lowStockItems = $this->metricsRepository->lowStockItems();

        return [
            Stat::make(trans('admin/dashboard.kpis.orders_today'), Number::format($ordersToday, locale: $locale))
                ->description(trans('admin/dashboard.kpis.orders_today_description'))
                ->icon('heroicon-m-shopping-cart')
                ->color('primary'),
            Stat::make(trans('admin/dashboard.kpis.revenue_last_seven_days'), Number::currency($revenueLastSevenDays, 'EUR', locale: $locale))
                ->description(trans('admin/dashboard.kpis.revenue_last_seven_days_description'))
                ->icon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make(trans('admin/dashboard.kpis.new_users_today'), Number::format($newUsersToday, locale: $locale))
                ->description(trans('admin/dashboard.kpis.new_users_today_description'))
                ->icon('heroicon-m-user-plus')
                ->color('info'),
            Stat::make(trans('admin/dashboard.kpis.low_stock_items'), Number::format($lowStockItems, locale: $locale))
                ->description(trans('admin/dashboard.kpis.low_stock_items_description'))
                ->icon('heroicon-m-exclamation-triangle')
                ->color('warning'),
        ];
    }
}
