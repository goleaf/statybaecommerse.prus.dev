<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReportResource\Widgets;

use App\Models\Report;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final /**
 * ReportTypesWidget
 * 
 * Filament resource for admin panel management.
 */
class ReportTypesWidget extends ChartWidget
{
    protected static ?string $heading = 'Report Types Distribution';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $reportTypes = Report::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type')
            ->toArray();

        $labels = [];
        $data = [];
        $colors = [];

        $typeColors = [
            'sales' => '#3B82F6',
            'products' => '#10B981',
            'customers' => '#F59E0B',
            'inventory' => '#EF4444',
            'analytics' => '#06B6D4',
            'financial' => '#8B5CF6',
            'marketing' => '#6B7280',
            'custom' => '#374151',
        ];

        foreach ($reportTypes as $type => $count) {
            $labels[] = __("admin.reports.types.{$type}");
            $data[] = $count;
            $colors[] = $typeColors[$type] ?? '#6B7280';
        }

        return [
            'datasets' => [
                [
                    'label' => __('admin.reports.charts.report_types'),
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
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
                        'label' => "function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }",
                    ],
                ],
            ],
        ];
    }
}
