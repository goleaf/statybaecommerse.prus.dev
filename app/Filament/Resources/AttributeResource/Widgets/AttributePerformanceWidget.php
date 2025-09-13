<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeResource\Widgets;

use App\Models\Attribute;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class AttributePerformanceWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $totalAttributes = Attribute::count();
        $enabledAttributes = Attribute::where('is_enabled', true)->count();
        $requiredAttributes = Attribute::where('is_required', true)->count();
        $filterableAttributes = Attribute::where('is_filterable', true)->count();
        $searchableAttributes = Attribute::where('is_searchable', true)->count();
        $visibleAttributes = Attribute::where('is_visible', true)->count();
        
        // Calculate performance metrics
        $enabledRate = $totalAttributes > 0 ? round(($enabledAttributes / $totalAttributes) * 100, 1) : 0;
        $requiredRate = $totalAttributes > 0 ? round(($requiredAttributes / $totalAttributes) * 100, 1) : 0;
        $filterableRate = $totalAttributes > 0 ? round(($filterableAttributes / $totalAttributes) * 100, 1) : 0;
        $searchableRate = $totalAttributes > 0 ? round(($searchableAttributes / $totalAttributes) * 100, 1) : 0;
        
        // Get most popular attribute
        $mostPopular = Attribute::withCount('products')
            ->orderBy('products_count', 'desc')
            ->first();
            
        // Get attributes with most values
        $mostValues = Attribute::withCount('values')
            ->orderBy('values_count', 'desc')
            ->first();

        return [
            Stat::make(__('attributes.enabled_rate'), $enabledRate . '%')
                ->description(__('attributes.enabled_attributes') . ': ' . $enabledAttributes . '/' . $totalAttributes)
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($enabledRate >= 90 ? 'success' : ($enabledRate >= 70 ? 'warning' : 'danger'))
                ->chart([85, 87, 89, 91, 93, 95, $enabledRate]),

            Stat::make(__('attributes.filterable_rate'), $filterableRate . '%')
                ->description(__('attributes.filterable_attributes') . ': ' . $filterableAttributes . '/' . $totalAttributes)
                ->descriptionIcon('heroicon-m-funnel')
                ->color($filterableRate >= 80 ? 'success' : ($filterableRate >= 60 ? 'warning' : 'danger'))
                ->chart([70, 72, 75, 78, 80, 82, $filterableRate]),

            Stat::make(__('attributes.most_popular'), $mostPopular?->name ?? __('attributes.none'))
                ->description(__('attributes.used_by_products', ['count' => $mostPopular?->products_count ?? 0]))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning')
                ->chart([5, 8, 12, 15, 18, 22, $mostPopular?->products_count ?? 0]),

            Stat::make(__('attributes.most_values'), $mostValues?->name ?? __('attributes.none'))
                ->description(__('attributes.values_count', ['count' => $mostValues?->values_count ?? 0]))
                ->descriptionIcon('heroicon-m-list-bullet')
                ->color('info')
                ->chart([3, 6, 9, 12, 15, 18, $mostValues?->values_count ?? 0]),
        ];
    }
}
