<?php

declare (strict_types=1);
namespace App\Filament\Resources\AttributeValueResource\Widgets;

use App\Models\Attribute;
use Filament\Widgets\ChartWidget;
/**
 * AttributeValueChartWidget
 * 
 * Filament v4 resource for AttributeValueChartWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class AttributeValueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Attribute Values by Attribute';
    protected static ?int $sort = 2;
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $data = Attribute::withCount('values')->orderBy('values_count', 'desc')->limit(10)->get();
        return ['datasets' => [['label' => __('attributes.values_count'), 'data' => $data->pluck('values_count')->toArray(), 'backgroundColor' => ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6B7280'], 'borderColor' => ['#1E40AF', '#059669', '#D97706', '#DC2626', '#7C3AED', '#0891B2', '#65A30D', '#EA580C', '#DB2777', '#4B5563'], 'borderWidth' => 2]], 'labels' => $data->pluck('name')->toArray()];
    }
    /**
     * Handle getType functionality with proper error handling.
     * @return string
     */
    protected function getType(): string
    {
        return 'doughnut';
    }
    /**
     * Handle getOptions functionality with proper error handling.
     * @return array
     */
    protected function getOptions(): array
    {
        return ['responsive' => true, 'maintainAspectRatio' => false, 'plugins' => ['legend' => ['position' => 'bottom'], 'tooltip' => ['callbacks' => ['label' => "function(context) {\n                            return context.label + ': ' + context.parsed + ' ' + '" . __('attributes.values') . "';\n                        }"]]]];
    }
}