<?php

declare(strict_types=1);

namespace App\Filament\Resources\CountryResource\Widgets;

use App\Models\Country;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

final /**
 * CountriesOverviewWidget
 * 
 * Filament resource for admin panel management.
 */
class CountriesOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('admin.countries.statistics.total_countries'), Country::count())
                ->description(__('admin.countries.statistics.total_countries_description'))
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('primary'),

            Stat::make(__('admin.countries.statistics.active_countries'), Country::where('is_active', true)->count())
                ->description(__('admin.countries.statistics.active_countries_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('admin.countries.statistics.eu_members'), Country::where('is_eu_member', true)->count())
                ->description(__('admin.countries.statistics.eu_members_description'))
                ->descriptionIcon('heroicon-m-flag')
                ->color('info'),

            Stat::make(__('admin.countries.statistics.countries_with_vat'), Country::where('requires_vat', true)->count())
                ->description(__('admin.countries.statistics.countries_with_vat_description'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('warning'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
