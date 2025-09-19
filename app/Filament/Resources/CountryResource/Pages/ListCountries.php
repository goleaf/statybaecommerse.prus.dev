<?php declare(strict_types=1);

namespace App\Filament\Resources\CountryResource\Pages;

use App\Filament\Resources\CountryResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

class ListCountries extends ListRecords
{
    protected static string $resource = CountryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('countries.filters.all'))
                ->icon('heroicon-o-globe-alt'),
            'active' => Tab::make(__('countries.statuses.active'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_active', true))
                ->badge(fn() => \App\Models\Country::where('is_active', true)->count())
                ->icon('heroicon-o-check-circle')
                ->badgeColor('success'),
            'eu_members' => Tab::make(__('countries.fields.is_eu_member'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_eu_member', true))
                ->badge(fn() => \App\Models\Country::where('is_eu_member', true)->count())
                ->icon('heroicon-o-flag')
                ->badgeColor('primary'),
            'vat_countries' => Tab::make(__('countries.fields.requires_vat'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('requires_vat', true))
                ->badge(fn() => \App\Models\Country::where('requires_vat', true)->count())
                ->icon('heroicon-o-calculator')
                ->badgeColor('warning'),
        ];
    }
}
