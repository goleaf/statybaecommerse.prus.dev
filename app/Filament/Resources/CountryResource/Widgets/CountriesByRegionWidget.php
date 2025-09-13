<?php

declare(strict_types=1);

namespace App\Filament\Resources\CountryResource\Widgets;

use App\Models\Country;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;

final class CountriesByRegionWidget extends ChartWidget
{
    protected static ?string $heading = 'Countries by Region';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function getDescription(): ?string
    {
        return 'Distribution of countries across different regions';
    }

    protected function getData(): array
    {
        $regions = Country::selectRaw('region, COUNT(*) as count')
            ->whereNotNull('region')
            ->groupBy('region')
            ->orderBy('count', 'desc')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Countries',
                    'data' => $regions->pluck('count')->toArray(),
                    'backgroundColor' => [
                        '#3B82F6', // Blue
                        '#10B981', // Green
                        '#F59E0B', // Yellow
                        '#EF4444', // Red
                        '#8B5CF6', // Purple
                        '#06B6D4', // Cyan
                        '#84CC16', // Lime
                    ],
                    'borderColor' => [
                        '#1E40AF', // Dark Blue
                        '#047857', // Dark Green
                        '#D97706', // Dark Yellow
                        '#DC2626', // Dark Red
                        '#7C3AED', // Dark Purple
                        '#0891B2', // Dark Cyan
                        '#65A30D', // Dark Lime
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $regions->pluck('region')->toArray(),
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
                            const label = context.label || "";
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ": " + value + " (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
        ];
    }
}