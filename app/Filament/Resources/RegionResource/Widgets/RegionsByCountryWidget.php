<?php declare(strict_types=1);

namespace App\Filament\Resources\RegionResource\Widgets;

use App\Models\Region;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

final class RegionsByCountryWidget extends ChartWidget
{
    protected static ?string $heading = 'Regions by Country';
    
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $regionsByCountry = Region::with('country')
            ->selectRaw('country_id, COUNT(*) as count')
            ->groupBy('country_id')
            ->get()
            ->mapWithKeys(function ($item) {
                $countryName = $item->country?->name ?? 'Unknown';
                return [$countryName => $item->count];
            });

        return [
            'datasets' => [
                [
                    'label' => __('regions.regions_by_country'),
                    'data' => array_values($regionsByCountry->toArray()),
                    'backgroundColor' => [
                        '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6',
                        '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#6366F1'
                    ],
                ],
            ],
            'labels' => array_keys($regionsByCountry->toArray()),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
