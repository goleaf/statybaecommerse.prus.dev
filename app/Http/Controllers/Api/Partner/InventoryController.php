<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Partner;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class InventoryController
{
    public function __invoke(Request $request): JsonResponse
    {
        // Keep pagination predictable by bounding the page size to a safe range.
        $perPage = min(max($request->integer('per_page', 50), 1), 100);
        $page = max($request->integer('page', 1), 1);

        // Build the base query with the relationships eager loaded to avoid N+1 lookups.
        $query = VariantInventory::query()
            ->with([
                'variant:id,product_id,sku,name,barcode',
                'variant.product:id,name,sku',
                'location:id,name,code',
            ])
            ->orderByDesc('updated_at')
            ->orderByDesc('id');

        // Allow partners to filter by product or variant SKU to fetch specific records quickly.
        $sku = $request->query('sku');
        if (is_string($sku) && $sku !== '') {
            $normalizedSku = trim($sku);
            $query->where(static function (Builder $builder) use ($normalizedSku): void {
                $builder
                    ->whereHas('variant', static function (Builder $variantQuery) use ($normalizedSku): void {
                        $variantQuery->where('sku', 'like', '%' . $normalizedSku . '%');
                    })
                    ->orWhereHas('variant.product', static function (Builder $productQuery) use ($normalizedSku): void {
                        $productQuery->where('sku', 'like', '%' . $normalizedSku . '%');
                    })
                    ->orWhere('warehouse_code', 'like', '%' . $normalizedSku . '%');
            });
        }

        // Support location filters so partners can slice stock by warehouse or store identifier.
        $locationId = $request->query('location_id');
        if (is_numeric($locationId)) {
            $query->where('location_id', (int) $locationId);
        }

        // Provide direct filters for variant and product identifiers when partners track those keys.
        $variantId = $request->query('variant_id');
        if (is_numeric($variantId)) {
            $query->where('variant_id', (int) $variantId);
        }

        $productId = $request->query('product_id');
        if (is_numeric($productId)) {
            $query->whereHas('variant', static function (Builder $variantQuery) use ($productId): void {
                $variantQuery->where('product_id', (int) $productId);
            });
        }

        // Offer a warehouse code filter for integrations that reference physical storage identifiers.
        $warehouseCode = $request->query('warehouse_code');
        if (is_string($warehouseCode) && $warehouseCode !== '') {
            $query->where('warehouse_code', 'like', '%' . trim($warehouseCode) . '%');
        }

        // Allow incremental fetches by only returning records updated after the provided timestamp.
        $updatedSince = $request->query('updated_since');
        if (is_string($updatedSince) && $updatedSince !== '') {
            $parsedSince = CarbonImmutable::make($updatedSince);
            if ($parsedSince instanceof CarbonInterface) {
                $query->where('updated_at', '>=', $parsedSince);
            }
        }

        // Execute the query with pagination and normalise the items for the API response contract.
        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        // Preserve any active filters on pagination links so integrations can continue
        // traversing pages without having to manually re-apply query parameters.
        $paginated->appends($request->query());

        $inventory = $paginated->getCollection()
            ->map($this->transformInventory(...))
            ->values()
            ->all();

        return response()->json([
            'data' => [
                'inventory' => $inventory,
            ],
            'meta' => [
                'pagination' => [
                    'total'        => $paginated->total(),
                    'count'        => $paginated->count(),
                    'per_page'     => $paginated->perPage(),
                    'current_page' => $paginated->currentPage(),
                    'last_page'    => $paginated->lastPage(),
                    'links'        => [
                        'next' => $paginated->nextPageUrl(),
                        'prev' => $paginated->previousPageUrl(),
                    ],
                ],
                'filters' => [
                    'sku'            => is_string($sku) && $sku !== '' ? $sku : null,
                    'location_id'    => is_numeric($locationId) ? (int) $locationId : null,
                    'variant_id'     => is_numeric($variantId) ? (int) $variantId : null,
                    'product_id'     => is_numeric($productId) ? (int) $productId : null,
                    'warehouse_code' => is_string($warehouseCode) && $warehouseCode !== '' ? $warehouseCode : null,
                    'updated_since'  => is_string($updatedSince) && $updatedSince !== '' ? $updatedSince : null,
                ],
                'scopes' => array_values((array) $request->attributes->get('partner_api_abilities', [])),
            ],
        ]);
    }

    /**
     * Convert an inventory record into an associative array ready for JSON responses.
     *
     * @return array{
     *     id: int|string|null,
     *     variant_id: int|string|null,
     *     product_id: int|string|null,
     *     sku: string|null,
     *     product_sku: string|null,
     *     variant_sku: string|null,
     *     product_name: string|null,
     *     variant_name: string|null,
     *     warehouse_code: string|null,
     *     location: array{id: int|string|null, name: string|null, code: string|null},
     *     stock: int,
     *     reserved: int,
     *     available: int,
     *     incoming: int,
     *     threshold: int,
     *     reorder_point: int,
     *     reorder_quantity: int|null,
     *     max_stock_level: int|null,
     *     cost_per_unit: float|null,
     *     status: array{code: string|null, label: string|null, is_low_stock: bool, is_out_of_stock: bool, needs_reorder: bool},
     *     timestamps: array{created_at: string|null, updated_at: string|null, last_restocked_at: string|null, last_sold_at: string|null},
     * }
     */
    private function transformInventory(VariantInventory $inventory): array
    {
        /** @var ProductVariant|null $variant */
        $variant = $inventory->getRelationValue('variant');
        $variant = $variant instanceof ProductVariant ? $variant : null;

        /** @var Product|null $product */
        $product = $variant?->getRelationValue('product');
        $product = $product instanceof Product ? $product : null;

        /** @var Location|null $location */
        $location = $inventory->getRelationValue('location');
        $location = $location instanceof Location ? $location : null;

        $variantSku = $variant?->getAttribute('sku');
        $productSku = $product?->getAttribute('sku');

        $productName = $product?->getAttribute('name');
        $variantName = $variant?->getAttribute('name');
        $locationName = $location?->getAttribute('name');

        $warehouseCode = $inventory->getAttribute('warehouse_code');
        $stockValue = $inventory->getAttribute('stock');
        $reservedValue = $inventory->getAttribute('reserved');
        $availableValue = $inventory->getAttribute('available');
        $incomingValue = $inventory->getAttribute('incoming');
        $thresholdValue = $inventory->getAttribute('threshold');
        $reorderPointValue = $inventory->getAttribute('reorder_point');
        $reorderQuantity = $inventory->getAttribute('reorder_quantity');
        $maxStockLevel = $inventory->getAttribute('max_stock_level');
        $costPerUnit = $inventory->getAttribute('cost_per_unit');

        $stock = is_numeric($stockValue) ? (int) $stockValue : 0;
        $reserved = is_numeric($reservedValue) ? (int) $reservedValue : 0;
        $available = is_numeric($availableValue) ? (int) $availableValue : 0;
        $incoming = is_numeric($incomingValue) ? (int) $incomingValue : 0;
        $threshold = is_numeric($thresholdValue) ? (int) $thresholdValue : 0;
        $reorderPoint = is_numeric($reorderPointValue) ? (int) $reorderPointValue : 0;

        $createdAt = $inventory->getAttribute('created_at');
        $updatedAt = $inventory->getAttribute('updated_at');
        $lastRestockedAt = $inventory->getAttribute('last_restocked_at');
        $lastSoldAt = $inventory->getAttribute('last_sold_at');

        $id = $inventory->getKey();
        $id = is_int($id) || is_string($id) ? $id : null;

        $variantId = $variant?->getKey();
        $variantId = $variantId !== null && (is_int($variantId) || is_string($variantId)) ? $variantId : null;

        $productId = $product?->getKey();
        $productId = $productId !== null && (is_int($productId) || is_string($productId)) ? $productId : null;

        $locationId = $location?->getKey();
        $locationId = $locationId !== null && (is_int($locationId) || is_string($locationId)) ? $locationId : null;

        $locationCode = $location?->getAttribute('code');

        $stockStatus = $inventory->getAttribute('stock_status');
        $stockStatusLabel = $inventory->getAttribute('stock_status_label');

        return [
            'id'             => $id,
            'variant_id'     => $variantId,
            'product_id'     => $productId,
            'sku'            => is_string($variantSku) ? $variantSku : (is_string($productSku) ? $productSku : null),
            'product_sku'    => is_string($productSku) ? $productSku : null,
            'variant_sku'    => is_string($variantSku) ? $variantSku : null,
            'product_name'   => is_string($productName) ? $productName : null,
            'variant_name'   => is_string($variantName) ? $variantName : null,
            'warehouse_code' => is_string($warehouseCode) ? $warehouseCode : null,
            'location'       => [
                'id'   => $locationId,
                'name' => is_string($locationName) ? $locationName : null,
                'code' => is_string($locationCode) ? $locationCode : null,
            ],
            'stock'            => $stock,
            'reserved'         => $reserved,
            'available'        => $available,
            'incoming'         => $incoming,
            'threshold'        => $threshold,
            'reorder_point'    => $reorderPoint,
            'reorder_quantity' => is_numeric($reorderQuantity) ? (int) $reorderQuantity : null,
            'max_stock_level'  => is_numeric($maxStockLevel) ? (int) $maxStockLevel : null,
            'cost_per_unit'    => is_numeric($costPerUnit) ? (float) $costPerUnit : null,
            'status'           => [
                'code'            => is_string($stockStatus) ? $stockStatus : null,
                'label'           => is_string($stockStatusLabel) ? $stockStatusLabel : null,
                'is_low_stock'    => (bool) $inventory->is_low_stock,
                'is_out_of_stock' => (bool) $inventory->is_out_of_stock,
                'needs_reorder'   => (bool) $inventory->needs_reorder,
            ],
            'timestamps' => [
                'created_at'        => $this->formatTimestamp($createdAt instanceof CarbonInterface ? $createdAt : null),
                'updated_at'        => $this->formatTimestamp($updatedAt instanceof CarbonInterface ? $updatedAt : null),
                'last_restocked_at' => $this->formatTimestamp($lastRestockedAt instanceof CarbonInterface ? $lastRestockedAt : null),
                'last_sold_at'      => $this->formatTimestamp($lastSoldAt instanceof CarbonInterface ? $lastSoldAt : null),
            ],
        ];
    }

    /**
     * Render a Carbon timestamp as an ISO-8601 string when available.
     */
    private function formatTimestamp(?CarbonInterface $timestamp): ?string
    {
        return $timestamp instanceof CarbonInterface ? $timestamp->toAtomString() : null;
    }
}
