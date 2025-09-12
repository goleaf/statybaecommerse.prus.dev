<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Services\DatabaseDateService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

final class SalesChart extends ChartWidget
{
    public function getHeading(): string
    {
        return __('analytics.sales_overview');
    }

    protected static ?int $sort = 2;
    protected ?string $pollingInterval = '60s';
    protected string $color = 'info';
    public ?string $filter = 'last_7_days';

    protected function getFilters(): ?array
    {
        return [
            'today' => __('admin.filters.today'),
            'last_7_days' => __('admin.filters.last_7_days'),
            'last_30_days' => __('admin.filters.last_30_days'),
            'last_3_months' => __('admin.filters.last_3_months'),
            'this_year' => __('admin.filters.this_year'),
        ];
    }

    protected function getData(): array
    {
        $filter = $this->filter;
        $query = Order::query();

        match ($filter) {
            'today' => $query->whereDate('created_at', today()),
            'last_7_days' => $query->where('created_at', '>=', now()->subDays(7)),
            'last_30_days' => $query->where('created_at', '>=', now()->subDays(30)),
            'last_3_months' => $query->where('created_at', '>=', now()->subMonths(3)),
            'this_year' => $query->whereYear('created_at', now()->year),
            default => $query->where('created_at', '>=', now()->subDays(7)),
        };

        $orders = $query
            ->select(
                DB::raw(DatabaseDateService::dateExpression('created_at') . ' as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $orderCounts = [];
        $revenues = [];

        foreach ($orders as $order) {
            $labels[] = Carbon::parse($order->date)->format('M j');
            $orderCounts[] = $order->count;
            $revenues[] = $order->revenue;
        }

        return [
            'datasets' => [
                [
                    'label' => __('admin.charts.orders'),
                    'data' => $orderCounts,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
                [
                    'label' => __('admin.charts.revenue') . ' (€)',
                    'data' => $revenues,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => __('admin.charts.orders'),
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => __('admin.charts.revenue') . ' (€)',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
        ];
    }

    public function getDescription(): ?string
    {
        return __('admin.widgets.sales_chart_description');
    }
}
