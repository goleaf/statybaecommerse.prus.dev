<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * ProductHistoryController
 *
 * HTTP controller handling ProductHistoryController related web requests, responses, and business logic with proper validation and error handling.
 */
final class ProductHistoryController extends Controller
{
    /**
     * Display a single product history page with optional filtering.
     */
    public function show(Request $request, Product $product): View
    {
        // Ensure the product has the relationships required by the Blade template eager loaded.
        $product->loadMissing(['brand.translations']);

        // Define the supported filter options to keep the controller aligned with the Livewire page defaults.
        $allowedPerPage = [10, 20, 50, 100];
        $allowedActions = ['created', 'updated', 'price_changed', 'stock_updated', 'status_changed', 'deleted', 'restored'];
        $allowedDateFilters = ['7', '30', '90'];

        // Normalise the pagination size and gracefully fall back to the default when invalid data is supplied.
        $perPage = (int) $request->integer('per_page', 20);
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 20;
        }

        // Sanitise the action filter so unknown actions do not leak into the query builder.
        $actionFilter = trim((string) $request->query('action', ''));
        if ($actionFilter !== '' && ! in_array($actionFilter, $allowedActions, true)) {
            $actionFilter = '';
        }

        // Normalise the optional date filter and convert it to an integer when valid.
        $dateFilterRaw = $request->query('date');
        $dateFilter = null;
        if ($dateFilterRaw !== null && in_array((string) $dateFilterRaw, $allowedDateFilters, true)) {
            $dateFilter = (int) $dateFilterRaw;
        }

        // Build the base query for the history timeline, mirroring the Livewire component defaults.
        $historyQuery = $product
            ->histories()
            ->with(['user:id,name,email'])
            ->latest();

        // Apply the optional action filter when present.
        if ($actionFilter !== '') {
            $historyQuery->where('action', $actionFilter);
        }

        // Apply the optional date range filter when provided.
        if ($dateFilter !== null) {
            $historyQuery->where('created_at', '>=', now()->subDays($dateFilter));
        }

        // Paginate the final query while preserving query string parameters for navigation links.
        $history = $historyQuery->paginate($perPage)->withQueryString();

        // Gather statistics used by the Blade template to present aggregate metrics.
        $totalChanges = $product->histories()->count();
        $priceChanges = $product->priceHistories()->count();
        $stockUpdates = $product->stockHistories()->count();
        $lastChange = $product->histories()->latest()->first();

        return view('livewire.pages.product-history', [
            'product'      => $product,
            'history'      => $history,
            'totalChanges' => $totalChanges,
            'priceChanges' => $priceChanges,
            'stockUpdates' => $stockUpdates,
            'lastChange'   => $lastChange,
            // Expose the normalised filters so the Blade view (and tests) can reflect the current state.
            'perPage'      => $perPage,
            'actionFilter' => $actionFilter,
            'dateFilter'   => $dateFilter !== null ? (string) $dateFilter : '',
        ]);
    }
}
