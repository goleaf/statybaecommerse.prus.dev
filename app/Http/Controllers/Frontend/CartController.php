<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

final class CartController extends Controller
{
    public function index(): View
    {
        return view('frontend.cart.index', [
            'cart' => $this->buildCartSummary(),
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::query()
            ->withoutGlobalScopes()
            ->with(['media', 'prices.currency'])
            ->findOrFail($data['product_id']);

        $quantity = $data['quantity'] ?? 1;

        $cart = $this->getCart();
        $key = (string) $product->getKey();

        $cart[$key] = [
            'id' => $key,
            'product_id' => $product->getKey(),
            'name' => $product->trans('name') ?? $product->name,
            'price' => (float) ($product->prices->first()?->amount ?? $product->price ?? 0),
            'quantity' => ($cart[$key]['quantity'] ?? 0) + $quantity,
            'image' => $product->getFirstMediaUrl(config('media.storage.collection_name')),
            'slug' => $product->slug,
        ];

        $this->persistCart($cart);

        return redirect()->route('frontend.cart.index')->with('status', __('Product added to cart.'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer'],
        ]);

        $cart = $this->getCart();
        $key = (string) $data['product_id'];

        if (! isset($cart[$key])) {
            return redirect()->route('frontend.cart.index');
        }

        $quantity = max(0, $data['quantity']);

        if ($quantity === 0) {
            unset($cart[$key]);
        } else {
            $cart[$key]['quantity'] = $quantity;
        }

        $this->persistCart($cart);

        return redirect()->route('frontend.cart.index')->with('status', __('Cart updated.'));
    }

    public function remove(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
        ]);

        $cart = $this->getCart();
        unset($cart[(string) $data['product_id']]);
        $this->persistCart($cart);

        return redirect()->route('frontend.cart.index')->with('status', __('Item removed from cart.'));
    }

    public function clear(): RedirectResponse
    {
        Session::forget('cart');
        Session::forget('cart_discount');

        return redirect()->route('frontend.cart.index')->with('status', __('Cart cleared.'));
    }

    private function getCart(): array
    {
        return Session::get('cart', []);
    }

    private function persistCart(array $cart): void
    {
        Session::put('cart', $cart);
    }

    private function buildCartSummary(): array
    {
        $cart = $this->getCart();

        $items = collect($cart)->map(function (array $item) {
            $quantity = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['price'] ?? 0);

            return [
                'id' => $item['id'] ?? null,
                'product_id' => $item['product_id'] ?? null,
                'name' => $item['name'] ?? '',
                'price' => $price,
                'quantity' => $quantity,
                'total' => round($price * $quantity, 2),
                'image' => $item['image'] ?? null,
                'slug' => $item['slug'] ?? null,
            ];
        });

        $subtotal = $items->sum('total');
        $taxRate = (float) config('shared.tax.default_rate', 0.21);
        $tax = round($subtotal * $taxRate, 2);
        $shipping = $subtotal > 50 ? 0.0 : 5.99;
        $discount = (float) Session::get('cart_discount', 0);
        $total = max(0, round($subtotal + $tax + $shipping - $discount, 2));

        return [
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'tax' => $tax,
            'shipping' => round($shipping, 2),
            'discount' => round($discount, 2),
            'total' => $total,
        ];
    }
}
