<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\DiscountRedemption;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DiscountRedemptionChartWidget extends ChartWidget
{
    protected ?string $heading = null;

    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return __('admin.widgets.redemption_trends');
    }

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '30s';

    public function getDescription(): ?string
    {
        return __('admin.widgets.redemption_activity_last_30_days');
    }

    protected function getData(): array
    {
        $data = DiscountRedemption::select(
            DB::raw('DATE(redeemed_at) as date'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(amount_saved) as total_amount')
        )
            ->where('redeemed_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label'           => __('admin.widgets.redemptions_count'),
                    'data'            => $data->pluck('count')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor'     => 'rgb(59, 130, 246)',
                    'borderWidth'     => 2,
                    'yAxisID'         => 'y',
                ],
                [
                    'label'           => __('admin.widgets.amount_saved'),
                    'data'            => $data->pluck('total_amount')->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor'     => 'rgb(16, 185, 129)',
                    'borderWidth'     => 2,
                    'yAxisID'         => 'y1',
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type'     => 'linear',
                    'display'  => true,
                    'position' => 'left',
                    'title'    => [
                        'display' => true,
                        'text'    => __('admin.widgets.redemptions_count'),
                    ],
                ],
                'y1' => [
                    'type'     => 'linear',
                    'display'  => true,
                    'position' => 'right',
                    'title'    => [
                        'display' => true,
                        'text'    => __('admin.widgets.amount_saved'),
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display'  => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode'      => 'index',
                    'intersect' => false,
                ],
            ],
            'interaction' => [
                'mode'      => 'nearest',
                'axis'      => 'x',
                'intersect' => false,
            ],
        ];
    }
}
