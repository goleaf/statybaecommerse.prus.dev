<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\ProductRequestData;
use App\Models\Product;
use App\Models\ProductRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

/**
 * ProductRequestController
 *
 * HTTP controller handling ProductRequestController related web requests, responses, and business logic with proper validation and error handling.
 */
final class ProductRequestController extends Controller
{
    use AuthorizesRequests;

    /**
     * Show the form for creating a new resource.
     */
    public function create(Product $product): View
    {
        if (! $product->isRequestable()) {
            abort(404, __('translations.product_not_requestable'));
        }

        return view('products.request-form', ['product' => $product]);
    }

    /**
     * Store a newly created resource in storage with validation.
     */
    public function store(ProductRequestData $data): RedirectResponse
    {
        $product = Product::findOrFail($data->product_id);
        if (! $product->isRequestable()) {
            // Preserve user input so the form can be redisplayed with validation feedback.
            return Redirect::back()
                ->withInput()
                ->withErrors(['error' => __('translations.product_not_requestable')]);
        }

        // Resolve the authenticated user up-front to avoid repeated helper calls.
        $userId = Auth::id();

        if ($userId === null) {
            // Guard against unexpected missing authentication contexts.
            abort(403);
        }

        // Persist the request and increment the aggregate counter atomically.
        DB::transaction(function () use ($product, $data, $userId): void {
            ProductRequest::query()->create([
                'product_id'         => $product->id,
                'user_id'            => $userId,
                'name'               => $data->name,
                'email'              => $data->email,
                'phone'              => $data->phone,
                'message'            => $data->message,
                'requested_quantity' => $data->requested_quantity,
                'status'             => ProductRequest::STATUS_PENDING,
            ]);

            // Keep the product level request counter in sync with the stored record.
            $product->incrementRequestsCount();
        });

        $productShowRoute = Route::has('products.show') ? 'products.show' : 'frontend.products.show';

        return redirect()->route($productShowRoute, $product)->with('success', __('translations.product_request_submitted_successfully'));
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(ProductRequest $productRequest): View
    {
        $this->authorize('view', $productRequest);

        return view('products.request-details', ['productRequest' => $productRequest]);
    }

    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user === null) {
            // Consistently guard endpoints that require authentication.
            abort(403);
        }

        $productRequests = ProductRequest::with(['product', 'respondedBy'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('products.requests-index', ['productRequests' => $productRequests]);
    }

    /**
     * Handle cancel functionality with proper error handling.
     */
    public function cancel(ProductRequest $productRequest): RedirectResponse
    {
        $this->authorize('update', $productRequest);
        if ($productRequest->isCompleted() || $productRequest->isCancelled()) {
            return redirect()->back()->withErrors(['error' => __('translations.cannot_cancel_request')]);
        }
        $productRequest->markAsCancelled();

        return redirect()->back()->with('success', __('translations.product_request_cancelled'));
    }
}
