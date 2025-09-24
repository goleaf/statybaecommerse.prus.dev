<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductHistory;
use App\Traits\HandlesContentNegotiation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * ProductHistoryController
 *
 * HTTP controller handling ProductHistoryController related web requests, responses, and business logic with proper validation and error handling.
 */
final class ProductHistoryController extends Controller
{
    use HandlesContentNegotiation;

    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request, Product $product): JsonResponse|View|Response
    {
        $query = $product->histories()->with(['user:id,name,email'])->latest();
        // Apply filters
        if ($request->has('action')) {
            $query->byAction($request->get('action'));
        }
        if ($request->has('field_name')) {
            $query->byField($request->get('field_name'));
        }
        if ($request->has('user_id')) {
            $query->byUser($request->get('user_id'));
        }
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->get('date_from'));
        }
        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->get('date_to'));
        }
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")->orWhere('action', 'like', "%{$search}%")->orWhere('field_name', 'like', "%{$search}%");
            });
        }
        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $histories = $query->paginate($perPage);
        $data = ['histories' => $histories->items(), 'pagination' => ['current_page' => $histories->currentPage(), 'last_page' => $histories->lastPage(), 'per_page' => $histories->perPage(), 'total' => $histories->total(), 'from' => $histories->firstItem(), 'to' => $histories->lastItem()], 'product' => ['id' => $product->id, 'name' => $product->name, 'sku' => $product->sku]];

        return $this->handleContentNegotiation($request, $data);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Request $request, Product $product, ProductHistory $history): JsonResponse|View|Response
    {
        if ($history->product_id !== $product->id) {
            return response()->json(['error' => 'History not found for this product'], 404);
        }
        $history->load(['user:id,name,email', 'product:id,name,sku']);
        $data = ['history' => $history, 'product' => ['id' => $product->id, 'name' => $product->name, 'sku' => $product->sku]];

        return $this->handleContentNegotiation($request, $data);
    }

    /**
     * Handle statistics functionality with proper error handling.
     */
    public function statistics(Request $request, Product $product): JsonResponse|View|Response
    {
        $totalChanges = $product->histories()->count();
        $recentChanges = $product->histories()->recent(7)->count();
        $changesByAction = $product->histories()->selectRaw('action, COUNT(*) as count')->groupBy('action')->pluck('count', 'action');
        $changesByField = $product->histories()->selectRaw('field_name, COUNT(*) as count')->whereNotNull('field_name')->groupBy('field_name')->pluck('count', 'field_name');
        $recentActivity = $product->histories()->with(['user:id,name'])->recent(7)->limit(5)->get(['id', 'action', 'field_name', 'description', 'created_at', 'user_id']);
        $priceChanges = $product->priceHistories()->count();
        $stockUpdates = $product->stockHistories()->count();
        $statusChanges = $product->statusHistories()->count();
        $data = ['statistics' => ['total_changes' => $totalChanges, 'recent_changes' => $recentChanges, 'changes_by_action' => $changesByAction, 'changes_by_field' => $changesByField, 'recent_activity' => $recentActivity, 'summary' => ['price_changes' => $priceChanges, 'stock_updates' => $stockUpdates, 'status_changes' => $statusChanges], 'change_frequency' => $product->getChangeFrequency(30), 'last_price_change' => $product->getLastPriceChange()?->created_at, 'last_stock_update' => $product->getLastStockUpdate()?->created_at, 'last_status_change' => $product->getLastStatusChange()?->created_at], 'product' => ['id' => $product->id, 'name' => $product->name, 'sku' => $product->sku]];

        return $this->handleContentNegotiation($request, $data);
    }

    /**
     * Handle export functionality with proper error handling.
     */
    public function export(Request $request, Product $product): Response
    {
        $query = $product->histories()->with(['user:id,name,email'])->latest();
        // Apply same filters as index
        if ($request->has('action')) {
            $query->byAction($request->get('action'));
        }
        if ($request->has('field_name')) {
            $query->byField($request->get('field_name'));
        }
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->get('date_from'));
        }
        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->get('date_to'));
        }
        $histories = $query->get();
        $csvData = "Date,Action,Field,Old Value,New Value,Description,User,IP Address\n";
        foreach ($histories as $history) {
            $csvData .= sprintf("%s,%s,%s,%s,%s,%s,%s,%s\n", $history->created_at->format('Y-m-d H:i:s'), $history->action, $history->field_name ?? 'N/A', is_array($history->old_value) ? json_encode($history->old_value) : $history->old_value ?? 'N/A', is_array($history->new_value) ? json_encode($history->new_value) : $history->new_value ?? 'N/A', str_replace(["\r", "\n"], ' ', $history->description ?? ''), $history->user?->name ?? 'System', $history->ip_address ?? 'N/A');
        }
        $filename = "product_history_{$product->sku}_".now()->format('Y-m-d_H-i-s').'.csv';

        return response($csvData)->header('Content-Type', 'text/csv; charset=UTF-8')->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, Product $product): JsonResponse
    {
        $request->validate(['action' => 'required|string|max:255', 'field_name' => 'nullable|string|max:255', 'old_value' => 'nullable', 'new_value' => 'nullable', 'description' => 'nullable|string|max:65535']);
        $history = ProductHistory::createHistoryEntry(product: $product, action: $request->get('action'), fieldName: $request->get('field_name'), oldValue: $request->get('old_value'), newValue: $request->get('new_value'), description: $request->get('description'), user: auth()->user());
        $history->load(['user:id,name,email']);

        return response()->json(['data' => $history, 'message' => 'History entry created successfully'], 201);
    }
}
