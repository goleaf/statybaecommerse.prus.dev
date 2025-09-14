<?php

declare(strict_types=1);

namespace App\Filament\Resources\CityResource\Widgets;

use App\Models\City;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

final class CityStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalCities = City::count();
        $enabledCities = City::where('is_enabled', true)->count();
        $capitalCities = City::where('is_capital', true)->count();
        $citiesWithPopulation = City::where('population', '>', 0)->count();
        $citiesWithCoordinates = City::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->count();

        return [
            Stat::make(__('cities.total_cities'), $totalCities)
                ->description(__('cities.total_cities'))
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary'),

            Stat::make(__('cities.enabled_cities'), $enabledCities)
                ->description(__('cities.enabled_cities'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart($this->getEnabledCitiesChartData()),

            Stat::make(__('cities.capital_cities'), $capitalCities)
                ->description(__('cities.capital_cities'))
                ->descriptionIcon('heroicon-m-crown')
                ->color('warning'),

            Stat::make(__('cities.cities_with_population'), $citiesWithPopulation)
                ->description(__('cities.cities_with_population'))
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->chart($this->getPopulationChartData()),

            Stat::make(__('cities.cities_with_coordinates'), $citiesWithCoordinates)
                ->description(__('cities.cities_with_coordinates'))
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('gray')
                ->chart($this->getCoordinatesChartData()),
        ];
    }

    private function getEnabledCitiesChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = City::where('is_enabled', true)
                ->whereDate('created_at', '<=', $date)
                ->count();
            $data[] = $count;
        }
        return $data;
    }

    private function getPopulationChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = City::where('population', '>', 0)
                ->whereDate('created_at', '<=', $date)
                ->count();
            $data[] = $count;
        }
        return $data;
    }

    private function getCoordinatesChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = City::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereDate('created_at', '<=', $date)
                ->count();
            $data[] = $count;
        }
        return $data;
    }
}
