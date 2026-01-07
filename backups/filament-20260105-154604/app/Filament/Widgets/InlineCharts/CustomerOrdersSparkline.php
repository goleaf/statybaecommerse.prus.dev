<?php

declare(strict_types=1);

namespace App\Filament\Widgets\InlineCharts;

use App\Models\Customer;
use App\Support\Stats\Series\CustomerSeries;
use Illuminate\Database\Eloquent\Model;
use LaraZeus\InlineChart\InlineChartWidget;

/**
 * Lightweight inline chart for summarising a customer's recent order activity.
 */
final class CustomerOrdersSparkline extends InlineChartWidget
{
    /**
     * The customer record currently powering the sparkline widget while honouring the base contract.
     */
    public ?Model $record = null;

    /**
     * Hide the default heading for compact table rendering.
     */
    protected ?string $heading = null;

    /**
     * Match the product chart dimensions for visual consistency.
     */
    protected ?string $maxHeight = '48';

    /**
     * Cache the computed dataset so subsequent Livewire renders reuse the same payload.
     *
     * @var array<string, mixed>
     */
    private array $cachedDataset = [];

    /**
     * Hash of the current dataset that the Livewire view can use for quick change detection.
     */
    public ?string $dataChecksum = null;

    /**
     * Prepare the component for rendering and prime the dataset cache.
     */
    public function mount(?Customer $record = null): void
    {
        $this->record = $record;
        $this->refreshDataset();
    }

    /**
     * When the record updates, regenerate the cached dataset so the chart stays in sync.
     */
    public function updatedRecord(?Customer $record): void
    {
        $this->record = $record;
        $this->refreshDataset();
    }

    /**
     * Build the Chart.js dataset using the cached customer order series helper.
     *
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        if ($this->cachedDataset === []) {
            $this->refreshDataset();
        }

        return $this->cachedDataset;
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

    /**
     * Generate and store the dataset alongside the checksum used by the Livewire view layer.
     */
    private function refreshDataset(): void
    {
        $this->cachedDataset = $this->resolveDataset();
        // Cast the JSON payload to a string so checksum generation stays deterministic for Chart.js consumers.
        $this->dataChecksum = md5((string) json_encode($this->cachedDataset));
    }

    /**
     * Resolve the helper series into the standardised dataset structure consumed by Chart.js.
     *
     * @return array<string, mixed>
     */
    private function resolveDataset(): array
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
}
