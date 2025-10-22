<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\Dashboard\DashboardTimeSeriesRepository;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Gate;

final class DashboardTimeSeriesWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 2];

    public function __construct(private readonly DashboardTimeSeriesRepository $timeSeriesRepository)
    {
        parent::__construct();
    }

    public static function canView(): bool
    {
        return Gate::allows(config('dashboard.permissions.view_charts'));
    }

    public function getHeading(): string
    {
        return trans('admin/dashboard.charts.heading');
    }

    public function getDescription(): ?string
    {
        return trans('admin/dashboard.charts.description');
    }

    protected function getData(): array
    {
        return $this->timeSeriesRepository->allSeries();
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive'          => true,
            'maintainAspectRatio' => false,
            'interaction'         => [
                'mode'      => 'index',
                'intersect' => false,
            ],
            'scales' => [
                'y' => [
                    'type'     => 'linear',
                    'display'  => true,
                    'position' => 'left',
                    'title'    => [
                        'display' => true,
                        'text'    => trans('admin/dashboard.charts.orders_axis'),
                    ],
                ],
                'y1' => [
                    'type'     => 'linear',
                    'display'  => true,
                    'position' => 'right',
                    'title'    => [
                        'display' => true,
                        'text'    => trans('admin/dashboard.charts.revenue_axis'),
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display'  => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
        ];
    }
}
