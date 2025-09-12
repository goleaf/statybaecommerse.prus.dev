<?php declare(strict_types=1);

namespace App\Filament\Resources\PriceListResource\Widgets;

use App\Models\PriceList;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class PriceListActivityWidget extends ChartWidget
{
    protected static ?string $heading = 'admin.price_lists.charts.price_lists_over_time';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = PriceList::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('admin.price_lists.charts.price_lists_created'),
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $data->pluck('date')->map(function ($date) {
                return \Carbon\Carbon::parse($date)->format('M d');
            })->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
        ];
    }
}
