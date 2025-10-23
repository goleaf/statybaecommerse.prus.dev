<?php

declare(strict_types=1);

namespace App\Filament\Widgets\InlineCharts;

use App\Support\Stats\Inline\CustomerSeries;
use Illuminate\Database\Eloquent\Model;
use LaraZeus\InlineChart\InlineChartWidget;

final class CustomerLtv12MonthsChart extends InlineChartWidget
{
    public ?Model $record = null;

    protected ?string $heading = null;

    protected ?string $maxHeight = '60px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        if ($this->record === null) {
            return ['datasets' => [], 'labels' => []];
        }

        $series = CustomerSeries::ordersLast12m((int) $this->record->getKey());

        return [
            'datasets' => [[
                'label'           => __('Revenue'),
                'data'            => $series['values'],
                'backgroundColor' => 'rgba(16, 185, 129, 0.35)',
                'borderColor'     => '#10b981',
                'borderWidth'     => 1,
            ]],
            'labels' => $series['labels'],
        ];
    }
}
