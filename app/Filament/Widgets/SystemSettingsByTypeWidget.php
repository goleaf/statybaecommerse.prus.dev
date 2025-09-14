<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\SystemSetting;
use Filament\Widgets\ChartWidget;
/**
 * SystemSettingsByTypeWidget
 * 
 * Filament v4 widget for SystemSettingsByTypeWidget dashboard display with real-time data and interactive features.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @property int|string|array $columnSpan
 */
final class SystemSettingsByTypeWidget extends ChartWidget
{
    protected ?string $heading = 'admin.system_settings.widgets.settings_by_type';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $types = SystemSetting::selectRaw('type, COUNT(*) as count')->where('is_active', true)->groupBy('type')->orderBy('count', 'desc')->get();
        return ['datasets' => [['label' => __('admin.system_settings.settings_count'), 'data' => $types->pluck('count'), 'backgroundColor' => ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6B7280', '#14B8A6', '#F43F5E', '#8B5A2B', '#6366F1', '#A855F7'], 'borderColor' => ['#2563EB', '#DC2626', '#059669', '#D97706', '#7C3AED', '#0891B2', '#65A30D', '#EA580C', '#DB2777', '#4B5563', '#0D9488', '#E11D48', '#92400E', '#4F46E5', '#9333EA'], 'borderWidth' => 2]], 'labels' => $types->map(function ($type) {
            return __('admin.system_settings.' . $type->type);
        })];
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
        return ['responsive' => true, 'maintainAspectRatio' => false, 'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['stepSize' => 1]]], 'plugins' => ['legend' => ['display' => false], 'tooltip' => ['callbacks' => ['label' => 'function(context) {
                            const label = context.label || "";
                            const value = context.parsed.y;
                            return label + ": " + value + " " + "' . __('admin.system_settings.settings') . '";
                        }']]]];
    }
}