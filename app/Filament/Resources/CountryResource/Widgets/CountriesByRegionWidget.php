<?php

declare(strict_types=1);

namespace App\Filament\Resources\CountryResource\Widgets;

use App\Models\Country;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

final class CountriesByRegionWidget extends ChartWidget
{
    protected static ?string $heading = 'admin.countries.widgets.countries_by_region';

    protected static ?int $sort = 2;

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
                    'label' => __('admin.countries.widgets.countries_count'),
                    'data' => $regions->pluck('count')->toArray(),
                    'backgroundColor' => [
                        '#3B82F6', // Blue
                        '#10B981', // Green
                        '#F59E0B', // Yellow
                        '#EF4444', // Red
                        '#8B5CF6', // Purple
                        '#06B6D4', // Cyan
                        '#84CC16', // Lime
                        '#F97316', // Orange
                    ],
                    'borderColor' => '#ffffff',
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
                            return context.label + ": " + context.parsed + " " + "' . __('admin.countries.widgets.countries') . '";
                        }',
                    ],
                ],
            ],
        ];
    }
}
