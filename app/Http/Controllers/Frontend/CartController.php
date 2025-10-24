<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CartAddItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\Cart\CartService;
use App\Services\Pricing\PriceCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

final class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly PriceCalculator $priceCalculator,
    ) {}

    public function index(Request $request): View
    {
        return view('frontend.cart.index', $this->buildCartSummary($request));
    }

    public function add(CartAddItemRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        $product = Product::query()->findOrFail($validated['product_id']);

        $cart = $this->getCart();
        $key = (string) $product->getKey();
        $current = $cart[$key] ?? [];
        $addedQuantity = (int) $validated['quantity'];
        $newQuantity = ((int) ($current['quantity'] ?? 0)) + $addedQuantity;

        $cart[$key] = [
            'id' => $key,
            'product_id' => $product->getKey(),
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => (float) ($product->sale_price ?? $product->price ?? 0),
            'quantity' => $newQuantity,
            'image' => $product->getFirstMediaUrl(config('media.storage.collection_name'), 'thumb') ?: null,
        ];

        $this->saveCart($cart);

        if ($request->expectsJson()) {
            $cartItem = $this->persistCartItem($request, $product, $addedQuantity);

            return $this->respondWithSummary($request, [
                'success' => true,
                'cart_item' => [
                    'id' => $cartItem->getKey(),
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'total_price' => (float) ($cartItem->total_price ?? 0.0),
                ],
            ], 201);
        }

        return redirect()->route('frontend.cart.index')->with('status', 'cart-updated');
    }

    public function update(Request $request, ?CartItem $cartItem = null): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            $payload = $request->validate([
                'quantity' => ['required', 'integer', 'min:1'],
            ]);

            if ($cartItem === null) {
                return response()->json([
                    'success' => false,
                    'message' => __('Cart item not found.'),
                ], 404);
            }

            $cartItem->quantity = (int) $payload['quantity'];
            $unitPrice = (float) ($cartItem->price ?? $cartItem->unit_price ?? 0.0);
            $cartItem->total_price = round($unitPrice * $cartItem->quantity, 2);
            $cartItem->save();

            $this->syncSessionFromCartItem($cartItem);

            return $this->respondWithSummary($request, [
                'success' => true,
                'cart_item' => [
                    'id' => $cartItem->getKey(),
                    'quantity' => $cartItem->quantity,
                ],
            ]);
        }

        $payload = $request->validate([
            'items' => ['required_without:product_id', 'array'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'product_id' => ['sometimes', 'integer'],
            'quantity' => ['required_without:items', 'integer', 'min:1'],
        ]);

        $cart = $this->getCart();

        $items = $payload['items'] ?? [['product_id' => $payload['product_id'], 'quantity' => $payload['quantity']]];

        foreach ($items as $item) {
            $key = (string) $item['product_id'];
            if (! isset($cart[$key])) {
                continue;
            }

            $cart[$key]['quantity'] = (int) $item['quantity'];
        }

        $this->saveCart($cart);

        return redirect()->route('frontend.cart.index')->with('status', 'cart-updated');
    }

    public function remove(Request $request, ?CartItem $cartItem = null): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            if ($cartItem === null) {
                return response()->json([
                    'success' => false,
                    'message' => __('Cart item not found.'),
                ], 404);
            }

            $cartItem->forceDelete();
            $this->removeFromSessionCart((int) $cartItem->product_id);

            return $this->respondWithSummary($request, [
                'success' => true,
                'message' => __('Item removed from cart.'),
            ]);
        }

        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
        ]);

        $cart = $this->getCart();
        Arr::forget($cart, (string) $validated['product_id']);
        $this->saveCart($cart);

        return redirect()->route('frontend.cart.index')->with('status', 'cart-updated');
    }

    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        $userId = $this->resolveUserId($request);
        $sessionId = (string) $request->session()->getId();

        $this->cartService->clear($userId, $sessionId);
        Session::forget('applied_coupon');
        Session::forget('checkout.coupon');

        if ($request->expectsJson()) {
            return $this->respondWithSummary($request, [
                'success' => true,
                'message' => __('Cart cleared successfully.'),
            ]);
        }

        return redirect()->route('frontend.cart.index')->with('status', 'cart-cleared');
    }

    private function persistCartItem(Request $request, Product $product, int $quantity): CartItem
    {
        $sessionId = (string) $request->session()->getId();
        $userId = $this->resolveUserId($request);
        $unitPrice = (float) ($product->sale_price ?? $product->price ?? 0.0);

        $cartItemQuery = CartItem::query()
            ->where(static function ($query) use ($sessionId, $userId): void {
                $query->where('session_id', $sessionId);

                if ($userId !== null) {
                    $query->orWhere('user_id', $userId);
                }
            })
            ->where('product_id', $product->getKey())
            ->whereNull('variant_id');

        $cartItem = $cartItemQuery->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
        } else {
            $cartItem = new CartItem([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'product_id' => $product->getKey(),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'price' => $unitPrice,
                'total_price' => round($unitPrice * $quantity, 2),
            ]);
        }

        $cartItem->user_id = $userId ?? $cartItem->user_id;
        $cartItem->unit_price = $unitPrice;
        $cartItem->price = $unitPrice;
        $cartItem->total_price = round($unitPrice * $cartItem->quantity, 2);
        $cartItem->product_snapshot = array_filter([
            'name' => $product->name,
            'price' => $unitPrice,
            'sku' => $product->sku,
            'image' => $product->getFirstMediaUrl(config('media.storage.collection_name'), 'thumb') ?: null,
        ], static fn ($value) => $value !== null);

        $cartItem->save();

        $this->syncSessionFromCartItem($cartItem);

        return $cartItem->refresh();
    }

    private function syncSessionFromCartItem(CartItem $cartItem): void
    {
        $cart = $this->getCart();
        $key = (string) $cartItem->product_id;

        if (! isset($cart[$key])) {
            return;
        }

        $cart[$key]['quantity'] = $cartItem->quantity;
        $this->saveCart($cart);
    }

    private function removeFromSessionCart(int $productId): void
    {
        $cart = $this->getCart();
        Arr::forget($cart, (string) $productId);
        $this->saveCart($cart);
    }

    private function respondWithSummary(Request $request, array $payload = [], int $status = 200): JsonResponse
    {
        $summary = $this->cartService->getSummary($this->resolveUserId($request), (string) $request->session()->getId());

        $payload['summary'] ??= $this->transformSummary($summary);
        $payload['cart'] ??= [
            'count' => $summary['count'],
            'items' => $summary['items'],
            'subtotal' => $summary['subtotal'],
            'discount' => $summary['discount'],
            'tax' => $summary['tax'],
            'shipping' => $summary['shipping'],
            'total' => $summary['total'],
        ];

        return response()->json($payload, $status);
    }

    private function buildCartSummary(Request $request): array
    {
        $summary = $this->cartService->getSummary($this->resolveUserId($request), (string) $request->session()->getId());
        $breakdown = $this->priceCalculator->breakdown(
            subtotal: $summary['subtotal'],
            discount: $summary['discount'],
            shipping: $summary['shipping'],
            vatRate: config('shared.tax.default_rate', 0.21)
        );

        return [
            'items' => $summary['items'],
            'subtotal' => $breakdown->subtotal,
            'tax' => $breakdown->tax,
            'shipping' => $breakdown->shipping,
            'discount' => $breakdown->discount,
            'total' => $breakdown->total,
            'summary' => $breakdown->toSummary() + ['item_count' => $summary['count']],
        ];
    }

    /**
     * @param array{items: array<int, mixed>, count:int, subtotal:float, tax:float, shipping:float, discount:float, total:float} $summary
     * @return array<string, mixed>
     */
    private function transformSummary(array $summary): array
    {
        return [
            'item_count' => $summary['count'],
            'items' => $summary['items'],
            'subtotal' => $summary['subtotal'],
            'tax' => $summary['tax'],
            'shipping' => $summary['shipping'],
            'discount' => $summary['discount'],
            'total' => $summary['total'],
        ];
    }

    private function resolveUserId(Request $request): ?int
    {
        $identifier = $request->user()?->getAuthIdentifier();

        return $identifier === null ? null : (int) $identifier;
    }

    private function getCart(): array
    {
        return Session::get('cart', []);
    }

    private function saveCart(array $cart): void
    {
        Session::put('cart', $cart);
    }
}
