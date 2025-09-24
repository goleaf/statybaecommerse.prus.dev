<?php

declare(strict_types=1);

namespace App\Filament\Pages\SliderAnalytics\Widgets;

use App\Models\Slider;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

final class SliderEngagementMetrics extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Slider Engagement Metrics';

    protected static ?int $sort = 3;

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

        $sliders = $query->get();

        // Calculate engagement metrics
        $withImages = $sliders->filter(fn ($slider) => $slider->hasMedia('slider_images'))->count();
        $withBackgrounds = $sliders->filter(fn ($slider) => $slider->hasMedia('slider_backgrounds'))->count();
        $withButtons = $sliders->filter(fn ($slider) => ! empty($slider->button_text) && ! empty($slider->button_url))->count();
        $withCustomColors = $sliders->filter(fn ($slider) => ! empty($slider->background_color) || ! empty($slider->text_color))->count();
        $withDescriptions = $sliders->filter(fn ($slider) => ! empty($slider->description))->count();
        $withSettings = $sliders->filter(fn ($slider) => ! empty($slider->settings))->count();

        return [
            'datasets' => [
                [
                    'label' => 'Engagement Features',
                    'data' => [
                        $withImages,
                        $withBackgrounds,
                        $withButtons,
                        $withCustomColors,
                        $withDescriptions,
                        $withSettings,
                    ],
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                'With Images',
                'With Backgrounds',
                'With Buttons',
                'Custom Colors',
                'With Descriptions',
                'With Settings',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Slider Feature Usage',
                ],
            ],
        ];
    }
}
