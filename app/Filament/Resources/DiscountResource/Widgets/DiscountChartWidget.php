<?php declare(strict_types=1);

namespace App\Filament\Resources\DiscountResource\Widgets;

use App\Models\Discount;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

final class DiscountChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Discount Usage Over Time';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Discount::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Discounts Created',
                    'data' => $data->pluck('count')->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
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
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}

