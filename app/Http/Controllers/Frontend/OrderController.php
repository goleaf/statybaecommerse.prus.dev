<?php

declare (strict_types=1);
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Traits\HandlesContentNegotiation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
/**
 * OrderController
 * 
 * HTTP controller handling OrderController related web requests, responses, and business logic with proper validation and error handling.
 * 
 */
final class OrderController extends Controller
{
    use HandlesContentNegotiation;
    /**
     * Display a listing of the resource with pagination and filtering.
     * @param Request $request
     * @return JsonResponse|View|Response
     */
    public function index(Request $request): JsonResponse|View|Response
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }
        $query = $user->orders()->with(['items.product', 'shipping']);
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")->orWhere('notes', 'like', "%{$search}%");
            });
        }
        $orders = $query->orderBy('created_at', 'desc')->paginate(10);
        $data = ['orders' => $orders->items(), 'pagination' => ['current_page' => $orders->currentPage(), 'last_page' => $orders->lastPage(), 'per_page' => $orders->perPage(), 'total' => $orders->total(), 'from' => $orders->firstItem(), 'to' => $orders->lastItem()]];
        return $this->handleContentNegotiation($request, $data, 'orders.index', ['orders' => $orders]);
    }
    /**
     * Display the specified resource with related data.
     * @param Order $order
     * @return View
     */
    public function show(Order $order): View
    {
        $user = Auth::user();
        if (!$user || $order->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }
        // Optimize relationship loading using Laravel 12.10 relationLoaded dot notation
        if (!$order->relationLoaded('items.product') || !$order->relationLoaded('items.productVariant') || !$order->relationLoaded('shipping') || !$order->relationLoaded('documents')) {
            $order->load(['items.product', 'items.productVariant', 'shipping', 'documents']);
        }
        return view('orders.show', compact('order'));
    }
    /**
     * Show the form for creating a new resource.
     * @return View
     */
    public function create(): View
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }
        $products = Product::with('variants')->where('is_visible', true)->get()->skipWhile(function (Product $product) {
            // Skip products that are not properly configured for order creation
            return empty($product->name) || !$product->is_visible || $product->price <= 0 || empty($product->slug) || $product->stock_quantity <= 0;
        });
        return view('orders.create', compact('products'));
    }
    /**
     * Store a newly created resource in storage with validation.
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }
        $validated = $request->validate(['items' => 'required|array|min:1', 'items.*.product_id' => 'required|exists:products,id', 'items.*.product_variant_id' => 'nullable|exists:product_variants,id', 'items.*.quantity' => 'required|integer|min:1', 'billing_address' => 'required|array', 'shipping_address' => 'required|array', 'notes' => 'nullable|string|max:1000', 'payment_method' => 'nullable|string|max:255']);
        try {
            DB::beginTransaction();
            // Calculate totals
            $subtotal = 0;
            $items = [];
            foreach ($validated['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $variant = $itemData['product_variant_id'] ? ProductVariant::findOrFail($itemData['product_variant_id']) : null;
                $unitPrice = $variant ? $variant->price : $product->price;
                $total = $unitPrice * $itemData['quantity'];
                $subtotal += $total;
                $items[] = ['product_id' => $product->id, 'product_variant_id' => $variant?->id, 'name' => $product->name, 'sku' => $variant ? $variant->sku : $product->sku, 'quantity' => $itemData['quantity'], 'unit_price' => $unitPrice, 'price' => $unitPrice, 'total' => $total];
            }
            // Create order
            $order = Order::create([
                'number' => 'ORD-' . strtoupper(uniqid()),
                'user_id' => $user->id,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                // Calculate based on business rules
                'shipping_amount' => 0,
                // Calculate based on shipping rules
                'discount_amount' => 0,
                'total' => $subtotal,
                'currency' => 'EUR',
                'billing_address' => $validated['billing_address'],
                'shipping_address' => $validated['shipping_address'],
                'notes' => $validated['notes'],
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
            ]);
            // Create order items
            foreach ($items as $item) {
                $order->items()->create($item);
            }
            DB::commit();
            return redirect()->route('frontend.orders.show', $order)->with('success', __('orders.messages.created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', __('orders.messages.creation_failed'));
        }
    }
    /**
     * Show the form for editing the specified resource.
     * @param Order $order
     * @return View
     */
    public function edit(Order $order): View
    {
        $user = Auth::user();
        if (!$user || $order->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }
        if (!$order->canBeCancelled()) {
            abort(403, __('orders.messages.cannot_edit'));
        }
        // Optimize relationship loading using Laravel 12.10 relationLoaded dot notation
        if (!$order->relationLoaded('items.product') || !$order->relationLoaded('items.productVariant')) {
            $order->load(['items.product', 'items.productVariant']);
        }
        $products = Product::with('variants')->where('is_visible', true)->get();
        return view('orders.edit', compact('order', 'products'));
    }
    /**
     * Update the specified resource in storage with validation.
     * @param Request $request
     * @param Order $order
     * @return RedirectResponse
     */
    public function update(Request $request, Order $order): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || $order->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }
        if (!$order->canBeCancelled()) {
            abort(403, __('orders.messages.cannot_edit'));
        }
        $validated = $request->validate(['items' => 'required|array|min:1', 'items.*.product_id' => 'required|exists:products,id', 'items.*.product_variant_id' => 'nullable|exists:product_variants,id', 'items.*.quantity' => 'required|integer|min:1', 'billing_address' => 'required|array', 'shipping_address' => 'required|array', 'notes' => 'nullable|string|max:1000', 'payment_method' => 'nullable|string|max:255']);
        try {
            DB::beginTransaction();
            // Delete existing items
            $order->items()->delete();
            // Calculate new totals
            $subtotal = 0;
            $items = [];
            foreach ($validated['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $variant = $itemData['product_variant_id'] ? ProductVariant::findOrFail($itemData['product_variant_id']) : null;
                $unitPrice = $variant ? $variant->price : $product->price;
                $total = $unitPrice * $itemData['quantity'];
                $subtotal += $total;
                $items[] = ['product_id' => $product->id, 'product_variant_id' => $variant?->id, 'name' => $product->name, 'sku' => $variant ? $variant->sku : $product->sku, 'quantity' => $itemData['quantity'], 'unit_price' => $unitPrice, 'price' => $unitPrice, 'total' => $total];
            }
            // Update order
            $order->update(['subtotal' => $subtotal, 'total' => $subtotal, 'billing_address' => $validated['billing_address'], 'shipping_address' => $validated['shipping_address'], 'notes' => $validated['notes'], 'payment_method' => $validated['payment_method']]);
            // Create new order items
            foreach ($items as $item) {
                $order->items()->create($item);
            }
            DB::commit();
            return redirect()->route('frontend.orders.show', $order)->with('success', __('orders.messages.updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', __('orders.messages.update_failed'));
        }
    }
    /**
     * Remove the specified resource from storage.
     * @param Order $order
     * @return RedirectResponse
     */
    public function destroy(Order $order): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || $order->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }
        if (!$order->canBeCancelled()) {
            abort(403, __('orders.messages.cannot_delete'));
        }
        $order->delete();
        return redirect()->route('frontend.orders.index')->with('success', __('orders.messages.deleted_successfully'));
    }
    /**
     * Handle cancel functionality with proper error handling.
     * @param Order $order
     * @return RedirectResponse
     */
    public function cancel(Order $order): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || $order->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }
        if (!$order->canBeCancelled()) {
            abort(403, __('orders.messages.cannot_cancel'));
        }
        $order->update(['status' => 'cancelled']);
        return redirect()->route('frontend.orders.show', $order)->with('success', __('orders.messages.cancelled_successfully'));
    }
}