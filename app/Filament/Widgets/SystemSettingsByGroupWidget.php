<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\SystemSetting;
use Filament\Widgets\ChartWidget;

final class SystemSettingsByGroupWidget extends ChartWidget
{
    protected ?string $heading = 'admin.system_settings.widgets.settings_by_group';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $groups = SystemSetting::selectRaw('group, COUNT(*) as count')
            ->where('is_active', true)
            ->groupBy('group')
            ->orderBy('count', 'desc')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('admin.system_settings.settings_count'),
                    'data' => $groups->pluck('count'),
                    'backgroundColor' => [
                        '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6',
                        '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6B7280',
                        '#14B8A6', '#F43F5E', '#8B5A2B', '#6366F1', '#A855F7',
                    ],
                    'borderColor' => [
                        '#2563EB', '#DC2626', '#059669', '#D97706', '#7C3AED',
                        '#0891B2', '#65A30D', '#EA580C', '#DB2777', '#4B5563',
                        '#0D9488', '#E11D48', '#92400E', '#4F46E5', '#9333EA',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $groups->map(function ($group) {
                return __('admin.system_settings.'.$group->group);
            }),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            const label = context.label || "";
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ": " + value + " (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
        ];
    }
}
