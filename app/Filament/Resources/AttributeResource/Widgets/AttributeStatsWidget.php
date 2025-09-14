<?php

declare (strict_types=1);
namespace App\Filament\Resources\AttributeResource\Widgets;

use App\Models\Attribute;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
/**
 * AttributeStatsWidget
 * 
 * Filament v4 resource for AttributeStatsWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class AttributeStatsWidget extends BaseWidget
{
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        $totalAttributes = Attribute::count();
        $enabledAttributes = Attribute::where('is_enabled', true)->count();
        $requiredAttributes = Attribute::where('is_required', true)->count();
        $filterableAttributes = Attribute::where('is_filterable', true)->count();
        return [Stat::make(__('attributes.total_attributes'), $totalAttributes)->description(__('attributes.total_attributes_description'))->descriptionIcon('heroicon-m-adjustments-horizontal')->color('primary'), Stat::make(__('attributes.enabled_attributes'), $enabledAttributes)->description(__('attributes.enabled_attributes_description'))->descriptionIcon('heroicon-m-check-circle')->color('success'), Stat::make(__('attributes.required_attributes'), $requiredAttributes)->description(__('attributes.required_attributes_description'))->descriptionIcon('heroicon-m-exclamation-triangle')->color('warning'), Stat::make(__('attributes.filterable_attributes'), $filterableAttributes)->description(__('attributes.filterable_attributes_description'))->descriptionIcon('heroicon-m-funnel')->color('info')];
    }
}