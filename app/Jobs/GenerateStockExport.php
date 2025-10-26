<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\VariantInventory;
use App\Support\Storage\SecureStorage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * GenerateStockExport
 *
 * Queue job for generating stock export files without blocking HTTP requests.
 */
final class GenerateStockExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of job attempts before failing.
     */
    public int $tries = 3;

    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(
        private readonly array $filters,
        private readonly ?int $requestedBy
    ) {
        $this->onQueue('exports');
    }

    /**
     * Define the backoff (delay) for retry attempts.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 120, 300];
    }

    /**
     * Handle the job, event, or request processing.
     */
    public function handle(): void
    {
        $disk = Storage::disk(SecureStorage::disk());
        if (! $disk->exists('exports')) {
            $disk->makeDirectory('exports');
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = sprintf(
            'stock_export_%s%s.csv',
            $timestamp,
            $this->requestedBy ? '_user-' . $this->requestedBy : ''
        );
        $path = 'exports/' . $filename;

        $stream = fopen('php://temp', 'w+');
        if ($stream === false) {
            throw new RuntimeException('Unable to create temporary stream for stock export.');
        }

        fputcsv($stream, [
            __('inventory.product'),
            __('inventory.variant'),
            __('inventory.location'),
            __('inventory.supplier'),
            __('inventory.current_stock'),
            __('inventory.reserved'),
            __('inventory.available'),
            __('inventory.cost_per_unit'),
            __('inventory.stock_value'),
            __('inventory.status'),
            __('inventory.expiry_date'),
            __('inventory.created_at'),
        ]);

        $timeout = now()->addMinutes(15);
        // 15 minute timeout for stock exports
        $this->buildQuery()
            ->cursor()
            ->takeUntilTimeout($timeout)
            ->each(function (VariantInventory $item) use ($stream): void {
                fputcsv($stream, [
                    $item->variant->product->name,
                    $item->variant->display_name,
                    $item->location->name,
                    $item->supplier?->name ?? '',
                    $item->stock,
                    $item->reserved,
                    $item->available_stock,
                    $item->cost_per_unit,
                    $item->stock_value,
                    $item->stock_status_label,
                    $item->expiry_date?->format('Y-m-d') ?? '',
                    $item->created_at->format('Y-m-d H:i:s'),
                ]);
            });

        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        if ($contents === false) {
            throw new RuntimeException('Failed to read generated stock export.');
        }

        $disk->put($path, $contents);

        Log::info('Stock export generated', [
            'path'         => $path,
            'filters'      => $this->filters,
            'requested_by' => $this->requestedBy,
        ]);
    }

    /**
     * @return Builder<VariantInventory>
     */
    private function buildQuery(): Builder
    {
        $query = VariantInventory::with(['variant.product', 'location', 'supplier']);

        if (! empty($this->filters['location_id'])) {
            $query->where('location_id', (int) $this->filters['location_id']);
        }

        if (! empty($this->filters['supplier_id'])) {
            $query->where('supplier_id', (int) $this->filters['supplier_id']);
        }

        if (! empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (! empty($this->filters['stock_status'])) {
            match ($this->filters['stock_status']) {
                'low_stock'     => $query->lowStock(),
                'out_of_stock'  => $query->outOfStock(),
                'needs_reorder' => $query->needsReorder(),
                'expiring_soon' => $query->expiringSoon(),
                default         => null,
            };
        }

        if (! empty($this->filters['search'])) {
            $search = (string) $this->filters['search'];
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->whereHas('variant.product', function ($q) use ($search): void {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('variant', function ($q) use ($search): void {
                        $q->where('sku', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }
}
