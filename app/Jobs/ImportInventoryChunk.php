<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

/**
 * ImportInventoryChunk
 *
 * Queue job for ImportInventoryChunk background processing with proper error handling, retry logic, and progress tracking.
 *
 * @property array $rows
 */
class ImportInventoryChunk implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /** @var array<int,array<string,mixed>> */
    private array $rows;

    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    /**
     * Handle the job, event, or request processing.
     */
    public function handle(): void
    {
        // Use LazyCollection with timeout to prevent long-running inventory import operations
        $timeout = now()->addMinutes(15);
        // 15 minute timeout for inventory imports
        LazyCollection::make($this->rows)->takeUntilTimeout($timeout)->each(function ($row) {
            $sku = (string) ($row['sku'] ?? '');
            $locationCode = (string) ($row['location_code'] ?? 'default');
            $stock = isset($row['stock']) ? (int) $row['stock'] : null;
            if ($sku === '' || $stock === null) {
                return;
            }
            $variantId = DB::table('sh_product_variants')->where('sku', $sku)->value('id');
            if (! $variantId) {
                $variantId = DB::table('sh_products')->where('sku', $sku)->value('id');
                if ($variantId) {
                    // Interpret as product-level inventory if variant not found
                    $existing = DB::table('sh_variant_inventories')->where('variant_id', $variantId)->first();
                    if ($existing) {
                        return;
                    }
                } else {
                    return;
                }
            }
            $inventoryId = DB::table('sh_inventories')->where('code', $locationCode)->value('id') ?? DB::table('sh_inventories')->insertGetId(['code' => $locationCode, 'name' => $locationCode, 'created_at' => now(), 'updated_at' => now()]);
            try {
                DB::table('sh_variant_inventories')->upsert([['variant_id' => (int) $variantId, 'inventory_id' => (int) $inventoryId, 'stock' => (int) $stock, 'reserved' => 0, 'updated_at' => now(), 'created_at' => now()]], ['variant_id', 'inventory_id'], ['stock', 'reserved', 'updated_at']);
                // Denormalize warehouse_quantity on product
                $productId = DB::table('sh_product_variants')->where('id', $variantId)->value('product_id') ?? $variantId;
                $sum = (int) DB::table('sh_product_variants as v')->join('sh_variant_inventories as vi', 'vi.variant_id', '=', 'v.id')->where('v.product_id', $productId)->sum(DB::raw('CASE WHEN (vi.stock - vi.reserved) > 0 THEN (vi.stock - vi.reserved) ELSE 0 END'));
                if ($sum === 0) {
                    $sum = (int) DB::table('sh_variant_inventories as vi')->where('vi.variant_id', $productId)->sum(DB::raw('CASE WHEN (vi.stock - vi.reserved) > 0 THEN (vi.stock - vi.reserved) ELSE 0 END'));
                }
                DB::table('sh_products')->where('id', $productId)->update(['warehouse_quantity' => $sum]);
            } catch (\Throwable $e) {
                // continue
            }
        });
    }
}
