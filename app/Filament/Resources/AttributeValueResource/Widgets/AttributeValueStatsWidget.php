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
        return [
            Stat::make(__('attributes.total_values'), AttributeValue::count())
                ->description(__('attributes.total_values_description'))
                ->descriptionIcon('heroicon-m-tag')
                ->color('primary'),
            Stat::make(__('attributes.enabled_values'), AttributeValue::where('is_enabled', true)->count())
                ->description(__('attributes.enabled_values_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make(__('attributes.required_values'), AttributeValue::where('is_required', true)->count())
                ->description(__('attributes.required_values_description'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
            Stat::make(__('attributes.default_values'), AttributeValue::where('is_default', true)->count())
                ->description(__('attributes.default_values_description'))
                ->descriptionIcon('heroicon-m-star')
                ->color('info'),
            Stat::make(__('attributes.values_with_color'), AttributeValue::whereNotNull('color_code')->count())
                ->description(__('attributes.values_with_color_description'))
                ->descriptionIcon('heroicon-m-paint-brush')
                ->color('secondary'),
            Stat::make(__('attributes.values_with_description'), AttributeValue::whereNotNull('description')->count())
                ->description(__('attributes.values_with_description_description'))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('gray'),
        ];
    }
}
