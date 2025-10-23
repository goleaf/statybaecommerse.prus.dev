<?php

declare(strict_types=1);

namespace App\Filament\Widgets\InlineCharts;

use App\Models\Customer;
use App\Support\Stats\Series\CustomerSeries;
use LaraZeus\InlineChart\InlineChartWidget;

/**
 * Lightweight inline chart for summarising a customer's recent order activity.
 */
final class CustomerOrdersSparkline extends InlineChartWidget
{
    /**
     * Hide the default heading for compact table rendering.
     */
    protected ?string $heading = null;

    /**
     * Match the product chart dimensions for visual consistency.
     */
    protected ?string $maxHeight = '48';

    /**
     * Build the Chart.js dataset using the cached customer order series helper.
     *
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        if (! $this->record instanceof Customer) {
            return $this->formatDataset([], [], __('customers.sparkline.orders_label', ['days' => 0]));
        }

        $series = CustomerSeries::dailyOrders($this->record);

        return $this->formatDataset(
            $series['labels'],
            $series['orders'],
            __('customers.sparkline.orders_label', ['days' => count($series['labels'])])
        );
    }

    /**
     * Convert helper output into the dataset structure expected by Chart.js.
     *
     * @param  array<int, string>    $labels
     * @param  array<int, float|int> $values
     * @return array<string, mixed>
     */
    private function formatDataset(array $labels, array $values, string $label): array
    {
        return [
            'datasets' => [
                [
                    'label'           => $label,
                    'data'            => $values,
                    'borderWidth'     => 2,
                    'borderColor'     => 'rgba(34, 197, 94, 1)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'fill'            => 'origin',
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
