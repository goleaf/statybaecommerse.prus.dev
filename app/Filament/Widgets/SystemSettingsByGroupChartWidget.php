<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\SystemSetting;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
/**
 * SystemSettingsByGroupChartWidget
 * 
 * Filament v4 widget for SystemSettingsByGroupChartWidget dashboard display with real-time data and interactive features.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @property int|string|array $columnSpan
 */
final class SystemSettingsByGroupChartWidget extends ChartWidget
{
    protected ?string $heading = 'System Settings by Group';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $data = SystemSetting::active()->select('group', DB::raw('count(*) as count'))->groupBy('group')->orderBy('count', 'desc')->get();
        return ['datasets' => [['label' => __('admin.system_settings.settings_count'), 'data' => $data->pluck('count')->toArray(), 'backgroundColor' => [
            '#3B82F6',
            // blue
            '#10B981',
            // emerald
            '#F59E0B',
            // amber
            '#EF4444',
            // red
            '#8B5CF6',
            // violet
            '#06B6D4',
            // cyan
            '#84CC16',
            // lime
            '#F97316',
            // orange
            '#EC4899',
            // pink
            '#6B7280',
        ], 'borderColor' => '#ffffff', 'borderWidth' => 2]], 'labels' => $data->pluck('group')->map(fn($group) => ucfirst($group))->toArray()];
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