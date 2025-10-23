<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Filament\Support\InteractsWithDateFilter;
use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * ProductChartWidget
 *
 * Chart widget showing product performance over time
 */
final class ProductChartWidget extends AdvancedChartWidget
{
    use InteractsWithDateFilter;

    protected static ?string $heading = 'Product performance';

    protected static string $color = 'primary';

    public ?string $filter = 'last_30_days';

    protected function getFilters(): ?array
    {
        return [
            'last_30_days' => __('Last 30 days'),
            'quarter'      => __('This quarter'),
            'year'         => __('This year'),
        ];
    }

    protected static bool $isLazy = true;

    protected function getData(): array
    {
        $startDate = Carbon::now()->subDays(29)->startOfDay();
        $cacheKey = sprintf('filament:widgets:product-chart:%s', $startDate->toDateString());

        /** @var array{labels: array<int, string>, data: array<int, int>} $chart */
        $chart = Cache::remember(
            $cacheKey,
            now()->addMinutes(30),
            static function () use ($startDate): array {
                $endDate = Carbon::now()->endOfDay();

                /** @var array<string, int> $counts */
                $counts = Product::query()
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as aggregate')
                    ->where('created_at', '>=', $startDate)
                    ->groupBy('date')
                    ->orderBy('date')
                    ->pluck('aggregate', 'date')
                    ->mapWithKeys(static function ($count, string $date): array {
                        $value = is_numeric($count) ? (int) $count : 0;

                        return [$date => $value];
                    })
                    ->all();

                $labels = [];
                $data = [];

                $cursor = $startDate->copy();
                while ($cursor->lte($endDate)) {
                    $labels[] = $cursor->format('M d');
                    $data[] = $counts[$cursor->toDateString()] ?? 0;
                    $cursor->addDay();
                }

                return [
                    'labels' => $labels,
                    'data' => $data,
                ];
            }
        );

        return [
            'datasets' => [
                [
                    'label' => __('products.widgets.products_created'),
                    'data' => $chart['data'],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $chart['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
