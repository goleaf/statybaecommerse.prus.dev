<?php

declare(strict_types=1);

namespace App\Filament\Resources\CityResource\Widgets;

use App\Models\City;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

final /**
 * CitiesByCountryChart
 * 
 * Filament resource for admin panel management.
 */
class CitiesByCountryChart extends ChartWidget
{
    protected static ?string $heading = 'Cities by Country';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $citiesByCountry = City::with('country')
            ->whereHas('country')
            ->selectRaw('country_id, COUNT(*) as city_count')
            ->groupBy('country_id')
            ->orderByDesc('city_count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'country' => $item->country?->name ?? 'Unknown',
                    'count' => $item->city_count,
                ];
            });

        return [
            'datasets' => [
                [
                    'label' => __('cities.total_cities'),
                    'data' => $citiesByCountry->pluck('count')->toArray(),
                    'backgroundColor' => [
                        'rgb(59, 130, 246)',   // Blue
                        'rgb(16, 185, 129)',   // Green
                        'rgb(245, 158, 11)',   // Yellow
                        'rgb(239, 68, 68)',    // Red
                        'rgb(139, 92, 246)',   // Purple
                        'rgb(236, 72, 153)',   // Pink
                        'rgb(14, 165, 233)',   // Sky
                        'rgb(34, 197, 94)',    // Emerald
                        'rgb(251, 146, 60)',   // Orange
                        'rgb(168, 85, 247)',   // Violet
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)',
                        'rgb(14, 165, 233)',
                        'rgb(34, 197, 94)',
                        'rgb(251, 146, 60)',
                        'rgb(168, 85, 247)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $citiesByCountry->pluck('country')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}
