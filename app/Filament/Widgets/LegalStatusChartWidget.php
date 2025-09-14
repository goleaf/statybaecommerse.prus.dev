<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Legal;
use Filament\Widgets\ChartWidget;

class LegalStatusChartWidget extends ChartWidget
{
    protected ?string $heading = 'Legal Documents Status Overview';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $published = Legal::where('is_enabled', true)->whereNotNull('published_at')->count();
        $draft = Legal::whereNull('published_at')->count();
        $disabled = Legal::where('is_enabled', false)->count();

        return [
            'datasets' => [
                [
                    'label' => __('admin.legal.documents_count'),
                    'data' => [$published, $draft, $disabled],
                    'backgroundColor' => [
                        '#22c55e', // green for published
                        '#eab308', // yellow for draft
                        '#ef4444', // red for disabled
                    ],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                __('admin.legal.status_published'),
                __('admin.legal.status_draft'),
                __('admin.legal.status_disabled'),
            ],
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
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            const label = context.label || "";
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return label + ": " + value + " (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
        ];
    }
}
