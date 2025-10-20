<?php

declare(strict_types=1);

namespace App\Filament\Resources\CountryResource\Widgets;

use App\Models\Country;
use Filament\Widgets\ChartWidget;

final class CountriesByRegionWidget extends ChartWidget
{
    protected ?string $heading = 'Countries by Region';

    protected function getData(): array
    {
        $unknownLabel = __('attributes.unknown');

        $data = Country::query()
            ->selectRaw('COALESCE(NULLIF(region, \'\'), ?) as region_label, COUNT(*) as total', [$unknownLabel])
            ->groupBy('region_label')
            ->orderByDesc('total')
            ->get();

        return [
            'datasets' => [
                [
                    'label'           => __('countries.filters.region'),
                    'data'            => $data->pluck('total')->toArray(),
                    'backgroundColor' => [
                        '#3B82F6',
                        '#10B981',
                        '#F59E0B',
                        '#EF4444',
                        '#8B5CF6',
                        '#06B6D4',
                        '#84CC16',
                        '#F97316',
                        '#EC4899',
                        '#6B7280',
                    ],
                ],
            ],
            'labels' => $data->pluck('region_label')->toArray(),
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
                    'display'  => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
