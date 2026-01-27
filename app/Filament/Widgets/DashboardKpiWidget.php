<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\Dashboard\DashboardMetricsRepository;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Throwable;

final class DashboardKpiWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    private ?DashboardMetricsRepository $metricsRepository = null;

    protected function getMetricsRepository(): DashboardMetricsRepository
    {
        return $this->metricsRepository ??= app(DashboardMetricsRepository::class);
    }

    public static function canView(): bool
    {
        return Gate::allows(config('dashboard.permissions.view_kpis'));
    }

    protected function getStats(): array
    {
        try {
            $locale = app()->getLocale();

            // Fetch all metrics with error handling
            $metrics = $this->fetchMetricsWithFallback();

            return [
                $this->buildOrdersStat($metrics['orders'], $locale),
                $this->buildRevenueStat($metrics['revenue'], $locale),
                $this->buildUsersStat($metrics['users'], $locale),
                $this->buildStockStat($metrics['stock'], $locale),
            ];
        } catch (Throwable $e) {
            Log::error('Dashboard KPI widget failed to load stats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->getErrorStats();
        }
    }

    /**
     * Fetch metrics with fallback values on failure
     */
    private function fetchMetricsWithFallback(): array
    {
        $repository = $this->getMetricsRepository();
        $metrics = [
            'orders'  => 0,
            'revenue' => 0.0,
            'users'   => 0,
            'stock'   => 0,
        ];

        try {
            $metrics['orders'] = $repository->ordersToday();
        } catch (Throwable $e) {
            Log::warning('Failed to fetch orders today metric', ['error' => $e->getMessage()]);
        }

        try {
            $metrics['revenue'] = $repository->revenueLastSevenDays();
        } catch (Throwable $e) {
            Log::warning('Failed to fetch revenue metric', ['error' => $e->getMessage()]);
        }

        try {
            $metrics['users'] = $repository->newUsersToday();
        } catch (Throwable $e) {
            Log::warning('Failed to fetch new users metric', ['error' => $e->getMessage()]);
        }

        try {
            $metrics['stock'] = $repository->lowStockItems();
        } catch (Throwable $e) {
            Log::warning('Failed to fetch low stock metric', ['error' => $e->getMessage()]);
        }

        return $metrics;
    }

    private function buildOrdersStat(int $orders, string $locale): Stat
    {
        return Stat::make(
            trans('admin/dashboard.kpis.orders_today'),
            Number::format($orders, locale: $locale)
        )
            ->description(trans('admin/dashboard.kpis.orders_today_description'))
            ->icon('heroicon-m-shopping-cart')
            ->color($orders > 0 ? 'primary' : 'gray')
            ->extraAttributes([
                'aria-label' => trans('admin/dashboard.kpis.orders_today') . ': ' . $orders,
            ]);
    }

    private function buildRevenueStat(float $revenue, string $locale): Stat
    {
        return Stat::make(
            trans('admin/dashboard.kpis.revenue_last_seven_days'),
            Number::currency($revenue, 'EUR', locale: $locale)
        )
            ->description(trans('admin/dashboard.kpis.revenue_last_seven_days_description'))
            ->icon('heroicon-m-banknotes')
            ->color($revenue > 0 ? 'success' : 'gray')
            ->extraAttributes([
                'aria-label' => trans('admin/dashboard.kpis.revenue_last_seven_days') . ': ' .
                    Number::currency($revenue, 'EUR', locale: $locale),
            ]);
    }

    private function buildUsersStat(int $users, string $locale): Stat
    {
        return Stat::make(
            trans('admin/dashboard.kpis.new_users_today'),
            Number::format($users, locale: $locale)
        )
            ->description(trans('admin/dashboard.kpis.new_users_today_description'))
            ->icon('heroicon-m-user-plus')
            ->color($users > 0 ? 'info' : 'gray')
            ->extraAttributes([
                'aria-label' => trans('admin/dashboard.kpis.new_users_today') . ': ' . $users,
            ]);
    }

    private function buildStockStat(int $lowStock, string $locale): Stat
    {
        $color = match (true) {
            $lowStock === 0 => 'success',
            $lowStock <= 5  => 'warning',
            default         => 'danger',
        };

        return Stat::make(
            trans('admin/dashboard.kpis.low_stock_items'),
            Number::format($lowStock, locale: $locale)
        )
            ->description(trans('admin/dashboard.kpis.low_stock_items_description'))
            ->icon('heroicon-m-exclamation-triangle')
            ->color($color)
            ->extraAttributes([
                'aria-label' => trans('admin/dashboard.kpis.low_stock_items') . ': ' . $lowStock,
            ]);
    }

    /**
     * Return error stats when metrics fail to load
     */
    private function getErrorStats(): array
    {
        return [
            Stat::make(trans('admin/dashboard.kpis.orders_today'), '—')
                ->description(trans('admin/dashboard.errors.metric_unavailable'))
                ->icon('heroicon-m-exclamation-triangle')
                ->color('danger'),
            Stat::make(trans('admin/dashboard.kpis.revenue_last_seven_days'), '—')
                ->description(trans('admin/dashboard.errors.metric_unavailable'))
                ->icon('heroicon-m-exclamation-triangle')
                ->color('danger'),
            Stat::make(trans('admin/dashboard.kpis.new_users_today'), '—')
                ->description(trans('admin/dashboard.errors.metric_unavailable'))
                ->icon('heroicon-m-exclamation-triangle')
                ->color('danger'),
            Stat::make(trans('admin/dashboard.kpis.low_stock_items'), '—')
                ->description(trans('admin/dashboard.errors.metric_unavailable'))
                ->icon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
