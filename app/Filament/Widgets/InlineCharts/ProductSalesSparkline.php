<?php

declare(strict_types=1);

namespace App\Filament\Widgets\InlineCharts;

use App\Models\Product;
use App\Support\Stats\Series\ProductSeries;
use Illuminate\Database\Eloquent\Model;
use LaraZeus\InlineChart\InlineChartWidget;

/**
 * Lightweight inline chart rendering the recent revenue sparkline for a product record.
 */
final class ProductSalesSparkline extends InlineChartWidget
{
    /**
     * The product record that powers the sparkline widget instance while honouring the base contract.
     */
    public ?Model $record = null;

    /**
     * Hide the heading so the widget renders as a pure sparkline.
     */
    protected ?string $heading = null;

    /**
     * Keep the chart within the compact sparkline dimensions.
     */
    protected ?string $maxHeight = '48';

    /**
     * Capture the optional product record and seed the dataset cache during component boot.
     */
    public function mount(?Product $record = null): void
    {
        $this->record = $record;
        parent::mount();
    }

    /**
     * Recompute the dataset whenever Livewire mutates the bound product record.
     */
    public function updatedRecord(?Product $record): void
    {
        $this->record = $record;
        $this->cachedData = null;
        $this->updateChartData();
    }

    /**
     * Build the Chart.js payload using the cached product sales series helper.
     *
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        if (! $this->record instanceof Product) {
            return $this->formatDataset([], [], __('products.sparkline.revenue_label', ['days' => 0]));
        }

        $series = ProductSeries::dailySales($this->record);

        return $this->formatDataset(
            $series['labels'],
            $series['revenue'],
            __('products.sparkline.revenue_label', ['days' => count($series['labels'])]),
        );
    }

    /**
     * Convert helper output into a Chart.js friendly dataset structure.
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
                    'borderColor'     => 'rgba(37, 99, 235, 1)',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.15)',
                    'fill'            => 'origin',
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
