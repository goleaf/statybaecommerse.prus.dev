<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\SystemSetting;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class SystemSettingsByTypeWidget extends ChartWidget
{
    protected ?string $heading = 'System Settings by Type';

    protected function getData(): array
    {
        $data = SystemSetting::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('system_settings.charts.settings_by_type'),
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => [
                        '#3B82F6', // blue
                        '#10B981', // emerald
                        '#F59E0B', // amber
                        '#EF4444', // red
                        '#8B5CF6', // violet
                        '#06B6D4', // cyan
                        '#84CC16', // lime
                        '#F97316', // orange
                        '#EC4899', // pink
                        '#6B7280', // gray
                    ],
                ],
            ],
            'labels' => $data->pluck('type')->map(fn ($type) => __("system_settings.types.{$type}"))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

