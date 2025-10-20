<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Pricing\PriceCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

final class CartController extends Controller
{
    public function index(Request $request): View
    {
        $items = $this->getCartItems($request);

        return view('frontend.cart.index', [
            'cartItems' => $items,
            'summary' => $this->summarize($items),
        ]);
    }

    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);
        $sessionId = $request->session()->getId();
        $userId = $request->user()?->id;

        /** @var Product $product */
        $product = Product::query()->findOrFail($validated['product_id']);
        $variant = null;
        if (! empty($validated['product_variant_id'])) {
            $variant = ProductVariant::query()
                ->where('product_id', $product->getKey())
                ->findOrFail($validated['product_variant_id']);
        }

        $unitPrice = (float) ($variant?->price ?? $product->sale_price ?? $product->price ?? 0.0);

        $cartItem = CartItem::query()
            ->where('session_id', $sessionId)
            ->where('product_id', $product->getKey())
            ->when($variant, fn ($query) => $query->where('product_variant_id', $variant?->getKey()))
            ->first();

        if ($cartItem) {
            $cartItem->incrementQuantity($quantity);
        } else {
            $cartItem = CartItem::query()->create([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'product_id' => $product->getKey(),
                'product_variant_id' => $variant?->getKey(),
                'quantity' => $quantity,
                'minimum_quantity' => $product->getMinimumQuantity(),
                'unit_price' => $unitPrice,
                'price' => $unitPrice,
                'total_price' => $unitPrice * $quantity,
                'product_snapshot' => $this->snapshotProduct($product, $variant),
            ]);
        }

        $items = $this->getCartItems($request);

        return response()->json([
            'message' => __('Item added to cart.'),
            'cart_item' => [
                'id' => $cartItem->getKey(),
                'quantity' => $cartItem->quantity,
                'total_price' => $cartItem->calculateSubtotal(),
            ],
            'summary' => $this->summarize($items),
        ], $cartItem->wasRecentlyCreated ? 201 : 200);
    }

    public function update(Request $request, CartItem $cartItem): JsonResponse
    {
        $cartItem = $this->ensureOwnership($request, $cartItem);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cartItem->updateQuantity((int) $validated['quantity']);
        $items = $this->getCartItems($request);

        return response()->json([
            'message' => __('Cart item updated.'),
            'cart_item' => [
                'id' => $cartItem->getKey(),
                'quantity' => $cartItem->quantity,
                'total_price' => $cartItem->calculateSubtotal(),
            ],
            'summary' => $this->summarize($items),
        ]);
    }

    public function remove(Request $request, CartItem $cartItem): JsonResponse
    {
        $cartItem = $this->ensureOwnership($request, $cartItem);
        $cartItem->forceDelete();
        $items = $this->getCartItems($request);

        return response()->json([
            'message' => __('Cart item removed.'),
            'summary' => $this->summarize($items),
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $items = $this->getCartItems($request);
        $items->each->forceDelete();

        return response()->json([
            'message' => __('Cart cleared.'),
            'summary' => $this->summarize(collect()),
        ]);
    }

    private function getCartItems(Request $request): Collection
    {
        return CartItem::query()
            ->where('session_id', $request->session()->getId())
            ->with(['product.media', 'product.brand'])
            ->orderBy('created_at')
            ->get();
    }

    private function summarize(Collection $items): array
    {
        $subtotal = (float) $items->sum(fn (CartItem $item) => $item->calculateSubtotal());
        $breakdown = app(PriceCalculator::class)->breakdown($subtotal);

        return ['item_count' => (int) $items->sum('quantity')] + $breakdown->toSummary();
    }

    private function ensureOwnership(Request $request, CartItem $cartItem): CartItem
    {
        $sessionId = $request->session()->getId();
        $userId = $request->user()?->id;

        if ($cartItem->session_id !== $sessionId || ($userId && $cartItem->user_id !== $userId)) {
            abort(404);
        }

        return $cartItem;
    }

    private function snapshotProduct(Product $product, ?ProductVariant $variant): array
    {
        $image = null;
        if (method_exists($product, 'getFirstMediaUrl')) {
            $image = $product->getFirstMediaUrl(config('media.storage.collection_name'))
                ?: $product->getFirstMediaUrl(config('media.storage.thumbnail_collection'));
        }

        return array_filter([
            'name' => $variant?->name ?? $product->name,
            'sku' => $variant?->sku ?? $product->sku,
            'price' => $variant?->price ?? $product->price,
            'image' => $image,
        ], static fn ($value) => $value !== null);
    }
}
