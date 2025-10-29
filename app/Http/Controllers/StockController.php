<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\GenerateStockExport;
use App\Models\Location;
use App\Models\Partner;
use App\Models\VariantInventory;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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
            $search = $request->string('search')->toString();

            $query->where(function (Builder $builder) use ($search): void {
                // Group the search constraints so that downstream filters (like location or status)
                // are not bypassed by OR clauses that would otherwise short-circuit the query.
                $builder
                    ->whereHas('variant.product', function (Builder $productQuery) use ($search): void {
                        $productQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('variant', function (Builder $variantQuery) use ($search): void {
                        $variantQuery
                            ->where('sku', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
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
        $allowedSorts = [
            // Map external sort keys to fully-qualified columns to prevent SQL injection.
            'created_at'       => 'variant_inventories.created_at',
            'stock'            => 'variant_inventories.stock',
            'reserved'         => 'variant_inventories.reserved',
            'available'        => 'variant_inventories.available',
            'reorder_point'    => 'variant_inventories.reorder_point',
            'reorder_quantity' => 'variant_inventories.reorder_quantity',
            'status'           => 'variant_inventories.status',
        ];

        $requestedSort = $request->get('sort_by', 'created_at');
        if (! is_string($requestedSort) || ! array_key_exists($requestedSort, $allowedSorts)) {
            // Fall back to the default when the client submits an unknown sort key.
            $requestedSort = 'created_at';
        }
        $sortColumn = $allowedSorts[$requestedSort];

        $sortDirectionInput = $request->get('sort_direction', 'desc');
        $sortDirection = is_string($sortDirectionInput)
            ? strtolower($sortDirectionInput)
            : 'desc';
        if (! in_array($sortDirection, ['asc', 'desc'], true)) {
            // Default to descending order whenever an unexpected direction is provided.
            $sortDirection = 'desc';
        }

        // Reset any implicit ordering (from scopes or relationship eager loads) before
        // applying the explicit sort so that fallback logic remains predictable.
        $query->reorder();

        if ($sortDirection === 'desc') {
            // Use an explicit descending clause so the database cannot default to
            // ascending order when the client provides an invalid direction.
            $query->orderByDesc($sortColumn);
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        // Apply a deterministic tie-breaker on the primary key so inventories created
        // at the exact same moment still respect the chosen direction consistently.
        $query->orderBy(
            'variant_inventories.id',
            $sortDirection === 'desc' ? 'desc' : 'asc'
        );
        $stockItems = $query->paginate(20)->withQueryString();
        // Get filter options
        $locations = Location::enabled()->get();
        $suppliers = Partner::enabled()->get();

        /** @var view-string $indexView */
        $indexView = 'stock.index';

        return view($indexView, ['stockItems' => $stockItems, 'locations' => $locations, 'suppliers' => $suppliers]);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(int $stockId): View
    {
        $stock = VariantInventory::with(['variant.product', 'location', 'supplier', 'stockMovements.user'])->findOrFail($stockId);

        /** @var view-string $showView */
        $showView = 'stock.show';

        return view($showView, ['stock' => $stock]);
    }

    /**
     * Handle adjustStock functionality with proper error handling.
     */
    public function adjustStock(\App\Http\Requests\Stock\AdjustStockRequest $request, int $stockId): JsonResponse
    {
        $stock = VariantInventory::findOrFail($stockId);
        /** @var array{quantity:int|string, reason:string, notes?:string|null} $validated */
        $validated = $request->validated();
        try {
            $actorId = Auth::id();
            if (is_string($actorId)) {
                // Support guards that surface the user identifier as a string.
                $actorId = (int) $actorId;
            }
            $correlationId = Str::uuid()->toString();
            // Cast optional notes to a string so downstream logging remains consistent.
            $notes = isset($validated['notes']) ? (string) $validated['notes'] : null;
            $quantity = (int) $validated['quantity'];
            $reason = (string) $validated['reason'];

            // Trigger an atomic adjustment that also records an audit row.
            $adjusted = $stock->adjustStock($quantity, $reason, $actorId, $correlationId, null, $notes);

            if (! $adjusted) {
                return response()->json(['success' => false, 'message' => __('inventory.adjustment_failed')], 400);
            }

            $fresh = $stock->fresh() ?? $stock;

            return response()->json(['success' => true, 'message' => __('inventory.stock_adjusted'), 'data' => ['new_stock' => $fresh->stock, 'available_stock' => $fresh->available_stock, 'correlation_id' => $correlationId]]);
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
        /** @var array{quantity:int|string} $validated */
        $validated = $request->validated();
        try {
            $quantity = (int) $validated['quantity'];

            if ($stock->reserve($quantity)) {
                // Refresh the model once so we return consistent post-reservation values.
                $freshStock = $stock->fresh() ?? $stock;

                return response()->json(['success' => true, 'message' => __('inventory.stock_reserved'), 'data' => ['reserved' => $freshStock->reserved, 'available_stock' => $freshStock->available_stock]]);
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
        /** @var array{quantity:int|string} $validated */
        $validated = $request->validated();
        try {
            $quantity = (int) $validated['quantity'];
            $stock->unreserve($quantity);

            // Refresh once so the response mirrors the persisted inventory state.
            $freshStock = $stock->fresh() ?? $stock;

            return response()->json(['success' => true, 'message' => __('inventory.stock_unreserved'), 'data' => ['reserved' => $freshStock->reserved, 'available_stock' => $freshStock->available_stock]]);
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
        $summary = ['total_items' => $stockItems->count(), 'total_stock_value' => $stockItems->sum('stock_value'), 'total_reserved_value' => $stockItems->sum('reserved_value'), 'low_stock_items' => $stockItems->filter(fn (VariantInventory $item): bool => $item->is_low_stock)->count(), 'out_of_stock_items' => $stockItems->filter(fn (VariantInventory $item): bool => $item->is_out_of_stock)->count(), 'needs_reorder_items' => $stockItems->filter(fn (VariantInventory $item): bool => $item->needs_reorder)->count()];
        // Group by location
        $byLocation = $stockItems->groupBy('location.name')->map(fn ($items): array => ['count' => $items->count(), 'total_value' => $items->sum('stock_value'), 'reserved_value' => $items->sum('reserved_value'), 'low_stock' => $items->filter(fn (VariantInventory $item): bool => $item->is_low_stock)->count(), 'out_of_stock' => $items->filter(fn (VariantInventory $item): bool => $item->is_out_of_stock)->count()]);
        // Group by supplier
        $bySupplier = $stockItems->groupBy('supplier.name')->map(fn ($items): array => ['count' => $items->count(), 'total_value' => $items->sum('stock_value'), 'reserved_value' => $items->sum('reserved_value'), 'low_stock' => $items->filter(fn (VariantInventory $item): bool => $item->is_low_stock)->count(), 'out_of_stock' => $items->filter(fn (VariantInventory $item): bool => $item->is_out_of_stock)->count()]);
        $locations = Location::enabled()->get();

        /** @var view-string $reportView */
        $reportView = 'stock.report';

        return view($reportView, ['stockItems' => $stockItems, 'summary' => $summary, 'byLocation' => $byLocation, 'bySupplier' => $bySupplier, 'locations' => $locations]);
    }

    /**
     * Handle exportStock functionality with proper error handling.
     */
    public function exportStock(Request $request): RedirectResponse
    {
        /** @var array<string, mixed> $filters */
        $filters = $request->only(['location_id', 'supplier_id', 'status', 'stock_status', 'search']);
        GenerateStockExport::dispatch($filters, $request->user()?->id);

        return redirect()
            ->route('exports.index')
            ->with('success', __('inventory.export_job_queued'));
    }
}
