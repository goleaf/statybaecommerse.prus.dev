<?php

declare(strict_types=1);

namespace App\Filament\Resources\CountryResource\Widgets;

use App\Models\Country;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

final class CountriesStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $distinctRegions = Country::query()
            ->whereNotNull('region')
            ->where('region', '!=', '')
            ->distinct('region')
            ->count('region');

        $distinctSubregions = Country::query()
            ->whereNotNull('subregion')
            ->where('subregion', '!=', '')
            ->distinct('subregion')
            ->count('subregion');

        $averageVatRate = (float) Country::query()
            ->whereNotNull('vat_rate')
            ->avg('vat_rate');

        $averageVatRateDisplay = $averageVatRate > 0
            ? number_format($averageVatRate, 2) . '%'
            : 'N/A';

        return [
            Stat::make(__('countries.stats.total_regions'), Number::format($distinctRegions))
                ->color('primary'),
            Stat::make(__('countries.stats.total_subregions'), Number::format($distinctSubregions))
                ->color('secondary'),
            Stat::make(__('countries.fields.vat_rate'), $averageVatRateDisplay)
                ->description(__('countries.fields.requires_vat'))
                ->color('warning'),
        ];
    }
}
