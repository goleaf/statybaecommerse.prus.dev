<?php declare(strict_types=1);

namespace App\Filament\Resources\ReviewResource\Widgets;

use App\Models\Review;
use Filament\Widgets\ChartWidget;

final class ReviewRatingDistributionWidget extends ChartWidget
{
    protected static ?string $heading = 'admin.reviews.widgets.rating_distribution';

    protected function getData(): array
    {
        $ratingData = Review::where('is_approved', true)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $labels = [];
        $data = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $labels[] = $i . ' ⭐';
            $data[] = $ratingData[$i] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => __('admin.reviews.widgets.review_count'),
                    'data' => $data,
                    'backgroundColor' => [
                        '#ef4444', // Red for 1 star
                        '#f97316', // Orange for 2 stars
                        '#eab308', // Yellow for 3 stars
                        '#22c55e', // Green for 4 stars
                        '#10b981', // Emerald for 5 stars
                    ],
                    'borderColor' => [
                        '#dc2626',
                        '#ea580c',
                        '#ca8a04',
                        '#16a34a',
                        '#059669',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
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
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ": " + context.parsed + " (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
        ];
    }
}
