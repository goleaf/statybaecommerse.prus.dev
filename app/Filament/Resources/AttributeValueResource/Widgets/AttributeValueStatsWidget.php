<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeValueResource\Widgets;

use App\Models\AttributeValue;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class AttributeValueStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalAttributeValues = AttributeValue::count();
        $activeAttributeValues = AttributeValue::where('is_active', true)->count();
        $colorValues = AttributeValue::whereHas('attribute', function ($query) {
            $query->where('type', 'color');
        })->count();
        $sizeValues = AttributeValue::whereHas('attribute', function ($query) {
            $query->where('type', 'size');
        })->count();

        return [
            Stat::make(__('attribute_values.stats.total_attribute_values'), $totalAttributeValues)
                ->description(__('attribute_values.stats.total_attribute_values_description'))
                ->descriptionIcon('heroicon-m-tag')
                ->color('primary'),

            Stat::make(__('attribute_values.stats.active_attribute_values'), $activeAttributeValues)
                ->description(__('attribute_values.stats.active_attribute_values_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('attribute_values.stats.color_values'), $colorValues)
                ->description(__('attribute_values.stats.color_values_description'))
                ->descriptionIcon('heroicon-m-paint-brush')
                ->color('info'),

            Stat::make(__('attribute_values.stats.size_values'), $sizeValues)
                ->description(__('attribute_values.stats.size_values_description'))
                ->descriptionIcon('heroicon-m-scale')
                ->color('warning'),
        ];
    }
}
