<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\VariantAnalytics;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class VariantPerformanceChart extends ChartWidget
{
    protected ?string $heading = 'Variant Performance Trends';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // Get analytics data for the last 30 days
        $analytics = VariantAnalytics::select([
            DB::raw('DATE(date) as date'),
            DB::raw('SUM(views) as total_views'),
            DB::raw('SUM(clicks) as total_clicks'),
            DB::raw('SUM(add_to_cart) as total_add_to_cart'),
            DB::raw('SUM(purchases) as total_purchases'),
        ])
            ->where('date', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Views',
                    'data' => $analytics->pluck('total_views')->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Clicks',
                    'data' => $analytics->pluck('total_clicks')->toArray(),
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Add to Cart',
                    'data' => $analytics->pluck('total_add_to_cart')->toArray(),
                    'borderColor' => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Purchases',
                    'data' => $analytics->pluck('total_purchases')->toArray(),
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $analytics->pluck('date')->map(fn ($date) => \Carbon\Carbon::parse($date)->format('M j'))->toArray(),
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
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Variant Performance Over Time',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
