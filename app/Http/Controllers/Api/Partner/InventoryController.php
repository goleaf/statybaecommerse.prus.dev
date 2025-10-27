<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Partner;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DateTimeInterface;

final class InventoryController
{
    public function __invoke(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 50);
        $perPage = max(1, min($perPage, 100));

        $filters = [
            'sku'           => $this->resolveSkuFilter($request),
            'updated_since' => $this->resolveUpdatedSinceFilter($request),
        ];

        $query = VariantInventory::query()
            ->with([
                'variant' => static fn ($builder) => $builder->select([
                    'id',
                    'product_id',
                    'sku',
                    'name',
                ]),
                'variant.product' => static fn ($builder) => $builder->select([
                    'id',
                    'sku',
                    'name',
                ]),
                'location' => static fn ($builder) => $builder->select([
                    'id',
                    'name',
                    'code',
                ]),
            ])
            ->orderBy('id');

        if (is_string($filters['sku'])) {
            $query->where(static function (Builder $builder) use ($filters): void {
                $builder
                    ->whereHas('variant', static function (Builder $variantQuery) use ($filters): void {
                        $variantQuery->where('sku', $filters['sku']);
                    })
                    ->orWhereHas('variant.product', static function (Builder $productQuery) use ($filters): void {
                        $productQuery->where('sku', $filters['sku']);
                    });
            });
        }

        if ($filters['updated_since'] instanceof CarbonImmutable) {
            $query->where('updated_at', '>=', $filters['updated_since']);
        }

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($perPage)->appends($request->query());

        $inventory = $paginator->getCollection()
            ->map(fn (VariantInventory $inventory): array => $this->transformInventory($inventory))
            ->values()
            ->all();

        $abilities = Arr::wrap($request->attributes->get('partner_api_abilities', []));

        return response()->json([
            'data' => [
                'inventory' => $inventory,
            ],
            'meta' => [
                'filters'    => $this->formatFiltersForResponse($filters),
                'pagination' => $this->formatPagination($paginator),
                'scopes'     => $abilities,
            ],
        ]);
    }

    private function resolveSkuFilter(Request $request): ?string
    {
        $value = $request->input('sku');

        if (! is_string($value)) {
            return null;
        }

        $sku = trim($value);

        return $sku !== '' ? $sku : null;
    }

    private function resolveUpdatedSinceFilter(Request $request): ?CarbonImmutable
    {
        $value = $request->input('updated_since');

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formatFiltersForResponse(array $filters): array
    {
        return array_filter([
            'sku'           => $filters['sku'] ?? null,
            'updated_since' => isset($filters['updated_since']) && $filters['updated_since'] instanceof CarbonImmutable
                ? $filters['updated_since']->toAtomString()
                : null,
        ], static fn ($value): bool => $value !== null);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatPagination(LengthAwarePaginator $paginator): array
    {
        return [
            'per_page'      => $paginator->perPage(),
            'current_page'  => $paginator->currentPage(),
            'last_page'     => $paginator->lastPage(),
            'total'         => $paginator->total(),
            'from'          => $paginator->firstItem(),
            'to'            => $paginator->lastItem(),
            'links'         => [
                'next' => $paginator->nextPageUrl(),
                'prev' => $paginator->previousPageUrl(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformInventory(VariantInventory $inventory): array
    {
        $variant = $inventory->variant;
        $product = $variant?->product;
        $location = $inventory->location;

        return [
            'id'            => $inventory->getKey(),
            'product_id'    => $product?->getKey(),
            'product_sku'   => $product instanceof Product ? $product->sku : null,
            'product_name'  => $product instanceof Product ? $product->name : null,
            'variant_id'    => $variant?->getKey(),
            'variant_sku'   => $variant instanceof ProductVariant ? $variant->sku : null,
            'variant_name'  => $variant instanceof ProductVariant ? $variant->name : null,
            'location'      => [
                'id'   => $location?->getKey(),
                'code' => $location instanceof Location ? $location->code : null,
                'name' => $location instanceof Location ? $location->name : null,
            ],
            'warehouse_code'   => $inventory->warehouse_code,
            'stock'            => (int) $inventory->stock,
            'reserved'         => (int) $inventory->reserved,
            'available'        => (int) $inventory->available,
            'incoming'         => $inventory->incoming !== null ? (int) $inventory->incoming : null,
            'threshold'        => $inventory->threshold !== null ? (int) $inventory->threshold : null,
            'reorder_point'    => $inventory->reorder_point !== null ? (int) $inventory->reorder_point : null,
            'reorder_quantity' => $inventory->reorder_quantity !== null ? (int) $inventory->reorder_quantity : null,
            'max_stock_level'  => $inventory->max_stock_level !== null ? (int) $inventory->max_stock_level : null,
            'status'           => [
                'code'  => $inventory->stock_status,
                'label' => method_exists($inventory, 'getStockStatusLabelAttribute')
                    ? $inventory->stock_status_label
                    : ucfirst(str_replace('_', ' ', (string) $inventory->stock_status)),
            ],
            'last_restocked_at' => $this->formatDateTime($inventory->last_restocked_at),
            'last_sold_at'      => $this->formatDateTime($inventory->last_sold_at),
            'created_at'        => $this->formatDateTime($inventory->created_at),
            'updated_at'        => $this->formatDateTime($inventory->updated_at),
        ];
    }

    private function formatDateTime(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $carbon = CarbonImmutable::make($value);

        if ($carbon instanceof CarbonImmutable) {
            return $carbon->toAtomString();
        }

        if ($value instanceof CarbonInterface) {
            return $value->toAtomString();
        }

        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::make($value)?->toAtomString();
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed !== '' ? $trimmed : null;
        }

        return null;
    }
}
