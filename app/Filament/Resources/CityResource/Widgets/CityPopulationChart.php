<?php

declare(strict_types=1);

namespace App\Filament\Resources\CityResource\Widgets;

use App\Models\City;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

final class CityPopulationChart extends ChartWidget
{
    protected static ?string $heading = 'City Population Distribution';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $populationRanges = [
            '0-10K' => City::where('population', '>=', 0)->where('population', '<', 10000)->count(),
            '10K-50K' => City::where('population', '>=', 10000)->where('population', '<', 50000)->count(),
            '50K-100K' => City::where('population', '>=', 50000)->where('population', '<', 100000)->count(),
            '100K-500K' => City::where('population', '>=', 100000)->where('population', '<', 500000)->count(),
            '500K+' => City::where('population', '>=', 500000)->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => __('cities.population'),
                    'data' => array_values($populationRanges),
                    'backgroundColor' => [
                        'rgb(59, 130, 246)',   // Blue
                        'rgb(16, 185, 129)',   // Green
                        'rgb(245, 158, 11)',   // Yellow
                        'rgb(239, 68, 68)',    // Red
                        'rgb(139, 92, 246)',   // Purple
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => array_keys($populationRanges),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}
