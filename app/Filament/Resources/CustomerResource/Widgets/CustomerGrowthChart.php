<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerResource\Widgets;

use App\Filament\Support\InteractsWithDateFilter;
use App\Models\Customer;
use Carbon\CarbonImmutable;
use EightyNine\FilamentAdvancedWidget\AdvancedChartWidget;
use Flowframe\Trend\Trend;
use Illuminate\Support\Facades\Cache;

final class CustomerGrowthChart extends AdvancedChartWidget
{
    use InteractsWithDateFilter;

    protected static ?string $heading = 'Customer growth';

    protected static string $color = 'success';

    public ?string $filter = 'quarter';

    protected function getFilters(): ?array
    {
        return [
            'month'   => __('Last month'),
            'quarter' => __('This quarter'),
            'year'    => __('This year'),
        ];
    }

    protected function getData(): array
    {
        [$from, $to] = $this->getDateRange($this->filter);
        $diff = $from->diffInDays($to);
        $granularityMethod = $diff > 120 ? 'perMonth' : 'perDay';

        /** @var array{labels: array<int, string>, data: array<int, int>} $chart */
        $chart = Cache::remember(
            sprintf('filament:widgets:customer-growth:%s:%s:%s', $this->filter, $from->format('Ymd'), $to->format('Ymd')),
            now()->addMinutes(10),
            static function () use ($from, $to, $granularityMethod, $diff): array {
                $trend = Trend::query(
                    Customer::query()->whereBetween('created_at', [$from, $to])
                )
                    ->between($from, $to)
                    ->{$granularityMethod}()
                    ->count();

                $labels = [];
                $data = [];

                foreach ($trend as $value) {
                    $labels[] = $diff > 120
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
                    'label'           => __('New customers'),
                    'data'            => $chart['data'],
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor'     => '#22c55e',
                    'borderWidth'     => 2,
                    'fill'            => true,
                    'tension'         => 0.35,
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
