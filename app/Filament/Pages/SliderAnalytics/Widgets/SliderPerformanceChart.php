<?php

declare(strict_types=1);

namespace App\Filament\Pages\SliderAnalytics\Widgets;

use App\Models\Slider;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

final class SliderPerformanceChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Slider Performance Over Time';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    protected function getData(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? now()->subDays(30);
        $endDate = $this->pageFilters['endDate'] ?? now();
        $sliderId = $this->pageFilters['sliderId'] ?? null;
        $status = $this->pageFilters['status'] ?? 'all';

        $query = Slider::query()
            ->when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate))
            ->when($sliderId, fn (Builder $query) => $query->where('id', $sliderId))
            ->when($status !== 'all', fn (Builder $query) => $query->where('is_active', $status === 'active'));

        // Group by week for the last 30 days
        $weeks = [];
        $activeData = [];
        $inactiveData = [];

        for ($i = 4; $i >= 0; $i--) {
            $weekStart = $startDate->copy()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            $weeks[] = $weekStart->format('M d');

            $activeCount = $query
                ->where('is_active', true)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();

            $inactiveCount = $query
                ->where('is_active', false)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();

            $activeData[] = $activeCount;
            $inactiveData[] = $inactiveCount;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Active Sliders',
                    'data' => $activeData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Inactive Sliders',
                    'data' => $inactiveData,
                    'backgroundColor' => 'rgba(107, 114, 128, 0.8)',
                    'borderColor' => 'rgb(107, 114, 128)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $weeks,
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
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Slider Performance Trends',
                ],
            ],
        ];
    }
}
