<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Services\DatabaseDateService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class OrdersChartWidget extends ChartWidget
{
    public static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return __('analytics.orders_overview');
    }

    public function getDescription(): ?string
    {
        return __('analytics.orders_and_revenue_trends');
    }

    public function getData(): array
    {
        $data = Order::select(
            DB::raw(DatabaseDateService::dateExpression('created_at') . ' as date'),
            DB::raw('COUNT(*) as orders'),
            DB::raw('SUM(total) as revenue')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('analytics.orders'),
                    'data' => $data->pluck('orders')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => __('analytics.revenue') . ' (€)',
                    'data' => $data->pluck('revenue')->map(fn($value) => (float) $value)->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $data->pluck('date')->map(fn($date) => \Carbon\Carbon::parse($date)->format('M j'))->toArray(),
        ];
    }

    public function getType(): string
    {
        return 'line';
    }

    public function getOptions(): array
    {
        return [
            'responsive' => true,
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => __('analytics.date'),
                    ],
                ],
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => __('analytics.orders'),
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => __('analytics.revenue') . ' (€)',
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
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->can('view_dashboard_charts') ?? false;
    }
}
