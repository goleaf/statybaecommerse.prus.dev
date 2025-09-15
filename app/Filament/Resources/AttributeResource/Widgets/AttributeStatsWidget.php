<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeResource\Widgets;

use App\Models\Attribute;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class AttributeStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalAttributes = Attribute::count();
        $activeAttributes = Attribute::where('is_active', true)->count();
        $attributesWithValues = Attribute::whereHas('attributeValues')->count();
        $filterableAttributes = Attribute::where('is_filterable', true)->count();

        return [
            Stat::make(__('attributes.stats.total_attributes'), $totalAttributes)
                ->description(__('attributes.stats.total_attributes_description'))
                ->descriptionIcon('heroicon-m-tag')
                ->color('primary'),

            Stat::make(__('attributes.stats.active_attributes'), $activeAttributes)
                ->description(__('attributes.stats.active_attributes_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('attributes.stats.attributes_with_values'), $attributesWithValues)
                ->description(__('attributes.stats.attributes_with_values_description'))
                ->descriptionIcon('heroicon-m-list-bullet')
                ->color('info'),

            Stat::make(__('attributes.stats.filterable_attributes'), $filterableAttributes)
                ->description(__('attributes.stats.filterable_attributes_description'))
                ->descriptionIcon('heroicon-m-funnel')
                ->color('warning'),
        ];
    }
}
