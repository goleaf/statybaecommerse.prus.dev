<?php declare(strict_types=1);

namespace App\Filament\Resources\RegionResource\Widgets;

use App\Models\Region;
use Filament\Widgets\ChartWidget;

final class RegionsByLevelWidget extends ChartWidget
{
    protected static ?string $heading = 'Regions by Level';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $regionsByLevel = Region::selectRaw('level, COUNT(*) as count')
            ->groupBy('level')
            ->orderBy('level')
            ->get()
            ->mapWithKeys(function ($item) {
                $levelName = match($item->level) {
                    0 => 'Root',
                    1 => 'State/Province',
                    2 => 'County',
                    3 => 'District',
                    default => "Level {$item->level}"
                };
                return [$levelName => $item->count];
            });

        return [
            'datasets' => [
                [
                    'label' => __('regions.regions_by_level'),
                    'data' => array_values($regionsByLevel->toArray()),
                    'backgroundColor' => [
                        '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6'
                    ],
                ],
            ],
            'labels' => array_keys($regionsByLevel->toArray()),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
