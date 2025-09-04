<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use App\Services\DatabaseDateService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB as Database;

class RealtimeAnalyticsWidget extends ChartWidget
{
    protected ?string $heading = 'Real-time Analytics';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public ?string $filter = 'today';

    protected function getData(): array
    {
        $period = match ($this->filter) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => now()->startOfDay(),
        };

        $events = AnalyticsEvent::select(
            Database::raw(DatabaseDateService::hourExpression('created_at') . ' as hour'),
            'event_type',
            Database::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', $period)
            ->groupBy('hour', 'event_type')
            ->orderBy('hour')
            ->get();

        $hours = range(0, 23);
        $eventTypes = ['page_view', 'product_view', 'add_to_cart', 'purchase'];

        $datasets = [];
        $colors = [
            'page_view' => 'rgb(59, 130, 246)',
            'product_view' => 'rgb(16, 185, 129)',
            'add_to_cart' => 'rgb(245, 158, 11)',
            'purchase' => 'rgb(239, 68, 68)',
        ];

        foreach ($eventTypes as $type) {
            $data = [];
            foreach ($hours as $hour) {
                $count = $events->where('event_type', $type)->where('hour', $hour)->first()?->count ?? 0;
                $data[] = $count;
            }

            $datasets[] = [
                'label' => ucfirst(str_replace('_', ' ', $type)),
                'data' => $data,
                'borderColor' => $colors[$type],
                'backgroundColor' => $colors[$type] . '20',
                'fill' => false,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => array_map(fn($h) => sprintf('%02d:00', $h), $hours),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => __('translations.today'),
            'week' => __('translations.this_week'),
            'month' => __('translations.this_month'),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
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
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
        ];
    }
}
