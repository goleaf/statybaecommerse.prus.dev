<?php

declare(strict_types=1);

namespace App\Filament\Resources\CountryResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\CountryResource;
use App\Filament\Resources\CountryResource\Widgets\CountriesByRegionWidget;
use App\Filament\Resources\CountryResource\Widgets\CountriesOverviewWidget;
use App\Filament\Resources\CountryResource\Widgets\CountriesStatsWidget;
use App\Filament\Resources\CountryResource\Widgets\CountriesWithVatWidget;
use App\Filament\Resources\CountryResource\Widgets\CountryDetailsWidget;
use App\Filament\Resources\CountryResource\Widgets\EuMembersWidget;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use App\Filament\WidgetTabs\Enums\WidgetTabTheme;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCountries extends BaseListRecords
{
    use HasResizableColumns;
    use HasWidgetTabs;

    protected static string $resource = CountryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            'all' => WidgetTab::make(__('countries.filters.all'))
                ->icon('heroicon-o-globe-alt')
                ->value(fn () => $this->getResource()::getEloquentQuery()->count()),
            'active' => WidgetTab::make(__('countries.statuses.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->value(fn () => \App\Models\Country::where('is_active', true)->count())
                ->icon('heroicon-o-check-circle')
                ->theme(WidgetTabTheme::Success),
            'eu_members' => WidgetTab::make(__('countries.fields.is_eu_member'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_eu_member', true))
                ->value(fn () => \App\Models\Country::where('is_eu_member', true)->count())
                ->icon('heroicon-o-flag')
                ->theme(WidgetTabTheme::Info),
            'vat_countries' => WidgetTab::make(__('countries.fields.requires_vat'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('requires_vat', true))
                ->value(fn () => \App\Models\Country::where('requires_vat', true)->count())
                ->icon('heroicon-o-calculator')
                ->theme(WidgetTabTheme::Warning),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CountriesOverviewWidget::class,
            CountriesStatsWidget::class,
            CountriesByRegionWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            CountriesWithVatWidget::class,
            EuMembersWidget::class,
            CountryDetailsWidget::class,
        ];
    }

    public function getWidgetTabs(): array
    {
        return [
            'all' => WidgetTab::make(__('countries.filters.all'))
                ->icon('heroicon-o-globe-alt')
                ->value(fn () => $this->getResource()::getEloquentQuery()->count()),
            'active' => WidgetTab::make(__('countries.statuses.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->value(fn () => \App\Models\Country::where('is_active', true)->count())
                ->icon('heroicon-o-check-circle')
                ->theme(WidgetTabTheme::Success),
            'eu_members' => WidgetTab::make(__('countries.fields.is_eu_member'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_eu_member', true))
                ->value(fn () => \App\Models\Country::where('is_eu_member', true)->count())
                ->icon('heroicon-o-flag')
                ->theme(WidgetTabTheme::Info),
            'vat_countries' => WidgetTab::make(__('countries.fields.requires_vat'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('requires_vat', true))
                ->value(fn () => \App\Models\Country::where('requires_vat', true)->count())
                ->icon('heroicon-o-calculator')
                ->theme(WidgetTabTheme::Warning),
        ];
    }
}
