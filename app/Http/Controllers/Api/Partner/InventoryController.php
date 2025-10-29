<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Partner;

use App\Models\Product;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use DateTimeInterface;

final class InventoryController
{
    public function __invoke(Request $request): JsonResponse
    {
        $limit = $this->resolveLimit($request);

        $filters = [
            'sku'           => $this->resolveSkuFilter($request),
            'updated_since' => $this->resolveUpdatedSinceFilter($request),
        ];

        $query = Product::query()
            ->select([
                'id',
                'name',
                'sku',
                'manage_stock',
                'stock_quantity',
                'low_stock_threshold',
                'updated_at',
                'created_at',
            ])
            ->orderBy('id');

        if (is_string($filters['sku'])) {
            // Direct SKU filtering allows partners to scope the summary to a single product.
            $query->where('sku', $filters['sku']);
        }

        if ($filters['updated_since'] instanceof CarbonImmutable) {
            $query->where('updated_at', '>=', $filters['updated_since']);
        }

        /** @var Collection<int, Product> $products */
        $products = $query->get();

        // Build the grouped response so partners can quickly identify low or out-of-stock items
        // alongside aggregate visibility for dashboards without additional pagination requests.
        $summary = $this->buildSummary($products);
        $lowStockProducts = $this->transformProducts(
            $products
                ->filter(static fn (Product $product): bool => $product->manage_stock && ! $product->isOutOfStock() && $product->isLowStock())
                ->take($limit)
        );
        $outOfStockProducts = $this->transformProducts(
            $products
                ->filter(static fn (Product $product): bool => $product->manage_stock && $product->isOutOfStock())
                ->take($limit)
        );

        $abilities = Arr::wrap($request->attributes->get('partner_api_abilities', []));

        return response()->json([
            'data' => [
                'inventory' => [
                    'summary'      => $summary,
                    'low_stock'    => $lowStockProducts,
                    'out_of_stock' => $outOfStockProducts,
                ],
            ],
            'meta' => [
                'filters'    => $this->formatFiltersForResponse($filters, $limit),
                'pagination' => $this->formatPaginationSummary($products, $limit),
                'scopes'     => $abilities,
            ],
        ]);
    }

    private function resolveLimit(Request $request): int
    {
        $value = $request->integer('limit');

        if ($value === null) {
            // Preserve backwards compatibility with historical `per_page` requests.
            $value = $request->integer('per_page');
        }

        if ($value === null) {
            $value = 25;
        }

        // Clamp the limit to a sane range so partners cannot overload the endpoint with massive payloads.
        return max(1, min((int) $value, 50));
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
    private function formatFiltersForResponse(array $filters, int $limit): array
    {
        return array_filter([
            'sku'           => $filters['sku'] ?? null,
            'updated_since' => isset($filters['updated_since']) && $filters['updated_since'] instanceof CarbonImmutable
                ? $filters['updated_since']->toAtomString()
                : null,
            'limit'         => $limit,
        ], static fn ($value): bool => $value !== null);
    }

    private function formatPaginationSummary(Collection $products, int $limit): array
    {
        $total = $products->count();

        // Compute a faux pagination payload so legacy consumers relying on pagination keys continue to function.
        return [
            'per_page'     => $limit,
            'current_page' => 1,
            'last_page'    => 1,
            'total'        => $total,
            'from'         => $total > 0 ? 1 : 0,
            'to'           => $total > 0 ? min($limit, $total) : 0,
            'links'        => [
                'next' => null,
                'prev' => null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformProduct(Product $product): array
    {
        return [
            'id'   => $product->getKey(),
            'sku'  => $product->sku,
            'name' => $product->name,
            'inventory' => [
                'manage_stock'        => (bool) $product->manage_stock,
                'stock_quantity'      => (int) ($product->stock_quantity ?? 0),
                'low_stock_threshold' => (int) ($product->low_stock_threshold ?? 0),
                'available_quantity'  => $product->availableQuantity(),
                'is_in_stock'         => $product->manage_stock ? $product->isInStock() : false,
                'is_low_stock'        => $product->manage_stock ? $product->isLowStock() : false,
                'is_out_of_stock'     => $product->manage_stock ? $product->isOutOfStock() : false,
            ],
            'updated_at' => $this->formatDateTime($product->updated_at),
        ];
    }

    /**
     * @param Collection<int, Product> $products
     * @return array<int, array<string, mixed>>
     */
    private function transformProducts(Collection $products): array
    {
        // Map each product into the lightweight structure expected by the partner contract.
        return $products
            ->sortBy(static fn (Product $product): int => $product->getKey())
            ->values()
            ->map(fn (Product $product): array => $this->transformProduct($product))
            ->all();
    }

    /**
     * @param Collection<int, Product> $products
     * @return array<string, int>
     */
    private function buildSummary(Collection $products): array
    {
        $totalProducts = $products->count();
        $trackedProducts = $products->filter(static fn (Product $product): bool => (bool) $product->manage_stock);

        // Separate tracked products into availability buckets so partners see a concise breakdown.
        $outOfStockCount = $trackedProducts->filter(static fn (Product $product): bool => $product->isOutOfStock())->count();
        $lowStockCount = $trackedProducts
            ->filter(static fn (Product $product): bool => ! $product->isOutOfStock() && $product->isLowStock())
            ->count();
        $inStockCount = $trackedProducts
            ->filter(static fn (Product $product): bool => ! $product->isLowStock() && ! $product->isOutOfStock())
            ->count();

        return [
            'total_products'   => $totalProducts,
            'tracked_products' => $trackedProducts->count(),
            'in_stock'         => $inStockCount,
            'low_stock'        => $lowStockCount,
            'out_of_stock'     => $outOfStockCount,
            'not_tracked'      => $totalProducts - $trackedProducts->count(),
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
