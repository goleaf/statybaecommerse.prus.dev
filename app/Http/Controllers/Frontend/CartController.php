<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Pricing\PriceCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

final class CartController extends Controller
{
    public function index(): View
    {
        return view('frontend.cart.index', $this->buildCartSummary());
    }

    public function add(\App\Http\Requests\Frontend\CartAddItemRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $product = Product::query()->findOrFail($validated['product_id']);

        $cart = $this->getCart();
        $key = (string) $product->getKey();
        $current = $cart[$key] ?? [];
        $quantity = ($current['quantity'] ?? 0) + $validated['quantity'];

        $cart[$key] = [
            'id' => $key,
            'product_id' => $product->getKey(),
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => (float) ($product->sale_price ?? $product->price ?? 0),
            'quantity' => $quantity,
            'image' => $product->getFirstMediaUrl(config('media.storage.collection_name'), 'thumb') ?: null,
        ];

        $this->saveCart($cart);

        return redirect()->route('frontend.cart.index')->with('status', 'cart-updated');
    }

    public function update(Request $request): RedirectResponse
    {
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

    public function remove(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
        ]);

        $cart = $this->getCart();
        Arr::forget($cart, (string) $validated['product_id']);
        $this->saveCart($cart);

        return redirect()->route('frontend.cart.index')->with('status', 'cart-updated');
    }

    public function clear(): RedirectResponse
    {
        Session::forget('cart');
        Session::forget('cart_discount');
        Session::forget('applied_coupon');

        return redirect()->route('frontend.cart.index')->with('status', 'cart-cleared');
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
