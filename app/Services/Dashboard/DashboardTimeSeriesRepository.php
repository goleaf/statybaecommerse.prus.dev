<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\Order;
use App\Models\Scopes\ActiveScope;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

final class DashboardTimeSeriesRepository
{
    public function allSeries(int $days = 30): array
    {
        $ttl = (int) Config::get('dashboard.cache_ttl', 60);
        $cacheKey = sprintf('dashboard.time_series.%s.%d', app()->getLocale(), $days);

        return Cache::remember($cacheKey, now()->addSeconds($ttl), function () use ($days): array {
            $dateRange = $this->generateDateRange($days);
            $revenue = $this->revenuePerDay($dateRange);
            $orders = $this->ordersPerDay($dateRange);
            $users = $this->usersPerDay($dateRange);

            $labels = $dateRange->map(function (CarbonImmutable $date): string {
                return $date->locale(app()->getLocale())->translatedFormat('MMM d');
            })->all();

            return [
                'labels'   => $labels,
                'datasets' => [
                    [
                        'label'           => trans('admin/dashboard.charts.revenue'),
                        'data'            => $revenue->all(),
                        'borderColor'     => '#10B981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                        'yAxisID'         => 'y1',
                    ],
                    [
                        'label'           => trans('admin/dashboard.charts.orders'),
                        'data'            => $orders->all(),
                        'borderColor'     => '#3B82F6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.15)',
                        'yAxisID'         => 'y',
                    ],
                    [
                        'label'           => trans('admin/dashboard.charts.users'),
                        'data'            => $users->all(),
                        'borderColor'     => '#F97316',
                        'backgroundColor' => 'rgba(249, 115, 22, 0.15)',
                        'yAxisID'         => 'y',
                    ],
                ],
            ];
        });
    }

    private function generateDateRange(int $days): Collection
    {
        $startDate = CarbonImmutable::now()->subDays($days - 1)->startOfDay();
        $endDate = CarbonImmutable::now()->endOfDay();

        return collect(CarbonPeriod::create($startDate, '1 day', $endDate)->toArray())
            ->map(static fn ($date) => CarbonImmutable::parse($date)->startOfDay());
    }

    private function revenuePerDay(Collection $dateRange): Collection
    {
        $startDate = $dateRange->first();
        $statuses = Config::get('dashboard.revenue_statuses', []);

        $results = Order::query()
            ->withoutGlobalScopes([ActiveScope::class])
            ->when($statuses !== [], fn ($query) => $query->whereIn('status', $statuses))
            ->where('created_at', '>=', $startDate)
            ->whereNull('deleted_at')
            ->selectRaw('DATE(created_at) as date, SUM(total) as aggregate')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('aggregate', 'date')
            ->map(static fn ($value) => (float) $value);

        return $this->fillMissingDates($dateRange, $results, 0.0);
    }

    private function ordersPerDay(Collection $dateRange): Collection
    {
        $startDate = $dateRange->first();

        $results = Order::query()
            ->withoutGlobalScopes([ActiveScope::class])
            ->where('created_at', '>=', $startDate)
            ->whereNull('deleted_at')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as aggregate')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('aggregate', 'date')
            ->map(static fn ($value) => (int) $value);

        return $this->fillMissingDates($dateRange, $results, 0);
    }

    private function usersPerDay(Collection $dateRange): Collection
    {
        $startDate = $dateRange->first();

        $results = User::query()
            ->withoutGlobalScopes([ActiveScope::class])
            ->where('created_at', '>=', $startDate)
            ->whereNull('deleted_at')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as aggregate')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('aggregate', 'date')
            ->map(static fn ($value) => (int) $value);

        return $this->fillMissingDates($dateRange, $results, 0);
    }

    private function fillMissingDates(Collection $dateRange, Collection $results, float|int $default): Collection
    {
        return $dateRange->mapWithKeys(function (CarbonImmutable $date) use ($results, $default) {
            $key = $date->toDateString();

            return [$key => $results->get($key, $default)];
        })->values();
    }
}
