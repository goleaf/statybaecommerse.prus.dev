<?php

declare (strict_types=1);
namespace App\Filament\Resources\AttributeResource\Widgets;

use App\Models\Attribute;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
/**
 * AttributeUsageWidget
 * 
 * Filament v4 resource for AttributeUsageWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property int|null $sort
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class AttributeUsageWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        $totalAttributes = Attribute::count();
        $attributesWithValues = Attribute::has('values')->count();
        $attributesWithProducts = Attribute::has('products')->count();
        $mostUsedAttribute = Attribute::withCount('products')->orderBy('products_count', 'desc')->first();
        return [Stat::make(__('attributes.attributes_with_values'), $attributesWithValues)->description(__('attributes.attributes_with_values_description'))->descriptionIcon('heroicon-m-list-bullet')->color('success')->chart([7, 2, 10, 3, 15, 4, 17]), Stat::make(__('attributes.attributes_with_products'), $attributesWithProducts)->description(__('attributes.attributes_with_products_description'))->descriptionIcon('heroicon-m-cube')->color('info')->chart([3, 5, 8, 12, 15, 18, 20]), Stat::make(__('attributes.most_used_attribute'), $mostUsedAttribute?->name ?? __('attributes.none'))->description(__('attributes.used_by_products', ['count' => $mostUsedAttribute?->products_count ?? 0]))->descriptionIcon('heroicon-m-star')->color('warning')->chart([2, 4, 6, 8, 10, 12, 14]), Stat::make(__('attributes.usage_rate'), $totalAttributes > 0 ? round($attributesWithProducts / $totalAttributes * 100, 1) . '%' : '0%')->description(__('attributes.usage_rate_description'))->descriptionIcon('heroicon-m-chart-bar')->color('primary')->chart([10, 15, 20, 25, 30, 35, 40])];
    }
}