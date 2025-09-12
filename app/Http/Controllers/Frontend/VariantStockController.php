<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\VariantInventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class VariantStockController extends Controller
{
    public function index(Request $request): View
    {
        $query = VariantInventory::with(['variant.product', 'location', 'supplier'])
            ->whereHas('variant.product', function ($q) {
                $q->where('is_visible', true);
            });

        // Apply filters
        if ($request->filled('location')) {
            $query->where('location_id', $request->location);
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'low_stock' => $query->lowStock(),
                'out_of_stock' => $query->outOfStock(),
                'needs_reorder' => $query->needsReorder(),
                'expiring_soon' => $query->expiringSoon(),
                default => null,
            };
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('variant.product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('variant', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $variantStocks = $query->paginate(20);
        $locations = Location::enabled()->get();

        return view('frontend.variant-stock.index', compact('variantStocks', 'locations'));
    }

    public function show(VariantInventory $variantStock): View
    {
        $variantStock->load(['variant.product', 'location', 'supplier', 'stockMovements.user']);

        return view('frontend.variant-stock.show', compact('variantStock'));
    }

    public function checkAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'location_id' => 'nullable|exists:locations,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $query = VariantInventory::where('variant_id', $request->variant_id);

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        $inventory = $query->first();

        if (! $inventory) {
            return response()->json([
                'available' => false,
                'message' => __('inventory.not_available_at_location'),
            ]);
        }

        $available = $inventory->canReserve((int) $request->quantity);

        return response()->json([
            'available' => $available,
            'available_stock' => $inventory->available_stock,
            'message' => $available
                ? __('inventory.available_for_reservation')
                : __('inventory.insufficient_stock'),
        ]);
    }

    public function getStockByLocation(Request $request): JsonResponse
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
        ]);

        $stocks = VariantInventory::with('location')
            ->where('variant_id', $request->variant_id)
            ->where('is_tracked', true)
            ->get()
            ->map(function ($inventory) {
                return [
                    'location_id' => $inventory->location_id,
                    'location_name' => $inventory->location->name,
                    'available_stock' => $inventory->available_stock,
                    'stock_status' => $inventory->stock_status,
                    'stock_status_label' => $inventory->stock_status_label,
                ];
            });

        return response()->json($stocks);
    }

    public function getLowStockAlerts(): JsonResponse
    {
        $lowStockItems = VariantInventory::with(['variant.product', 'location'])
            ->lowStock()
            ->whereHas('variant.product', function ($q) {
                $q->where('is_visible', true);
            })
            ->limit(10)
            ->get()
            ->map(function ($inventory) {
                return [
                    'id' => $inventory->id,
                    'product_name' => $inventory->product_name,
                    'variant_name' => $inventory->variant_name,
                    'location_name' => $inventory->location_name,
                    'current_stock' => $inventory->stock,
                    'threshold' => $inventory->threshold,
                    'stock_status' => $inventory->stock_status,
                ];
            });

        return response()->json($lowStockItems);
    }
}
