<?php

declare (strict_types=1);
namespace App\Filament\Resources\AttributeResource\Widgets;

use App\Models\Attribute;
use Filament\Widgets\ChartWidget;
/**
 * AttributeAnalyticsWidget
 * 
 * Filament v4 resource for AttributeAnalyticsWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property int|null $sort
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class AttributeAnalyticsWidget extends ChartWidget
{
    protected static ?int $sort = 4;
    /**
     * Handle getHeading functionality with proper error handling.
     * @return string
     */
    public function getHeading(): string
    {
        return 'Attribute Usage Analytics';
    }
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $attributes = Attribute::withCount(['products', 'values'])->orderBy('products_count', 'desc')->limit(10)->get();
        $labels = [];
        $usageData = [];
        $valuesData = [];
        foreach ($attributes as $attribute) {
            $labels[] = $attribute->name;
            $usageData[] = $attribute->products_count;
            $valuesData[] = $attribute->values_count;
        }
        return ['datasets' => [['label' => __('attributes.products_using_attribute'), 'data' => $usageData, 'backgroundColor' => 'rgba(59, 130, 246, 0.5)', 'borderColor' => 'rgb(59, 130, 246)', 'borderWidth' => 2, 'yAxisID' => 'y'], ['label' => __('attributes.attribute_values_count'), 'data' => $valuesData, 'backgroundColor' => 'rgba(16, 185, 129, 0.5)', 'borderColor' => 'rgb(16, 185, 129)', 'borderWidth' => 2, 'yAxisID' => 'y1']], 'labels' => $labels];
    }
    /**
     * Handle getType functionality with proper error handling.
     * @return string
     */
    protected function getType(): string
    {
        return 'bar';
    }
    /**
     * Handle getOptions functionality with proper error handling.
     * @return array
     */
    protected function getOptions(): array
    {
        return ['responsive' => true, 'maintainAspectRatio' => false, 'interaction' => ['mode' => 'index', 'intersect' => false], 'scales' => ['x' => ['display' => true, 'title' => ['display' => true, 'text' => __('attributes.attributes')]], 'y' => ['type' => 'linear', 'display' => true, 'position' => 'left', 'title' => ['display' => true, 'text' => __('attributes.products_count')]], 'y1' => ['type' => 'linear', 'display' => true, 'position' => 'right', 'title' => ['display' => true, 'text' => __('attributes.values_count')], 'grid' => ['drawOnChartArea' => false]]], 'plugins' => ['legend' => ['display' => true, 'position' => 'top'], 'tooltip' => ['mode' => 'index', 'intersect' => false]]];
    }
}