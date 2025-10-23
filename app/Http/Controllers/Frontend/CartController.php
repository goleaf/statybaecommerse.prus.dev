<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

final class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function index(Request $request): JsonResponse
    {
        $cartItems = collect($request->session()->get('cart', []));
        $summary = $this->calculateSummary($cartItems);

        return view('frontend.cart.index', [
            'items' => $cartItems,
            'summary' => $summary,
        ]);
    }

    public function add(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::query()->findOrFail($data['product_id']);
        $quantity = $data['quantity'] ?? 1;

        $cart = collect($request->session()->get('cart', []));

        $existing = $cart->firstWhere('id', $product->id);

        if ($existing) {
            $cart = $cart->map(function (array $item) use ($product, $quantity) {
                if ((int) $item['id'] === $product->id) {
                    $item['quantity'] += $quantity;
                    $item['total'] = $item['price'] * $item['quantity'];
                }

                return $item;
            });
        } else {
            $cart->push([
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'quantity' => $quantity,
                'sku' => $product->sku,
                'total' => (float) $product->price * $quantity,
            ]);
        }

        $request->session()->put('cart', $cart->values()->all());

        return redirect()->route('frontend.cart.index')->with('status', __('Product added to cart.'));
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = collect($request->session()->get('cart', []));
        $quantityUpdates = collect($data['items'])->keyBy(fn ($item) => (int) $item['id']);

        $cart = $cart->map(function (array $item) use ($quantityUpdates) {
            $productId = (int) ($item['id'] ?? 0);
            if ($quantityUpdates->has($productId)) {
                $newQuantity = (int) $quantityUpdates[$productId]['quantity'];
                $item['quantity'] = $newQuantity;
                $item['total'] = ($item['price'] ?? 0) * $newQuantity;
            }

            return $item;
        });

        $request->session()->put('cart', $cart->values()->all());

        return redirect()->route('frontend.cart.index')->with('status', __('Cart updated successfully.'));
    }

    public function remove(string $id): JsonResponse
    {
        $data = $request->validate([
            'id' => ['required', 'integer'],
        ]);

        $cart = collect($request->session()->get('cart', []))
            ->reject(fn (array $item) => (int) $item['id'] === (int) $data['id'])
            ->values();

        $request->session()->put('cart', $cart->all());

        return redirect()->route('frontend.cart.index')->with('status', __('Item removed from cart.'));
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget(['cart', 'cart_discount', 'applied_coupon']);

        return redirect()->route('frontend.cart.index')->with('status', __('Cart cleared.'));
    }

    private function calculateSummary(Collection $items): array
    {
        $subtotal = $items->sum(fn ($item) => ($item['price'] ?? 0) * ($item['quantity'] ?? 0));
        $taxRate = (float) config('shared.tax.default_rate', 0.21);
        $tax = $subtotal * $taxRate;
        $shipping = $subtotal > 50 ? 0 : 5.99;
        $discount = (float) session('cart_discount', 0);
        $total = $subtotal + $tax + $shipping - $discount;

        return [
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'shipping' => round($shipping, 2),
            'discount' => round($discount, 2),
            'total' => round(max($total, 0), 2),
        ];
    }

    public function clear(Request $request): JsonResponse
    {
        $sessionId = $request->session()->getId();
        $userIdentifier = $request->user()?->getAuthIdentifier();
        $userId = is_numeric($userIdentifier) ? (int) $userIdentifier : null;

        $fallbackSessionId = $request->session()->get('cart_session_id');
        $fallbackSessionId = is_string($fallbackSessionId) ? $fallbackSessionId : null;

        $this->cartService->clear($userId, $sessionId, $fallbackSessionId);
        $summary = $this->cartService->getSummary($userId, $sessionId);

        return response()->json([
            'success' => true,
            'message' => __('Cart cleared successfully.'),
            'cart' => $summary,
        ]);
    }
}
