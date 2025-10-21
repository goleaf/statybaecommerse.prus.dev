<?php

declare(strict_types=1);

namespace App\Filament\Widgets\InlineCharts;

use App\Support\Stats\Inline\ProductSeries;
use Illuminate\Database\Eloquent\Model;
use LaraZeus\InlineChart\InlineChartWidget;

final class ProductSales30DaysChart extends InlineChartWidget
{
    public ?Model $record = null;

    protected ?string $heading = null;

    protected ?string $maxHeight = '60px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        if ($this->record === null) {
            return ['datasets' => [], 'labels' => []];
        }

        $series = ProductSeries::last30Days((int) $this->record->getKey());

        return [
            'datasets' => [[
                'label'           => __('Sales'),
                'data'            => $series['values'],
                'borderColor'     => '#2563eb',
                'backgroundColor' => 'rgba(37, 99, 235, 0.15)',
                'tension'         => 0.35,
                'pointRadius'     => 0,
            ]],
            'labels' => $series['labels'],
        ];
    }
}
