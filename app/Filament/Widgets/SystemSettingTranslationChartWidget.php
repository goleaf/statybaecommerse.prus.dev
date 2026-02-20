<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\SystemSettingTranslation;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class SystemSettingTranslationChartWidget extends ChartWidget
{
    protected ?string $heading = null;

    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return __('admin.widgets.translations_by_language');
    }

    protected function getData(): array
    {
        $data = SystemSettingTranslation::select('locale', DB::raw('count(*) as count'))
            ->groupBy('locale')
            ->orderBy('count', 'desc')
            ->get();

        return [
            'datasets' => [
                [
                    'label'           => __('admin.system_setting_translations.translations_count'),
                    'data'            => $data->pluck('count')->toArray(),
                    'backgroundColor' => [
                        '#3B82F6', // Blue
                        '#10B981', // Green
                        '#F59E0B', // Yellow
                        '#EF4444', // Red
                        '#8B5CF6', // Purple
                        '#06B6D4', // Cyan
                        '#84CC16', // Lime
                    ],
                ],
            ],
            'labels' => $data->pluck('locale')->map(fn ($locale) => match ($locale) {
                'en'    => '🇺🇸 ' . __('translations.english'),
                'lt'    => '🇱🇹 ' . __('translations.lithuanian'),
                'de'    => '🇩🇪 ' . __('translations.german'),
                'fr'    => '🇫🇷 ' . __('translations.french'),
                'es'    => '🇪🇸 ' . __('translations.spanish'),
                'pl'    => '🇵🇱 ' . __('admin.labels.polish'),
                'ru'    => '🇷🇺 ' . __('translations.russian'),
                default => $locale,
            })->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive'          => true,
            'maintainAspectRatio' => false,
            'plugins'             => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
