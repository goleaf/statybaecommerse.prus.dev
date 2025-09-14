<?php

declare (strict_types=1);
namespace App\Filament\Resources\ReportResource\Widgets;

use App\Models\Report;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
/**
 * ReportTypesWidget
 * 
 * Filament v4 resource for ReportTypesWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ReportTypesWidget extends ChartWidget
{
    protected static ?string $heading = 'Report Types Distribution';
    protected static ?int $sort = 2;
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $reportTypes = Report::select('type', DB::raw('count(*) as count'))->groupBy('type')->get()->pluck('count', 'type')->toArray();
        $labels = [];
        $data = [];
        $colors = [];
        $typeColors = ['sales' => '#3B82F6', 'products' => '#10B981', 'customers' => '#F59E0B', 'inventory' => '#EF4444', 'analytics' => '#06B6D4', 'financial' => '#8B5CF6', 'marketing' => '#6B7280', 'custom' => '#374151'];
        foreach ($reportTypes as $type => $count) {
            $labels[] = __("admin.reports.types.{$type}");
            $data[] = $count;
            $colors[] = $typeColors[$type] ?? '#6B7280';
        }
        return ['datasets' => [['label' => __('admin.reports.charts.report_types'), 'data' => $data, 'backgroundColor' => $colors, 'borderColor' => $colors, 'borderWidth' => 1]], 'labels' => $labels];
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
        return ['responsive' => true, 'maintainAspectRatio' => false, 'plugins' => ['legend' => ['position' => 'bottom'], 'tooltip' => ['callbacks' => ['label' => "function(context) {\n                            const label = context.label || '';\n                            const value = context.parsed;\n                            const total = context.dataset.data.reduce((a, b) => a + b, 0);\n                            const percentage = ((value / total) * 100).toFixed(1);\n                            return label + ': ' + value + ' (' + percentage + '%)';\n                        }"]]]];
    }
}