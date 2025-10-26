<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Partner;

use App\Models\Category;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class InventoryController
{
    public function __construct(
        /**
         * The inventory service centralises stock calculations for both products and variants.
         */
        private InventoryService $inventoryService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        // Normalise the requested limit to keep the endpoint predictable and safe.
        $limit = (int) $request->integer('limit', 25);
        $limit = max(1, min($limit, 100));

        // Gather the latest summary numbers so partners can reconcile their catalogues.
        $summary = $this->inventoryService->getInventorySummary();

        // Provide a concise look at products that require manual attention (low/out of stock).
        $lowStockCollection = $this->inventoryService->getLowStockProducts($limit);

        $lowStockProducts = $lowStockCollection
            ->map(function (Model $model): array {
                assert($model instanceof Product);

                return $this->formatProduct($model);
            })
            ->values()
            ->all();

        $outOfStockCollection = $this->inventoryService->getOutOfStockProducts($limit);

        $outOfStockProducts = $outOfStockCollection
            ->map(function (Model $model): array {
                assert($model instanceof Product);

                return $this->formatProduct($model);
            })
            ->values()
            ->all();

        return response()->json([
            'data' => [
                'inventory' => [
                    'generated_at' => now()->toISOString(),
                    'summary'      => $summary,
                    'low_stock'    => $lowStockProducts,
                    'out_of_stock' => $outOfStockProducts,
                ],
            ],
        ]);
    }

    /**
     * Transform a product model into a lightweight payload for the API response.
     *
     * @return array<string, mixed>
     */
    private function formatProduct(Product $product): array
    {
        // Extract the essential product identifiers and inventory attributes.
        $inventory = $product->getInventoryInfo();

        $categories = $product->categories;

        $normalizedCategories = $categories
            ->map(function (Model $model): array {
                assert($model instanceof Category);

                return $model->only(['id', 'name', 'slug']);
            })
            ->values()
            ->all();

        return [
            'id'         => $product->getKey(),
            'sku'        => $product->sku,
            'name'       => $product->name,
            'inventory'  => $inventory,
            'brand'      => $product->brand?->only(['id', 'name', 'slug']),
            'categories' => $normalizedCategories,
            'updated_at' => $product->updated_at?->toISOString(),
        ];
    }
}
