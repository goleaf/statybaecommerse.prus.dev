<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Data\ExportRequestData;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\User;
use App\Services\Export\ExportService;
use App\Services\Export\Exporters\ProductHistoryExport;
use App\Support\ListQuery\ListQueryValidator;
use App\Support\ListQuery\ListResponse;
use App\Support\ProductHistory\ProductHistoryListConfiguration;
use App\Traits\HandlesContentNegotiation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ProductHistoryController
 *
 * HTTP controller handling ProductHistoryController related web requests, responses, and business logic with proper validation and error handling.
 */
final class ProductHistoryController extends Controller
{
    use HandlesContentNegotiation;

    public function __construct(private readonly ExportService $exportService) {}

    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request, Product $product): JsonResponse|View|Response
    {
        $this->authorize('viewAny', [ProductHistory::class, $product]);

        $definition = ProductHistoryListConfiguration::definition();
        $listQuery = ListQueryValidator::fromRequest($request, $definition);

        $query = $product
            ->histories()
            ->with(['user:id,name,email']);

        // Apply the allow-listed filters and sorts so callers cannot project
        // arbitrary columns or SQL snippets into the query builder.
        $listQuery->applyFilters($query);
        $listQuery->applySorts($query);

        if (! $listQuery->hasSort('created_at')) {
            $query->orderByDesc('product_histories.created_at');
        }

        // Always fall back to a deterministic secondary ordering to stabilise
        // pagination windows when multiple history records share timestamps.
        $query->orderByDesc('product_histories.id');

        $histories = $query->paginate($listQuery->perPage(), ['*'], 'page', $listQuery->page());

        $payload = ListResponse::fromPaginator($histories, $listQuery, [
            'histories' => $histories->items(),
            'product' => [
                'id' => $product->getKey(),
                'name' => $product->name,
                'sku' => $product->sku,
            ],
        ]);

        return $this->handleContentNegotiation($request, $payload);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Request $request, Product $product, ProductHistory $history): JsonResponse|View|Response
    {
        if ($history->product_id !== $product->getKey()) {
            return response()->json(['error' => 'History not found for this product'], 404);
        }

        $this->authorize('view', [$history, $product]);

        $history->load(['user:id,name,email', 'product:id,name,sku']);
        $data = [
            'history' => $history,
            'product' => [
                'id' => $product->getKey(),
                'name' => $product->name,
                'sku' => $product->sku,
            ],
        ];

        return $this->handleContentNegotiation($request, $data);
    }

    /**
     * Handle statistics functionality with proper error handling.
     */
    public function statistics(Request $request, Product $product): JsonResponse|View|Response
    {
        $this->authorize('statistics', [ProductHistory::class, $product]);

        $definition = ProductHistoryListConfiguration::definition();
        $listQuery = ListQueryValidator::fromRequest($request, $definition);

        $baseQuery = ProductHistory::query()->where('product_id', $product->getKey());
        $listQuery->applyFilters($baseQuery);

        $totalChanges = (clone $baseQuery)->count();
        $recentChanges = (clone $baseQuery)->where('created_at', '>=', now()->subDays(7))->count();
        $changesByAction = (clone $baseQuery)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action');
        $changesByField = (clone $baseQuery)
            ->selectRaw('field_name, COUNT(*) as count')
            ->whereNotNull('field_name')
            ->groupBy('field_name')
            ->pluck('count', 'field_name');
        $recentActivity = (clone $baseQuery)
            ->with(['user:id,name'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'action', 'field_name', 'description', 'created_at', 'user_id']);
        $priceChanges = (clone $baseQuery)->where('field_name', 'price')->count();
        $stockUpdates = (clone $baseQuery)->where('field_name', 'stock_quantity')->count();
        $statusChanges = (clone $baseQuery)->where('field_name', 'status')->count();

        $data = [
            'statistics' => [
                'total_changes' => $totalChanges,
                'recent_changes' => $recentChanges,
                'changes_by_action' => $changesByAction,
                'changes_by_field' => $changesByField,
                'recent_activity' => $recentActivity,
                'summary' => [
                    'price_changes' => $priceChanges,
                    'stock_updates' => $stockUpdates,
                    'status_changes' => $statusChanges,
                ],
                'change_frequency' => $product->getChangeFrequency(30),
                'last_price_change' => $product->getLastPriceChange()?->created_at,
                'last_stock_update' => $product->getLastStockUpdate()?->created_at,
                'last_status_change' => $product->getLastStatusChange()?->created_at,
            ],
            'product' => [
                'id' => $product->getKey(),
                'name' => $product->name,
                'sku' => $product->sku,
            ],
            'meta' => ListResponse::meta($listQuery),
        ];

        return $this->handleContentNegotiation($request, $data);
    }

    /**
     * Handle export functionality with proper error handling.
     */
    public function export(Request $request, Product $product): StreamedResponse
    {
        $this->authorize('export', [ProductHistory::class, $product]);

        $definition = ProductHistoryListConfiguration::definition();
        $listQuery = ListQueryValidator::fromRequest($request, $definition);

        $filters = $listQuery->filters();
        $user = $request->user();

        $exportRequest = new ExportRequestData(
            name: sprintf('product-history-%s', $product->sku ?? $product->getKey()),
            exportable: ProductHistoryExport::class,
            format: (string) $request->input('format', 'csv'),
            columns: $this->resolveExportColumns($request),
            filters: array_merge($filters, ['product_id' => $product->getKey()]),
            userId: $user instanceof User ? $user->getKey() : null,
            meta: [
                'product_id' => $product->getKey(),
                'product_name' => $product->name,
                'query' => [
                    'filters' => $filters,
                ],
            ],
        );

        $export = $user instanceof User
            ? $this->exportService->queueExport($exportRequest, $user)
            : $this->exportService->queue($exportRequest);

        $downloadUrl = URL::temporarySignedRoute(
            'api.exports.download',
            now()->addMinutes(5),
            ['export' => $export->uuid]
        );

        // Stream a single server-sent event so the caller receives the queued
        // export identifier immediately without waiting for the job to finish.
        return response()->stream(function () use ($export, $downloadUrl): void {
            $payload = [
                'event' => 'queued',
                'export_id' => $export->getKey(),
                'uuid' => $export->uuid,
                'status' => (string) $export->status,
                'download_url' => $downloadUrl,
            ];

            $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($encoded === false) {
                $encoded = json_encode(['event' => 'queued'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{"event":"queued"}';
            }

            echo 'data: ' . $encoded . "\n\n";
            if (function_exists('ob_flush')) {
                @ob_flush();
            }
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        $this->authorize('create', [ProductHistory::class, $product]);

        $validated = $request->validate([
            'action' => ['required', 'string', 'max:255'],
            'field_name' => ['nullable', 'string', 'max:255'],
            'old_value' => ['nullable'],
            'new_value' => ['nullable'],
            'description' => ['nullable', 'string', 'max:65535'],
        ]);

        $actor = $request->user();
        $history = ProductHistory::createHistoryEntry(
            product: $product,
            action: $validated['action'],
            fieldName: $validated['field_name'] ?? null,
            oldValue: $validated['old_value'] ?? null,
            newValue: $validated['new_value'] ?? null,
            description: $validated['description'] ?? null,
            user: $actor instanceof User ? $actor : null,
        );

        $history->load(['user:id,name,email']);

        return response()->json([
            'data' => $history,
            'message' => 'History entry created successfully',
        ], 201);
    }

    /**
     * Normalise the requested export columns against the allow-listed set.
     *
     * @return array<int, string>
     */
    private function resolveExportColumns(Request $request): array
    {
        $requested = $request->input('columns', []);
        $columns = [];
        $allowed = ProductHistoryExport::allowedColumnKeys();

        foreach ((array) $requested as $column) {
            if (! is_string($column)) {
                continue;
            }

            $column = trim($column);

            if ($column === '' || ! in_array($column, $allowed, true)) {
                continue;
            }

            $columns[] = $column;
        }

        return $columns;
    }
}
