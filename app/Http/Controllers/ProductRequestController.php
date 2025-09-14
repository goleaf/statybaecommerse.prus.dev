<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\ProductRequestData;
use App\Models\Product;
use App\Models\ProductRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final /**
 * ProductRequestController
 * 
 * HTTP controller handling web requests and responses.
 */
class ProductRequestController extends Controller
{
    /**
     * Display the product request form.
     */
    public function create(Product $product): View
    {
        if (! $product->isRequestable()) {
            abort(404, __('translations.product_not_requestable'));
        }

        return view('products.request-form', compact('product'));
    }

    /**
     * Store a new product request.
     */
    public function store(ProductRequestData $data): RedirectResponse
    {
        $product = Product::findOrFail($data->product_id);

        if (! $product->isRequestable()) {
            return redirect()->back()
                ->withErrors(['error' => __('translations.product_not_requestable')]);
        }

        $productRequest = ProductRequest::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'name' => $data->name,
            'email' => $data->email,
            'phone' => $data->phone,
            'message' => $data->message,
            'requested_quantity' => $data->requested_quantity,
            'status' => 'pending',
        ]);

        // Increment the requests count on the product
        $product->incrementRequestsCount();

        return redirect()->route('products.show', $product)
            ->with('success', __('translations.product_request_submitted_successfully'));
    }

    /**
     * Display the specified product request.
     */
    public function show(ProductRequest $productRequest): View
    {
        $this->authorize('view', $productRequest);

        return view('products.request-details', compact('productRequest'));
    }

    /**
     * Display a listing of the user's product requests.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $productRequests = ProductRequest::with(['product', 'respondedBy'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('products.requests-index', compact('productRequests'));
    }

    /**
     * Cancel a product request.
     */
    public function cancel(ProductRequest $productRequest): RedirectResponse
    {
        $this->authorize('update', $productRequest);

        if ($productRequest->isCompleted() || $productRequest->isCancelled()) {
            return redirect()->back()
                ->withErrors(['error' => __('translations.cannot_cancel_request')]);
        }

        $productRequest->markAsCancelled();

        return redirect()->back()
            ->with('success', __('translations.product_request_cancelled'));
    }
}
