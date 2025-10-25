<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;
use Throwable;
use function collect;

/**
 * ImportInventoryChunk
 *
 * Queue job for ImportInventoryChunk background processing with proper error handling, retry logic, and progress tracking.
 */
class ImportInventoryChunk implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * SQL expression that keeps cross-database stock maths consistent.
     */
    private const AVAILABLE_STOCK_SQL = 'CASE WHEN (vi.stock - vi.reserved) > 0 THEN (vi.stock - vi.reserved) ELSE 0 END';

    /**
     * Number of job attempts before failing.
     */
    public int $tries = 5;

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function __construct(private readonly array $rows)
    {
    }

    /**
     * Define retry backoff windows (in seconds).
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [30, 90, 180, 300, 600];
    }

    /**
     * Cache for variant ids keyed by SKU so each import chunk only looks them up once.
     *
     * @var array<string, int>
     */
    private array $variantIdsBySku = [];

    /**
     * Cache for product ids keyed by SKU when the SKU belongs to a parent product instead of a variant.
     *
     * @var array<string, int>
     */
    private array $productIdsBySku = [];

    /**
     * Cache for location ids keyed by their warehouse code to avoid duplicate inserts.
     *
     * @var array<string, int>
     */
    private array $locationIdsByCode = [];

    /**
     * Cache that maps variant ids to product ids so follow-up rows reuse the lookup.
     *
     * @var array<int, int>
     */
    private array $productIdsByVariantId = [];

    /**
     * Handle the job, event, or request processing.
     */
    public function handle(): void
    {
        // Use LazyCollection with timeout to prevent long-running inventory import operations
        $timeout = now()->addMinutes(15);

        // Prime caches so every chunk keeps database chatter predictable and fast
        $this->primeSkuIndexes();
        $this->primeLocationCache();

        LazyCollection::make($this->rows)
            ->takeUntilTimeout($timeout)
            ->each(function (array $row): void {
                try {
                    $this->processRow($row);
                } catch (Throwable $exception) {
                    // Bubble up enough context for observability without crashing the whole chunk
                    Log::error('Inventory import row failed.', [
                        'sku' => $row['sku'] ?? null,
                        'location_code' => $row['location_code'] ?? null,
                        'error' => $exception->getMessage(),
                    ]);
                }
            });
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function processRow(array $row): void
    {
        // Normalise the incoming SKU and stock payload to keep comparisons strict and predictable
        $sku = $this->normalizeSku($row['sku'] ?? null);
        $stock = $this->extractStock($row['stock'] ?? null);

        if ($sku === '' || $stock === null) {
            return;
        }

        $locationCode = $this->normalizeLocationCode($row['location_code'] ?? null);

        [$variantId, $productId] = $this->resolveVariantContext($sku);

        if ($productId === null) {
            Log::warning('Inventory import skipped because SKU could not be resolved.', [
                'sku' => $sku,
                'location_code' => $locationCode,
            ]);

            return;
        }

        // Ensure we have a location row for the provided code before mutating stock levels
        $locationId = $this->resolveLocationId($locationCode);

        DB::transaction(function () use ($variantId, $productId, $locationId, $stock): void {
            // Persist the fresh stock values for the resolved variant (or product fallback)
            VariantInventory::query()->updateOrCreate(
                ['variant_id' => $variantId ?? $productId, 'location_id' => $locationId],
                ['stock' => $stock, 'reserved' => 0]
            );

            // Keep the product-level warehouse quantity denormalised for quick dashboard lookups
            $this->recalculateWarehouseQuantity($productId);
        });
    }

    private function primeSkuIndexes(): void
    {
        // Collect every unique SKU from the chunk so we can fetch identifiers in a single query
        $skus = collect($this->rows)
            ->pluck('sku')
            ->map(fn ($value): string => $this->normalizeSku($value))
            ->filter()
            ->unique()
            ->all();

        if ($skus === []) {
            return;
        }

        $this->variantIdsBySku = ProductVariant::query()
            ->whereIn('sku', $skus)
            ->pluck('id', 'sku')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $this->productIdsBySku = Product::query()
            ->whereIn('sku', $skus)
            ->pluck('id', 'sku')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    private function primeLocationCache(): void
    {
        // Gather all of the location codes seen in this chunk, always including the default fallback
        $codes = collect($this->rows)
            ->pluck('location_code')
            ->map(fn ($value): string => $this->normalizeLocationCode($value))
            ->unique()
            ->filter()
            ->all();

        if (! in_array('default', $codes, true)) {
            $codes[] = 'default';
        }

        $this->locationIdsByCode = Location::query()
            ->whereIn('code', $codes)
            ->pluck('id', 'code')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * @return array{0: int|null, 1: int|null}
     */
    private function resolveVariantContext(string $sku): array
    {
        if (isset($this->variantIdsBySku[$sku])) {
            $variantId = $this->variantIdsBySku[$sku];

            return [$variantId, $this->productIdForVariant($variantId)];
        }

        if (isset($this->productIdsBySku[$sku])) {
            return [null, $this->productIdsBySku[$sku]];
        }

        return [null, null];
    }

    private function productIdForVariant(int $variantId): int
    {
        if (! isset($this->productIdsByVariantId[$variantId])) {
            $productId = ProductVariant::query()->whereKey($variantId)->value('product_id');

            $this->productIdsByVariantId[$variantId] = $productId !== null ? (int) $productId : $variantId;
        }

        return $this->productIdsByVariantId[$variantId];
    }

    private function resolveLocationId(string $code): int
    {
        if (! isset($this->locationIdsByCode[$code])) {
            // Create a sensible default name and ensure the warehouse is enabled for immediate use
            $location = Location::query()->firstOrCreate(
                ['code' => $code],
                [
                    'name' => $code === 'default' ? 'Default Warehouse' : $code,
                    'is_enabled' => true,
                    'is_default' => $code === 'default',
                    'type' => 'warehouse',
                ]
            );

            $this->locationIdsByCode[$code] = (int) $location->id;
        }

        return $this->locationIdsByCode[$code];
    }

    private function recalculateWarehouseQuantity(int $productId): void
    {
        $available = (int) DB::table('product_variants as v')
            ->join('variant_inventories as vi', 'vi.variant_id', '=', 'v.id')
            ->where('v.product_id', $productId)
            ->sum(DB::raw(self::AVAILABLE_STOCK_SQL));

        if ($available === 0) {
            $available = (int) DB::table('variant_inventories as vi')
                ->where('vi.variant_id', $productId)
                ->sum(DB::raw(self::AVAILABLE_STOCK_SQL));
        }

        Product::query()->whereKey($productId)->update(['warehouse_quantity' => $available]);
    }

    private function normalizeSku(mixed $sku): string
    {
        // Trim extraneous whitespace so data exported from spreadsheets imports cleanly
        return trim((string) ($sku ?? ''));
    }

    private function normalizeLocationCode(mixed $code): string
    {
        // Falling back to a default warehouse keeps historical imports working even when no code is supplied
        $normalised = trim((string) ($code ?? ''));

        return $normalised === '' ? 'default' : $normalised;
    }

    private function extractStock(mixed $stock): ?int
    {
        if ($stock === null || $stock === '') {
            return null;
        }

        if (! is_numeric($stock)) {
            return null;
        }

        return (int) $stock;
    }
}
