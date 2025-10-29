<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * InventoryController
 *
 * HTTP controller handling InventoryController related web requests, responses, and business logic with proper validation and error handling.
 */
final class InventoryController extends Controller
{
    /**
     * Recognised stock status keys for inventory filtering.
     */
    private const STOCK_STATUS_FILTERS = ['in_stock', 'low_stock', 'out_of_stock', 'not_tracked'];

    /**
     * Allowed columns for the sortable inventory listing.
     */
    private const ALLOWED_SORT_COLUMNS = ['name', 'sku', 'price', 'stock_quantity', 'created_at'];

    /**
     * Allowed sort directions so user input never leaks unsafe SQL fragments.
     */
    private const ALLOWED_SORT_DIRECTIONS = ['asc', 'desc'];

    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): View
    {
        // Start with the base inventory query eager loading relationships used by the Blade template.
        $query = Product::with(['brand', 'categories'])->where('is_visible', true)->published();

        // Apply request-driven filters in a predictable sequence to keep the query legible and extendable.
        $this->applyStockStatusFilter($query, $request);
        $this->applyBrandFilter($query, $request);
        $this->applyCategoryFilter($query, $request);
        $this->applySearchFilter($query, $request);
        $this->applySorting($query, $request);

        // Paginate results while preserving query parameters so filters persist across pages.
        $products = $query->paginate(20)->withQueryString();

        return view('inventory', ['products' => $products]);
    }

    /**
     * Apply the stock status filter when the request contains a recognised status value.
     *
     * @param Builder<Product> $query The query builder the filter should modify.
     */
    private function applyStockStatusFilter(Builder $query, Request $request): void
    {
        // Pull the user-provided status and normalise it to lowercase for safe comparisons.
        $rawStockStatus = $request->input('stock_status');

        if (! is_string($rawStockStatus)) {
            return;
        }

        $stockStatus = strtolower($rawStockStatus);

        if (! in_array($stockStatus, self::STOCK_STATUS_FILTERS, true)) {
            return;
        }

        // Wrap the conditional logic in a sub query so each stock status remains isolated.
        $query->where(static function (Builder $stockScopedQuery) use ($stockStatus): void {
            switch ($stockStatus) {
                case 'in_stock':
                    // In stock items are tracked, above zero, and above the configured threshold.
                    $stockScopedQuery
                        ->where('manage_stock', true)
                        ->whereColumn('stock_quantity', '>', 'low_stock_threshold');

                    break;

                case 'low_stock':
                    // Low stock items are tracked, above zero, and at or below their low stock threshold.
                    $stockScopedQuery
                        ->where('manage_stock', true)
                        ->where('stock_quantity', '>', 0)
                        ->whereColumn('stock_quantity', '<=', 'low_stock_threshold');

                    break;

                case 'out_of_stock':
                    // Out of stock items are tracked but have zero or negative quantity.
                    $stockScopedQuery
                        ->where('manage_stock', true)
                        ->where('stock_quantity', '<=', 0);

                    break;

                case 'not_tracked':
                    // Not tracked items ignore stock management entirely.
                    $stockScopedQuery->where('manage_stock', false);

                    break;
            }
        });
    }

    /**
     * Apply the brand filter by coercing the incoming identifier to an integer.
     *
     * @param Builder<Product> $query The query builder the filter should modify.
     */
    private function applyBrandFilter(Builder $query, Request $request): void
    {
        if (! $request->filled('brand')) {
            return;
        }

        // Use Laravel's integer helper to avoid casting surprises from malformed input.
        $brandId = $request->integer('brand');

        $query->where('brand_id', $brandId);
    }

    /**
     * Apply the category filter using a whereHas constraint so pivot tables remain encapsulated.
     *
     * @param Builder<Product> $query The query builder the filter should modify.
     */
    private function applyCategoryFilter(Builder $query, Request $request): void
    {
        if (! $request->filled('category')) {
            return;
        }

        $categoryId = $request->integer('category');

        $query->whereHas('categories', static function (Builder $categoryQuery) use ($categoryId): void {
            // whereKey keeps the lookup tied to the model's primary key definition.
            $categoryQuery->whereKey($categoryId);
        });
    }

    /**
     * Apply keyword searching against product metadata while trimming stray whitespace.
     *
     * @param Builder<Product> $query The query builder the filter should modify.
     */
    private function applySearchFilter(Builder $query, Request $request): void
    {
        if (! $request->filled('search')) {
            return;
        }

        $searchInput = $request->input('search');

        if (! is_string($searchInput)) {
            return;
        }

        $search = trim($searchInput);

        if ($search === '') {
            return;
        }

        $query->where(static function (Builder $searchQuery) use ($search): void {
            // Use LIKE expressions for name, sku, and description to provide a broad search surface.
            $likeExpression = "%{$search}%";

            $searchQuery
                ->where('name', 'like', $likeExpression)
                ->orWhere('sku', 'like', $likeExpression)
                ->orWhere('description', 'like', $likeExpression);
        });
    }

    /**
     * Apply the requested sort column and direction, falling back to safe defaults when invalid input is provided.
     *
     * @param Builder<Product> $query The query builder the filter should modify.
     */
    private function applySorting(Builder $query, Request $request): void
    {
        // Pull inputs without defaults so we can validate them before deciding on the final values.
        $sortByInput = $request->input('sort');
        $sortDirectionInput = $request->input('direction');

        $sortBy = 'name';

        if (is_string($sortByInput)) {
            $candidateColumn = strtolower($sortByInput);

            if (in_array($candidateColumn, self::ALLOWED_SORT_COLUMNS, true)) {
                $sortBy = $candidateColumn;
            }
        }

        $sortDirection = 'asc';

        if (is_string($sortDirectionInput)) {
            $candidateDirection = strtolower($sortDirectionInput);

            if (in_array($candidateDirection, self::ALLOWED_SORT_DIRECTIONS, true)) {
                $sortDirection = $candidateDirection;
            }
        }

        $query->orderBy($sortBy, $sortDirection);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Product $product): View
    {
        $product->load(['brand', 'categories', 'reviews', 'variants']);

        return view('products.show', ['product' => $product]);
    }
}
