<?php

declare(strict_types=1);

namespace App\Filament\Resources\CountryResource\Widgets;

use App\Models\Country;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

final class CountriesOverviewWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalCountries = Country::query()->count();
        $activeCountries = Country::query()->where('is_active', true)->count();
        $euMembers = Country::query()->where('is_eu_member', true)->count();
        $vatCountries = Country::query()->where('requires_vat', true)->count();

        return [
            Stat::make(__('countries.stats.total_countries'), Number::format($totalCountries)),
            Stat::make(__('countries.stats.active_countries'), Number::format($activeCountries))
                ->color('success'),
            Stat::make(__('countries.stats.eu_members'), Number::format($euMembers))
                ->color('info'),
            Stat::make(__('countries.stats.vat_countries'), Number::format($vatCountries))
                ->color('warning'),
        ];
    }
}
