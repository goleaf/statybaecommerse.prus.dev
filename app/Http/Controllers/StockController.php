<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\GenerateStockExport;
use App\Models\Location;
use App\Models\Partner;
use App\Models\VariantInventory;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * StockController
 *
 * HTTP controller handling StockController related web requests, responses, and business logic with proper validation and error handling.
 */
final class StockController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): View
    {
        $query = VariantInventory::with(['variant.product', 'location', 'supplier']);
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('variant.product', function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('variant', function ($q) use ($search): void {
                $q->where('sku', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%");
            });
        }
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->get('location_id'));
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->get('supplier_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->filled('stock_status')) {
            $stockStatus = $request->get('stock_status');
            match ($stockStatus) {
                'low_stock'     => $query->lowStock(),
                'out_of_stock'  => $query->outOfStock(),
                'needs_reorder' => $query->needsReorder(),
                'expiring_soon' => $query->expiringSoon(),
                default         => null,
            };
        }
        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);
        $stockItems = $query->paginate(20)->withQueryString();
        // Get filter options
        $locations = Location::enabled()->get();
        $suppliers = Partner::enabled()->get();

        return view('stock.index', ['stockItems' => $stockItems, 'locations' => $locations, 'suppliers' => $suppliers]);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(int $stockId): View
    {
        $stock = VariantInventory::with(['variant.product', 'location', 'supplier', 'stockMovements.user'])->findOrFail($stockId);

        return view('stock.show', ['stock' => $stock]);
    }

    /**
     * Handle adjustStock functionality with proper error handling.
     */
    public function adjustStock(\App\Http\Requests\Stock\AdjustStockRequest $request, int $stockId): JsonResponse
    {
        $stock = VariantInventory::findOrFail($stockId);
        $validated = $request->validated();
        try {
            $stock->adjustStock($validated['quantity'], $validated['reason']);

            return response()->json(['success' => true, 'message' => __('inventory.stock_adjusted'), 'data' => ['new_stock' => $stock->fresh()->stock, 'available_stock' => $stock->fresh()->available_stock]]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => __('inventory.adjustment_failed'), 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle reserveStock functionality with proper error handling.
     */
    public function reserveStock(\App\Http\Requests\Stock\ReserveStockRequest $request, int $stockId): JsonResponse
    {
        $stock = VariantInventory::findOrFail($stockId);
        $validated = $request->validated();
        try {
            if ($stock->reserve($validated['quantity'])) {
                return response()->json(['success' => true, 'message' => __('inventory.stock_reserved'), 'data' => ['reserved' => $stock->fresh()->reserved, 'available_stock' => $stock->fresh()->available_stock]]);
            } else {
                return response()->json(['success' => false, 'message' => __('inventory.reserve_failed_message')], 400);
            }
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => __('inventory.reserve_failed'), 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle unreserveStock functionality with proper error handling.
     */
    public function unreserveStock(\App\Http\Requests\Stock\UnreserveStockRequest $request, int $stockId): JsonResponse
    {
        $stock = VariantInventory::findOrFail($stockId);
        $validated = $request->validated();
        try {
            $stock->unreserve($validated['quantity']);

            return response()->json(['success' => true, 'message' => __('inventory.stock_unreserved'), 'data' => ['reserved' => $stock->fresh()->reserved, 'available_stock' => $stock->fresh()->available_stock]]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => __('inventory.unreserve_failed'), 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle getStockMovements functionality with proper error handling.
     */
    public function getStockMovements(int $stockId): JsonResponse
    {
        $movements = VariantInventory::findOrFail($stockId)
            ->stockMovements()
            ->with('user')
            ->latest('moved_at')
            ->paginate(20);

        return response()->json($movements);
    }

    /**
     * Handle getStockReport functionality with proper error handling.
     */
    public function getStockReport(Request $request): View
    {
        $query = VariantInventory::with(['variant.product', 'location', 'supplier']);
        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->get('date_to'));
        }
        // Apply location filter
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->get('location_id'));
        }
        $stockItems = $query->get();
        // Calculate summary statistics
        $summary = ['total_items' => $stockItems->count(), 'total_stock_value' => $stockItems->sum('stock_value'), 'total_reserved_value' => $stockItems->sum('reserved_value'), 'low_stock_items' => $stockItems->filter(fn ($item) => $item->isLowStock())->count(), 'out_of_stock_items' => $stockItems->filter(fn ($item) => $item->isOutOfStock())->count(), 'needs_reorder_items' => $stockItems->filter(fn ($item) => $item->needsReorder())->count()];
        // Group by location
        $byLocation = $stockItems->groupBy('location.name')->map(fn ($items): array => ['count' => $items->count(), 'total_value' => $items->sum('stock_value'), 'reserved_value' => $items->sum('reserved_value'), 'low_stock' => $items->filter(fn ($item) => $item->isLowStock())->count(), 'out_of_stock' => $items->filter(fn ($item) => $item->isOutOfStock())->count()]);
        // Group by supplier
        $bySupplier = $stockItems->groupBy('supplier.name')->map(fn ($items): array => ['count' => $items->count(), 'total_value' => $items->sum('stock_value'), 'reserved_value' => $items->sum('reserved_value'), 'low_stock' => $items->filter(fn ($item) => $item->isLowStock())->count(), 'out_of_stock' => $items->filter(fn ($item) => $item->isOutOfStock())->count()]);
        $locations = Location::enabled()->get();

        return view('stock.report', ['stockItems' => $stockItems, 'summary' => $summary, 'byLocation' => $byLocation, 'bySupplier' => $bySupplier, 'locations' => $locations]);
    }

    /**
     * Handle exportStock functionality with proper error handling.
     */
    public function exportStock(Request $request): RedirectResponse
    {
        $filters = $request->only(['location_id', 'supplier_id', 'status', 'stock_status', 'search']);
        GenerateStockExport::dispatch($filters, $request->user()?->id);

        return redirect()
            ->route('exports.index')
            ->with('success', __('inventory.export_job_queued'));
    }
}
