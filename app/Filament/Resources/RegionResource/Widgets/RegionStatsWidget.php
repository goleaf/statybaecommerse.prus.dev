<?php declare(strict_types=1);

namespace App\Filament\Resources\RegionResource\Widgets;

use App\Models\Region;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

final class RegionStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('regions.total_regions'), Region::count())
                ->description(__('regions.total_regions'))
                ->descriptionIcon('heroicon-m-map')
                ->color('primary'),
            
            Stat::make(__('regions.enabled_regions'), Region::enabled()->count())
                ->description(__('regions.enabled_regions'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make(__('regions.default_regions'), Region::default()->count())
                ->description(__('regions.default_regions'))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
            
            Stat::make(__('regions.regions_by_level'), Region::byLevel(0)->count())
                ->description(__('regions.root_regions'))
                ->descriptionIcon('heroicon-m-tree')
                ->color('info'),
        ];
    }
}
