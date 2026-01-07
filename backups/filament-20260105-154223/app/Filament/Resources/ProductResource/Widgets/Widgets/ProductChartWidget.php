<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Filament\Support\InteractsWithDateFilter;
use App\Models\Product;
use Carbon\CarbonImmutable;
use EightyNine\FilamentAdvancedWidget\AdvancedChartWidget;
use Flowframe\Trend\Trend;
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

    protected function getData(): array
    {
        [$from, $to] = $this->getDateRange($this->filter);
        $diff = $from->diffInDays($to);
        $granularityMethod = $diff > 90 ? 'perMonth' : 'perDay';

        /** @var array{labels: array<int, string>, data: array<int, int>} $chart */
        $chart = Cache::remember(
            sprintf('filament:widgets:product-chart:%s:%s:%s', $this->filter, $from->format('Ymd'), $to->format('Ymd')),
            now()->addMinutes(30),
            static function () use ($from, $to, $granularityMethod, $diff): array {
                $trend = Trend::query(
                    Product::query()->whereBetween('created_at', [$from, $to])
                )
                    ->between($from, $to)
                    ->{$granularityMethod}()
                    ->count();

                $labels = [];
                $data = [];

                foreach ($trend as $value) {
                    $labels[] = $diff > 90
                        ? CarbonImmutable::parse($value->date)->isoFormat('MMM YYYY')
                        : CarbonImmutable::parse($value->date)->isoFormat('MMM D');
                    $data[] = (int) $value->aggregate;
                }

                return [
                    'labels' => $labels,
                    'data'   => $data,
                ];
            }
        );

        return [
            'datasets' => [
                [
                    'label'           => __('products.widgets.products_created'),
                    'data'            => $chart['data'],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.15)',
                    'borderColor'     => '#3b82f6',
                    'borderWidth'     => 2,
                    'fill'            => true,
                    'tension'         => 0.4,
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
