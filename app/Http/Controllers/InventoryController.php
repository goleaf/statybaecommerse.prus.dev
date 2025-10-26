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
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): View
    {
        // Start with the base inventory query eager loading relationships used by the Blade template.
        $query = Product::with(['brand', 'categories'])->where('is_visible', true)->published();

        // Restrict stock status filtering to the recognised set so unexpected input cannot
        // break the column comparison logic or leak invalid SQL fragments.
        if ($request->filled('stock_status')) {
            $stockStatusInput = $request->input('stock_status');

            if (is_string($stockStatusInput) && in_array($stockStatusInput, ['in_stock', 'low_stock', 'out_of_stock', 'not_tracked'], true)) {
                $query->where(static function (Builder $stockScopedQuery) use ($stockStatusInput): void {
                    switch ($stockStatusInput) {
                        case 'in_stock':
                            $stockScopedQuery
                                ->where('manage_stock', true)
                                // Use whereColumn so the database safely compares column values without raw SQL.
                                ->whereColumn('stock_quantity', '>', 'low_stock_threshold');

                            break;

                        case 'low_stock':
                            $stockScopedQuery
                                ->where('manage_stock', true)
                                ->where('stock_quantity', '>', 0)
                                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold');

                            break;

                        case 'out_of_stock':
                            $stockScopedQuery
                                ->where('manage_stock', true)
                                ->where('stock_quantity', '<=', 0);

                            break;

                        case 'not_tracked':
                            $stockScopedQuery->where('manage_stock', false);

                            break;
                    }
                });
            }
        }

        // Normalise the incoming brand identifier to an integer to avoid unexpected casting quirks.
        if ($request->filled('brand')) {
            $brandId = $request->integer('brand');

            $query->where('brand_id', $brandId);
        }

        // Filter products by their attached categories using the relationship key instead of a raw column name.
        if ($request->filled('category')) {
            $categoryId = $request->integer('category');

            $query->whereHas('categories', static function (Builder $categoryQuery) use ($categoryId): void {
                $categoryQuery->whereKey($categoryId);
            });
        }

        // Trim the search term so accidental whitespace does not impact the lookup.
        if ($request->filled('search')) {
            $searchInput = $request->input('search');

            if (is_string($searchInput)) {
                $search = trim($searchInput);

                if ($search !== '') {
                    $query->where(static function (Builder $searchQuery) use ($search): void {
                        $likeExpression = "%{$search}%";

                        $searchQuery
                            ->where('name', 'like', $likeExpression)
                            ->orWhere('sku', 'like', $likeExpression)
                            ->orWhere('description', 'like', $likeExpression);
                    });
                }
            }
        }

        // Whitelist allowed sort columns and directions so the generated ORDER BY clause stays predictable.
        $allowedSorts = ['name', 'sku', 'price', 'stock_quantity', 'created_at'];
        $allowedDirections = ['asc', 'desc'];
        $sortByInput = $request->input('sort', 'name');
        $sortDirectionInput = $request->input('direction', 'asc');

        $sortBy = ! is_string($sortByInput) || ! in_array($sortByInput, $allowedSorts, true) ? 'name' : $sortByInput;

        if (! is_string($sortDirectionInput)) {
            $sortDirection = 'asc';
        } else {
            $sortDirection = strtolower($sortDirectionInput);

            if (! in_array($sortDirection, $allowedDirections, true)) {
                $sortDirection = 'asc';
            }
        }

        $query->orderBy($sortBy, $sortDirection);

        // Paginate results while preserving query parameters so filters persist across pages.
        $products = $query->paginate(20)->withQueryString();

        return view('inventory', ['products' => $products]);
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
