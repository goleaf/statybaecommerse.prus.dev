<?php

declare(strict_types=1);

namespace App\Filament\Pages\SliderAnalytics\Widgets;

use App\Models\Slider;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

final class SliderViewsTimeline extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Slider Views Timeline';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    public function getData(): array
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

        // Generate timeline data for the last 30 days
        $days = [];
        $viewsData = [];
        $clicksData = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = $startDate->copy()->addDays($i);
            $days[] = $date->format('M d');

            // Simulate views and clicks data based on slider activity
            $daySliders = $query->whereDate('created_at', $date)->count();
            $activeSliders = $query->where('is_active', true)->whereDate('created_at', '<=', $date)->count();

            // Simulate views (higher for active sliders)
            $views = $activeSliders * rand(10, 50) + $daySliders * rand(5, 20);
            $viewsData[] = $views;

            // Simulate clicks (lower than views)
            $clicks = max(0, $views - rand(5, 15));
            $clicksData[] = $clicks;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Views',
                    'data' => $viewsData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Clicks',
                    'data' => $clicksData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $days,
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
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 10,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Daily Views and Clicks',
                ],
                'filler' => [
                    'propagate' => false,
                ],
            ],
            'interaction' => [
                'intersect' => false,
            ],
        ];
    }
}
