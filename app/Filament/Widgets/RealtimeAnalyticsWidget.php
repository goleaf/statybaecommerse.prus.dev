<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use App\Models\Order;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class RealtimeAnalyticsWidget extends ChartWidget
{
    public static ?int $sort = 2;
    public ?string $pollingInterval = '10s';
    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return __('admin.widgets.realtime_analytics');
    }

    public function getData(): array
    {
        $timeRange = $this->getTimeRange();

        return [
            'datasets' => [
                [
                    'label' => __('admin.widgets.page_views'),
                    'data' => $this->getPageViews($timeRange),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
                [
                    'label' => __('admin.widgets.orders'),
                    'data' => $this->getOrdersData($timeRange),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
                [
                    'label' => __('admin.widgets.new_users'),
                    'data' => $this->getNewUsersData($timeRange),
                    'backgroundColor' => 'rgba(168, 85, 247, 0.1)',
                    'borderColor' => 'rgb(168, 85, 247)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $this->getLabels($timeRange),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
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
            'scales' => [
                'x' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => __('admin.widgets.time'),
                    ],
                ],
                'y' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => __('admin.widgets.count'),
                    ],
                    'beginAtZero' => true,
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }

    private function getTimeRange(): array
    {
        $hours = [];
        for ($i = 23; $i >= 0; $i--) {
            $hours[] = now()->subHours($i);
        }
        return $hours;
    }

    private function getLabels(array $timeRange): array
    {
        return array_map(function ($time) {
            return $time->format('H:i');
        }, $timeRange);
    }

    private function getPageViews(array $timeRange): array
    {
        $data = [];

        foreach ($timeRange as $time) {
            $count = AnalyticsEvent::where('event_type', 'page_view')
                ->whereBetween('created_at', [
                    $time->startOfHour(),
                    $time->copy()->endOfHour()
                ])
                ->count();

            $data[] = $count;
        }

        return $data;
    }

    private function getOrdersData(array $timeRange): array
    {
        $data = [];

        foreach ($timeRange as $time) {
            $count = Order::whereBetween('created_at', [
                $time->startOfHour(),
                $time->copy()->endOfHour()
            ])->count();

            $data[] = $count;
        }

        return $data;
    }

    private function getNewUsersData(array $timeRange): array
    {
        $data = [];

        foreach ($timeRange as $time) {
            $count = User::whereBetween('created_at', [
                $time->startOfHour(),
                $time->copy()->endOfHour()
            ])->count();

            $data[] = $count;
        }

        return $data;
    }

    public function getDescription(): ?string
    {
        return __('admin.widgets.realtime_analytics_description');
    }
}
