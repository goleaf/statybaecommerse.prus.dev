<?php

declare (strict_types=1);
namespace App\Filament\Resources\AttributeResource\Widgets;

use App\Models\Attribute;
use Filament\Widgets\ChartWidget;
/**
 * AttributeTypesWidget
 * 
 * Filament v4 resource for AttributeTypesWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property int|null $sort
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class AttributeTypesWidget extends ChartWidget
{
    protected static ?int $sort = 2;
    /**
     * Handle getHeading functionality with proper error handling.
     * @return string
     */
    public function getHeading(): string
    {
        return 'Attribute Types Distribution';
    }
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $types = Attribute::selectRaw('type, COUNT(*) as count')->groupBy('type')->pluck('count', 'type')->toArray();
        $labels = [];
        $data = [];
        $colors = [];
        $typeColors = ['text' => '#3B82F6', 'number' => '#10B981', 'boolean' => '#F59E0B', 'select' => '#8B5CF6', 'multiselect' => '#EF4444', 'color' => '#EC4899', 'date' => '#06B6D4', 'textarea' => '#84CC16', 'file' => '#F97316', 'image' => '#6366F1'];
        foreach ($types as $type => $count) {
            $labels[] = __('attributes.' . $type);
            $data[] = $count;
            $colors[] = $typeColors[$type] ?? '#6B7280';
        }
        return ['datasets' => [['label' => __('attributes.attributes'), 'data' => $data, 'backgroundColor' => $colors, 'borderColor' => $colors, 'borderWidth' => 1]], 'labels' => $labels];
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
        return ['responsive' => true, 'maintainAspectRatio' => false, 'plugins' => ['legend' => ['position' => 'bottom'], 'tooltip' => ['callbacks' => ['label' => 'function(context) {
                            const label = context.label || "";
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ": " + value + " (" + percentage + "%)";
                        }']]]];
    }
}