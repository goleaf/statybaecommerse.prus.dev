<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Partner;
use App\Models\VariantInventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\LazyCollection;
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
            $query->whereHas('variant.product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('variant', function ($q) use ($search) {
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
                'low_stock' => $query->lowStock(),
                'out_of_stock' => $query->outOfStock(),
                'needs_reorder' => $query->needsReorder(),
                'expiring_soon' => $query->expiringSoon(),
                default => null,
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

        return view('stock.index', compact('stockItems', 'locations', 'suppliers'));
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(int $stockId): View
    {
        $stock = VariantInventory::with(['variant.product', 'location', 'supplier', 'stockMovements.user'])->findOrFail($stockId);

        return view('stock.show', compact('stock'));
    }

    /**
     * Handle adjustStock functionality with proper error handling.
     */
    public function adjustStock(Request $request, int $stockId): JsonResponse
    {
        $stock = VariantInventory::findOrFail($stockId);
        $request->validate(['quantity' => 'required|integer', 'reason' => 'required|string|in:sale,return,adjustment,manual_adjustment,restock,damage,theft,transfer', 'notes' => 'nullable|string|max:1000']);
        try {
            $stock->adjustStock($request->quantity, $request->reason);

            return response()->json(['success' => true, 'message' => __('inventory.stock_adjusted'), 'data' => ['new_stock' => $stock->fresh()->stock, 'available_stock' => $stock->fresh()->available_stock]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => __('inventory.adjustment_failed'), 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle reserveStock functionality with proper error handling.
     */
    public function reserveStock(Request $request, int $stockId): JsonResponse
    {
        $stock = VariantInventory::findOrFail($stockId);
        $request->validate(['quantity' => 'required|integer|min:1|max:'.$stock->available_stock, 'notes' => 'nullable|string|max:1000']);
        try {
            if ($stock->reserve($request->quantity)) {
                return response()->json(['success' => true, 'message' => __('inventory.stock_reserved'), 'data' => ['reserved' => $stock->fresh()->reserved, 'available_stock' => $stock->fresh()->available_stock]]);
            } else {
                return response()->json(['success' => false, 'message' => __('inventory.reserve_failed_message')], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => __('inventory.reserve_failed'), 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle unreserveStock functionality with proper error handling.
     */
    public function unreserveStock(Request $request, int $stockId): JsonResponse
    {
        $stock = VariantInventory::findOrFail($stockId);
        $request->validate(['quantity' => 'required|integer|min:1|max:'.$stock->reserved, 'notes' => 'nullable|string|max:1000']);
        try {
            $stock->unreserve($request->quantity);

            return response()->json(['success' => true, 'message' => __('inventory.stock_unreserved'), 'data' => ['reserved' => $stock->fresh()->reserved, 'available_stock' => $stock->fresh()->available_stock]]);
        } catch (\Exception $e) {
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
        $byLocation = $stockItems->groupBy('location.name')->map(function ($items) {
            return ['count' => $items->count(), 'total_value' => $items->sum('stock_value'), 'reserved_value' => $items->sum('reserved_value'), 'low_stock' => $items->filter(fn ($item) => $item->isLowStock())->count(), 'out_of_stock' => $items->filter(fn ($item) => $item->isOutOfStock())->count()];
        });
        // Group by supplier
        $bySupplier = $stockItems->groupBy('supplier.name')->map(function ($items) {
            return ['count' => $items->count(), 'total_value' => $items->sum('stock_value'), 'reserved_value' => $items->sum('reserved_value'), 'low_stock' => $items->filter(fn ($item) => $item->isLowStock())->count(), 'out_of_stock' => $items->filter(fn ($item) => $item->isOutOfStock())->count()];
        });
        $locations = Location::enabled()->get();

        return view('stock.report', compact('stockItems', 'summary', 'byLocation', 'bySupplier', 'locations'));
    }

    /**
     * Handle exportStock functionality with proper error handling.
     *
     * @return Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportStock(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = VariantInventory::with(['variant.product', 'location', 'supplier']);
        // Apply filters
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->get('location_id'));
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->get('supplier_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        $filename = 'stock_export_'.now()->format('Y-m-d_H-i-s').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            // CSV headers
            fputcsv($handle, [__('inventory.product'), __('inventory.variant'), __('inventory.location'), __('inventory.supplier'), __('inventory.current_stock'), __('inventory.reserved'), __('inventory.available'), __('inventory.cost_per_unit'), __('inventory.stock_value'), __('inventory.status'), __('inventory.expiry_date'), __('inventory.created_at')]);
            // Use LazyCollection with timeout to prevent long-running export operations
            $timeout = now()->addMinutes(15);
            // 15 minute timeout for stock exports
            $query->cursor()->takeUntilTimeout($timeout)->each(function ($item) use ($handle) {
                fputcsv($handle, [$item->variant->product->name, $item->variant->display_name, $item->location->name, $item->supplier?->name ?? '', $item->stock, $item->reserved, $item->available_stock, $item->cost_per_unit, $item->stock_value, $item->stock_status_label, $item->expiry_date?->format('Y-m-d') ?? '', $item->created_at->format('Y-m-d H:i:s')]);
            });
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="'.$filename.'"']);
    }
}
